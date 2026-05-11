# Déploiement OVH Perso — sans aucun outil local

> **Pour qui ?** Tu as un hébergement OVH **Perso** (sans SSH), tu veux installer PraxiTests, tu **n'as PHP/Composer/Node sur aucune machine**.

## Le flux : 4 étapes, ~30 min

```
[1] GitHub builde le zip pour toi (5 min, gratuit)
       ↓
[2] Tu télécharges le zip généré (30 sec)
       ↓
[3] Tu uploades sur OVH via FileZilla (15-20 min)
       ↓
[4] Tu ouvres install.php — c'est terminé (5 min)
```

Tu n'installes **rien sur ta machine**. Juste un navigateur web + FileZilla (gratuit).

---

## Étape 1 — GitHub builde le zip pour toi

### 1.1 Crée un compte GitHub (si tu n'en as pas)

https://github.com/signup — gratuit, 2 min.

### 1.2 Crée un dépôt privé pour PraxiTests

1. Connecte-toi à GitHub
2. Clique sur le **+** en haut à droite → **New repository**
3. **Repository name** : `praxitests` (ou ce que tu veux)
4. Coche **Private** (recommandé)
5. **Create repository**

### 1.3 Upload ton code via l'interface web GitHub

> **Sans terminal, juste le navigateur :**

1. Sur la page de ton dépôt fraîchement créé, clique **uploading an existing file**
2. Drag & drop **tout le contenu** du dossier `C:\Users\Fradin Alexandre\Documents\Claude\Projects\PraxiTests\` (sauf `node_modules/` et `vendor/` s'ils existent — ils ne devraient pas)
3. ⚠ **Important** : assure-toi d'inclure le dossier caché `.github/` (les workflows).
4. **Limit GitHub web upload** : 100 fichiers max par batch. Si plus, fais plusieurs batches OU utilise **GitHub Desktop** (point 1.3.bis).
5. En bas, écris « Initial commit » → **Commit changes**

### 1.3.bis Alternative : GitHub Desktop (recommandé si beaucoup de fichiers)

1. Télécharge **GitHub Desktop** : https://desktop.github.com (gratuit, GUI Windows)
2. Installe + connecte ton compte
3. **File → Add Local Repository** → sélectionne `C:\Users\Fradin Alexandre\Documents\Claude\Projects\PraxiTests`
4. Tu verras tous les fichiers en attente
5. Bas-gauche : message « Initial commit » → **Commit to main**
6. Bouton **Publish repository** en haut → **Private** → **Publish repository**

### 1.4 Le build se lance automatiquement

Sur la page de ton dépôt GitHub :

1. Clique l'onglet **Actions** (en haut)
2. Tu verras un workflow nommé **Build PraxiTests Release Zip** en cours (rond jaune qui tourne)
3. Attends ~3 à 5 minutes
4. Quand le rond passe en vert ✓ : le build est terminé

> Si le rond passe rouge ✗ : clique dessus, regarde l'erreur. Souvent c'est un fichier manquant. Tu peux re-uploader et relancer manuellement via **Run workflow**.

---

## Étape 2 — Télécharge le zip généré

1. Sur GitHub → onglet **Actions** → clique sur le workflow réussi (rond vert)
2. En bas de la page, section **Artifacts** : clique sur **praxitests-XXXXXXXX-YYYYYYY**
3. Le zip se télécharge sur ton ordinateur (~50 Mo)
4. **Décompresse-le** : tu obtiens un fichier `praxitests-XXXXXXXX-YYYYYYY.zip` (oui, GitHub zippe le zip — c'est normal)

> Tu obtiens donc 2 zips imbriqués. Le **vrai** zip à uploader est celui à l'intérieur.

### Astuce : créer une « release » officielle (optionnel)

Pour une version finale propre :

1. Sur GitHub → page du dépôt → bouton **Create a new release** (à droite)
2. Tag : `v1.0.0`
3. Title : `PraxiTests 1.0.0`
4. **Publish release**
5. Le workflow refait le build et **attache le zip directement à la release** — téléchargeable en 1 clic depuis la page Releases.

---

## Étape 3 — Upload sur OVH via FileZilla

### 3.1 Prépare OVH avant upload

Connecte-toi à **OVH Manager** (https://www.ovh.com/manager/) :

#### a) Active PHP 8.2

→ **Hébergements** → ton hébergement → **Multisite** → trouve ton domaine → **Modifier** → **Version PHP** = `8.2` → **Valider**

#### b) Crée une base MySQL

→ **Bases de données** → **Créer une base** :
- Type : `MySQL Privée`
- Nom : `praxitests` (ou `mondomaine_praxi`)
- Mot de passe : génère 16 caractères

> **Note bien l'hôte** affiché ensuite : c'est `XXXX.mysql.db` (pas `localhost`). Tu en auras besoin à l'étape 4.

#### c) Crée un sous-domaine (recommandé)

→ **Domaines** → ton domaine → **Sous-domaines** → **Ajouter** :
- Sous-domaine : `praxitests` → final URL `https://praxitests.tonsite.fr`
- **Cible / dossier racine** : laisse vide pour l'instant (à modifier plus tard)

→ Active aussi **SSL gratuit Let's Encrypt** (case à cocher).

### 3.2 Récupère tes identifiants FTP OVH

OVH Manager → **Hébergements** → ton hébergement → onglet **FTP-SSH** :
- **Hôte** : `ftp.cluster0XX.hosting.ovh.net` (le numéro varie)
- **Utilisateur** : ton login OVH (souvent `tondomaine.fr-`)
- **Mot de passe** : si oublié, **Réinitialiser** depuis l'OVH Manager

### 3.3 Configure FileZilla

1. Télécharge **FileZilla Client** : https://filezilla-project.org/download.php?type=client
2. Installe (gratuit, 5 min)
3. **Fichier → Gestionnaire de sites → Nouveau site** :
   - Hôte : tes credentials FTP OVH
   - Protocole : **FTP**
   - Chiffrement : **TLS explicite**
   - Type de connexion : **Normale**
   - Identifiant + Mot de passe
4. **Connexion**

### 3.4 Upload

1. Côté droit de FileZilla : navigue dans `/www/` (la racine de tes sites OVH)
2. Crée un dossier `praxitests` (clic-droit → **Créer un dossier**)
3. Entre dans `/www/praxitests/`
4. Côté gauche : ouvre le dossier où tu as **décompressé le zip GitHub** (le vrai contenu, pas le zip de zip)
5. **Sélectionne TOUT** (Ctrl+A) → drag & drop vers la droite
6. ⚠ **Patience** : 50 Mo + ~3000 fichiers via FTP = **15 à 30 minutes**

> Pendant l'upload, tu peux préparer l'étape 4 (compte admin, etc.).

### 3.5 Configure le sous-domaine sur `praxitests/public`

Une fois l'upload **terminé** :

OVH Manager → **Multisite** → trouve ton sous-domaine `praxitests.tonsite.fr` → **Modifier** :
- **Dossier racine** : `praxitests/public` (très important — pas `praxitests/` mais `praxitests/public`)
- **Valider**

→ Attends ~1 min que la config soit prise en compte.

---

## Étape 4 — Lance l'installeur web

1. Ouvre dans ton navigateur : `https://praxitests.tonsite.fr/install.php`
2. Tu vois la page **Bienvenue** → clique **Commencer**
3. **Étape 1 — Vérification système** : tout doit être ✓. Si non, vérifie permissions (étape 5 ci-dessous).
4. **Étape 2 — Base de données** :
   - Type : `MySQL`
   - Hôte : celui noté à 3.1.b (style `XXXX.mysql.db`)
   - Port : `3306`
   - Nom de la base : ce que tu as créé
   - Utilisateur : pareil
   - Mot de passe : pareil
   - **Tester la connexion** → ✓
5. **Étape 3 — Compte admin** : ton nom, ton email, mot de passe fort
6. **Étape 4 — Marque & URL** : nom plateforme, URL = `https://praxitests.tonsite.fr`
7. **Étape 5 — SMTP** : si tu as Mailgun/Brevo/etc., remplis. Sinon laisse vide pour l'instant.
8. **Étape 6 — Licence** : laisse vide
9. **Étape 7 — Lancer l'installation** → patience ~30 secondes
10. **Page « Installation terminée »** ✓

Tu peux te connecter sur `https://praxitests.tonsite.fr/login`.

---

## Étape 5 — Si l'installeur signale des permissions

Sur OVH Perso, certains dossiers ont besoin de permissions élargies. Via FileZilla :

1. Clic-droit sur le dossier `storage/` → **Attributs des fichiers**
2. Cocher **Inclure les sous-dossiers**
3. Valeur numérique : `755`
4. **OK**

Pareil pour `bootstrap/cache/`.

Recharge `install.php?step=requirements` — tout doit passer ✓.

---

## Mise à jour vers nouvelle version

Quand une nouvelle version sort :

1. Push tes changements sur GitHub (ou utilise **Run workflow** sur Actions tab)
2. Télécharge le nouveau zip
3. Sauvegarde ton fichier `.env` actuel (download via FileZilla)
4. Upload le nouveau contenu via FileZilla (écrase l'ancien)
5. Re-upload ton `.env`
6. Ouvre `https://praxitests.tonsite.fr/install.php?force=1` → l'installeur détecte l'install existante et lance juste les nouvelles migrations.

---

## Ce qui peut planter (et solutions)

### Build GitHub Actions échoue

→ Regarde les logs (clic sur l'étape rouge). Souvent un fichier manquant ou un test qui rate.
→ Tu peux désactiver les tests en supprimant `tests/` avant l'upload GitHub.

### FTP super lent / coupe la connexion

→ Utilise **rsync over SFTP** si possible, ou divise l'upload en plusieurs sessions.
→ Active **transfert binaire** dans FileZilla : Transfert → Type de transfert → **Binaire**.

### `install.php` retourne 500

→ Vérifie permissions (étape 5).
→ Vérifie PHP 8.2 actif (étape 3.1.a).
→ Vérifie que le sous-domaine pointe bien sur `praxitests/public` (pas `praxitests/`).

### « Class not found »

→ Le `vendor/` n'a pas été uploadé entièrement. Re-upload juste le dossier `vendor/`.

### Sous-domaine retourne « Index of /praxitests »

→ Document root pas configuré. Étape 3.5 — doit être `praxitests/public`.

### `npm run build` n'a pas généré `public/build/`

→ Soit le workflow GitHub a échoué, soit c'est dans le zip mais pas uploadé. Vérifie via FileZilla que `praxitests/public/build/` existe.

---

## Résumé : le compte combiné

| Action | Outils requis | Coût | Temps |
|--------|---------------|------|-------|
| Compte GitHub | navigateur | gratuit | 2 min |
| Upload code GitHub | navigateur ou GitHub Desktop | gratuit | 5-10 min |
| Build cloud (Actions) | rien — automatique | gratuit | 5 min (asynchrone) |
| Téléchargement zip | navigateur | gratuit | 30 sec |
| FileZilla | installation client | gratuit | 5 min setup |
| Upload OVH | FileZilla | inclus dans Perso | 15-30 min |
| Config OVH (DB, domain) | OVH Manager | inclus | 5 min |
| Wizard install.php | navigateur | inclus | 5 min |
| **Total** | | **gratuit** | **~45-60 min** |

---

## Alternative : faire builder le zip par quelqu'un d'autre

Si tu ne veux pas même créer un compte GitHub, tu peux :

1. **Demander à un dev** de runner `make-release.bat` sur sa machine et te transmettre le zip
2. **Utiliser Replit ou Gitpod** (envs Linux dans ton navigateur) pour build une fois
3. **Acheter le zip pré-buildé** auprès de Praxis Accompagnement (en mode service)

Mais GitHub Actions reste la solution la plus propre, gratuite et automatisée.

---

## Aide ?

Bloqué ? Note précisément :
- À quelle étape (1-5)
- Le message d'erreur exact
- Une capture d'écran si possible

Bon déploiement.
