# Audit Déploiement PraxiQuest — 2026-06-30

> Audit poussé de la structure du projet, des scripts de déploiement et du système de plugins.
> Objectif : cartographier les causes racines des problèmes de déploiement récurrents.

---

## Résumé exécutif

| Sévérité | Nb | Description |
|---|---|---|
| 🔴 CRITIQUE | 2 | Plugins silencieusement ignorés au déploiement |
| 🟠 MOYEN | 3 | Fichiers corrompus (null bytes) + incohérence script |
| 🟡 FAIBLE | 2 | Pollution repo + messages commits opaques |

---

## Architecture du projet

```
PraxiTests/
├── app/
│   ├── Core/Plugins/         ← PluginManager, PluginRegistry, AbstractPlugin
│   ├── Providers/            ← PraxiQuestServiceProvider (boot des plugins)
│   ├── Http/Controllers/     ← Candidate/, Admin/, etc.
│   └── Models/               ← Plugin.php (table `plugins`)
├── plugins/                  ← 20 plugins (19 actifs + _template)
│   ├── [slug]/plugin.json    ← manifeste (slug, service_provider, reward…)
│   ├── [slug]/PluginServiceProvider.php  ← classmap (racine)
│   └── [slug]/src/           ← PSR-4 (classes PHP)
├── routes/
│   ├── web.php               ← routes core candidat + billing + 360
│   ├── admin.php             ← back-office
│   └── auth.php              ← authentification
├── composer.json             ← PSR-4 + classmap pour chaque plugin
├── deploy-ovh.ps1            ← script LOCAL (build Vite + git push)
└── deploy-server.sh          ← script SERVEUR OVH (git pull + migrate + cache)
```

### Cycle de vie d'un plugin (comment ça boot)

1. `deploy-ovh.ps1` (local) : `npm run build` → `git push`
2. `deploy-server.sh` (SSH OVH) : `git pull` → `composer install` → `migrate` → **`praxiquest:plugins:discover --sync`** → `cache:clear/cache`
3. Au runtime : `PraxiQuestServiceProvider::boot()` → `PluginManager::bootEnabledPlugins()` → lit la table `plugins` (enabled=true) → charge le `service_provider` via autoload Composer

**Point clé :** un plugin existe en DB seulement si `discover --sync` l'a trouvé **ET** son `plugin.json` est valide JSON. S'il n'est pas en DB, il ne boot jamais, même si tout le code PHP est correct.

---

## 🔴 BUG-1 — 10 plugin.json invalides (trailing comma)

### Symptôme
Au déploiement, `praxiquest:plugins:discover --sync` ignore silencieusement 10 plugins. Aucune erreur visible en console (juste un `logger()->warning()`).

### Plugins affectés
| Plugin | Erreur JSON |
|---|---|
| praxiboost | trailing comma ligne 24 |
| praxiflow | trailing comma ligne 25 |
| praxilead | trailing comma ligne 24 |
| praxilink | trailing comma ligne 22 |
| praximiroir | virgule manquante (déjà différent) |
| praxiself | trailing comma ligne 25 |
| praxispeak | trailing comma ligne 25 |
| praxivision | trailing comma ligne 24 |
| praxizen | trailing comma ligne 25 |
| praxizenith | trailing comma ligne 24 |

### Cause racine
Le bloc `"reward"` de chaque plugin.json se termine par une **virgule traînante** avant `}` :

```json
  "reward": {
    "threshold_eclats": 600,
    "entry_route": "praxiboost.index"
  },           ← ← ← VIRGULE INVALIDE (pas de propriété après)
               ← ligne vide ici
```

PHP `json_decode($content, true, 512, JSON_THROW_ON_ERROR)` lève une `\JsonException`. `PluginRegistry::discover()` catch `\Throwable` → log warning → continue. Plugin absent de la DB → jamais activé.

### Fix
Supprimer la virgule finale dans les 10 fichiers. Exemple pour `praxiboost/plugin.json` :

```json
  "reward": {
    "threshold_eclats": 600,
    "entry_route": "praxiboost.index"
  }           ← pas de virgule ici (c'est la dernière propriété)
}
```

**Vérification rapide en local :**
```bash
for f in plugins/*/plugin.json; do
  php -r "json_decode(file_get_contents('$f'), true, 512, JSON_THROW_ON_ERROR);" 2>&1 && echo "$f OK" || echo "$f ERREUR"
done
```

---

## 🔴 BUG-2 — `praximiroir` absent de `composer.json`

### Symptôme
Même si son `plugin.json` était valide, `praximiroir` ne peut pas booter : sa classe `Praxis\Plugins\PraxiMiroir\PluginServiceProvider` est inconnue de l'autoloader Composer.

### Cause
Le plugin a été créé mais ses entrées n'ont **jamais été ajoutées** à `composer.json` :
- Pas d'entrée PSR-4 pour `Praxis\\Plugins\\PraxiMiroir\\`
- Pas d'entrée classmap pour `plugins/praximiroir/PluginServiceProvider.php`

### Fix — Ajouter dans `composer.json` :

```json
"psr-4": {
  ...
  "Praxis\\Plugins\\PraxiMiroir\\": "plugins/praximiroir/src/",
  "Praxis\\Plugins\\PraxiMiroir\\Database\\Seeders\\": "plugins/praximiroir/database/seeders/",
  ...
},
"classmap": [
  ...
  "plugins/praximiroir/PluginServiceProvider.php"
]
```

Puis `composer dump-autoload` (local) et redéployer.

---

## 🟠 BUG-3 — Null bytes dans 2 fichiers

Ces fichiers ont été tronqués/corrompus lors d'un push Windows (problème documenté en mémoire).

### `config/praxiquest.php`
- **2 null bytes** aux positions 6269-6270, juste **après** `];` (fin de fichier)
- Risque : sur certaines configs OPcache, PHP peut refuser de compiler le fichier → fatal error au boot de l'app
- Fix : réécrire proprement la fin du fichier (supprimer les bytes `\x00`)

### `plugins/praximet/resources/js/Pages/PraximetResult.vue`
- **5 null bytes** aux positions 4596-4600, après `</template>`
- Risque : Vite/esbuild peut rejeter le fichier lors du build → page résultats Praximet blanche
- Fix : réécrire le fichier (supprimer les bytes `\x00` en fin)

**Détection automatique :**
```bash
find . -name "*.php" -o -name "*.vue" | grep -v node_modules | grep -v vendor | \
  xargs -I{} bash -c 'n=$(tr -cd "\000" < "{}" | wc -c); [ $n -gt 0 ] && echo "{}: $n null bytes"'
```

---

## 🟠 BUG-4 — Incohérence chemin dans `deploy-ovh.ps1`

### Symptôme
Le message final de `deploy-ovh.ps1` affiche :
```
cd ~/praxiquest && bash deploy-server.sh
```
Mais le **vrai chemin** sur OVH est `~/www` (documenté dans `deploy-server.sh` ligne 4).

### Risque
Erreur humaine : lancer `cd ~/praxiquest` en SSH → dossier introuvable → confusion, perte de temps.

### Fix — `deploy-ovh.ps1` ligne 31 :
```powershell
# Avant
Write-Host "    cd ~/praxiquest && bash deploy-server.sh"
# Après
Write-Host "    cd ~/www && bash deploy-server.sh"
```

---

## 🟡 INFO-1 — 78 fichiers `vite.config.js.timestamp-*.mjs` dans le repo

### Situation
Le `.gitignore` contient bien `vite.config.js.timestamp-*.mjs` **mais ces fichiers ont été committés avant la règle**. Git les track toujours → ils s'accumulent à chaque commit (78 fichiers à ce jour).

### Impact
- Commits inutilement lourds
- `git status` bruité
- Pas d'impact fonctionnel sur le déploiement

### Fix
```bash
git rm --cached vite.config.js.timestamp-*.mjs
git commit -m "chore: désindexer les fichiers timestamp vite (déjà dans .gitignore)"
```

---

## 🟡 INFO-2 — Messages de commits non descriptifs

Tous les commits récents ont le même message automatique :
```
feat: maj complete pour test OVH (2026-06-30 HH:MM)
```

### Impact
Impossible de retrouver quel commit a introduit un bug. Aucune traçabilité des changements.

### Recommandation
Modifier `deploy-ovh.ps1` pour demander un vrai message :
```powershell
$msg = Read-Host "Message de commit (ex: fix: trailing comma plugin.json)"
if (-not $msg) { $msg = "chore: maj $(Get-Date -Format 'yyyy-MM-dd HH:mm')" }
```

---

## Procédure de déploiement complète et correcte

### Checklist avant tout déploiement

```bash
# 1. Valider TOUS les plugin.json
for f in plugins/*/plugin.json; do
  php -r "json_decode(file_get_contents('$f'), true, 512, JSON_THROW_ON_ERROR);" 2>&1 \
    && echo "✓ $f" || echo "✗ $f INVALIDE"
done

# 2. Vérifier null bytes
find . \( -name "*.php" -o -name "*.vue" \) | grep -v node_modules | grep -v vendor | \
  xargs -I{} bash -c 'n=$(tr -cd "\000" < "{}" | wc -c); [ $n -gt 0 ] && echo "✗ {}: $n null bytes"'

# 3. Vérifier que chaque plugin est dans composer.json
for slug in $(ls plugins/ | grep -v '^_'); do
  grep -q "$slug" composer.json && echo "✓ $slug" || echo "✗ $slug ABSENT de composer.json"
done
```

### Ordre des opérations (LOCAL)
1. `npm run build` (Vite)
2. Valider plugin.json (cf. ci-dessus)
3. `git add -A && git commit -m "description précise" && git push`

### Ordre des opérations (SSH OVH → `~/www`)
```bash
cd ~/www
bash deploy-server.sh
# Le script fait : git pull → composer install → migrate → discover --sync → cache
```

### Après déploiement — activation manuelle si nouveau plugin
```bash
php artisan praxiquest:plugins:discover --sync
php artisan praxiquest:plugins:activate <slug>
php artisan cache:clear
```

---

## Récapitulatif des fixes à appliquer

| Priorité | Action | Fichier(s) |
|---|---|---|
| 🔴 1 | Supprimer trailing comma | 10 × `plugins/*/plugin.json` |
| 🔴 2 | Ajouter praximiroir dans composer.json | `composer.json` |
| 🟠 3 | Supprimer null bytes | `config/praxiquest.php` + `plugins/praximet/resources/js/Pages/PraximetResult.vue` |
| 🟠 4 | Corriger chemin deploy | `deploy-ovh.ps1` ligne 31 |
| 🟡 5 | Désindexer vite timestamps | `git rm --cached vite.config.js.timestamp-*.mjs` |
| 🟡 6 | Messages commits descriptifs | `deploy-ovh.ps1` (prompt interactif) |

---

*Audit généré le 2026-06-30 par Claude / Cowork*
