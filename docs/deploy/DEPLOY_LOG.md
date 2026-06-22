# PraxiQuest — Journal de déploiement OVH

## Contexte
- **Projet** : PraxiQuest — SaaS Laravel 11 + Inertia.js + Vue 3 + Tailwind CSS v3 + Vite
- **Hébergement** : OVH mutualisé cluster121 — `praxiquest.decisionpro.fr`
- **Serveur web** : Apache (pas OpenResty comme supposé initialement)
- **PHP** : 8.2
- **Repo GitHub** : `https://github.com/alexandrefradin-lab/PraxiTests.git` (branche `main`)
- **SSH OVH** : `decisiv@ssh.cluster121.hosting.ovh.net`
- **Dossier projet** : `~/praxiquest/`
- **Dossier local** : `C:\Users\Fradin Alexandre\Documents\Claude\Projects\PraxiTests`

---

## Ce qui a été fait

### 1. Fix `public/index.php` — Servir les assets `/build/` via PHP
OVH shared hosting (OpenResty/Apache) ne suivait pas les symlinks pour les fichiers statiques.
Un bloc PHP a été ajouté en tête de `public/index.php` pour intercepter les requêtes `/build/*`
et les streamer directement avec le bon `Content-Type`.

> **Note** : finalement Apache servait les fichiers statiques directement (pas besoin du bloc PHP),
> mais le fix a été gardé pour la compatibilité.

### 2. Permissions du dossier `public/build/`
Apache retournait 403 sur les assets JS/CSS.

```bash
chmod -R 755 ~/praxiquest/public/build/
```

### 3. Création des dossiers de cache Laravel
Erreur `Please provide a valid cache path` au démarrage.

```bash
mkdir -p ~/praxiquest/storage/framework/{views,cache/data,sessions}
chmod -R 775 ~/praxiquest/storage
php ~/praxiquest/artisan view:clear
```

### 4. Authentification GitHub via SSH (plus de token HTTPS)
Les tokens HTTPS échouaient. Solution : clé SSH dédiée.

```bash
ssh-keygen -t ed25519 -C "ovh-praxiquest" -f ~/.ssh/id_github -N ""
# Clé publique ajoutée sur github.com/settings/ssh
cat >> ~/.ssh/config << 'EOF'
Host github.com
  IdentityFile ~/.ssh/id_github
  User git
EOF
git remote set-url origin git@github.com:alexandrefradin-lab/PraxiTests.git
```

### 5. Fix CSS Tailwind — PostCSS manquant
Le fichier `postcss.config.js` était absent du projet.
Résultat : les directives `@tailwind` n'étaient pas traitées → CSS de 5.5 kB au lieu de 51 kB.
Toutes les classes utilitaires Tailwind étaient absentes.

**Fix** : création de `postcss.config.js` à la racine du projet :

```js
export default {
  plugins: {
    tailwindcss: {},
    autoprefixer: {},
  },
}
```

Puis rebuild local + push + pull OVH.

---

## Workflow de déploiement (à retenir)

```bash
# Local (PowerShell)
cd "C:\Users\Fradin Alexandre\Documents\Claude\Projects\PraxiTests"
npm run build
git add -f public/build/
git commit -m "..."
git push

# OVH (PuTTY)
cd ~/praxiquest
rm -rf public/build/
git pull
```

---

## État actuel
- ✅ Site accessible sur `https://praxiquest.decisionpro.fr`
- ✅ Assets JS/CSS servis correctement (Apache, permissions 755)
- ✅ Tailwind CSS compilé correctement (51 kB)
- ✅ Laravel boot OK (storage/cache créés)
- ✅ Git push/pull via SSH fonctionnel
- ⚠️ Design landing page à améliorer (composants Vue)
- ⚠️ Token GitHub exposé dans le terminal — **à révoquer** sur github.com/settings/tokens
