# Déploiement PraxiTests — guide client final

> **Pour qui ?** Toute personne qui veut installer PraxiTests sur son hébergement, sans toucher à une ligne de code.

## En 5 minutes, sans code

1. **Tu reçois un fichier** : `praxitests-1.0.0-alpha.zip` (~ 50 Mo)
2. **Tu l'uploades** sur ton hébergement (FTP, cPanel, ou panneau OVH/Infomaniak)
3. **Tu décompresses** dans le dossier de ton domaine
4. **Tu pointes le domaine** vers le sous-dossier `public/`
5. **Tu ouvres** `https://ton-domaine.com/install.php` et tu suis l'assistant

C'est tout. Aucun terminal, aucun code, aucune compétence technique avancée.

---

## Prérequis hébergeur

Vérifie auprès de ton hébergeur ces 3 points :

| Élément | Valeur min | Comment vérifier |
|---------|-----------|-------------------|
| **PHP** | 8.2+ | Plupart des hébergeurs offrent un sélecteur dans cPanel / OVH / etc. |
| **MySQL ou MariaDB** | 8 / 10.6+ | Crée une base vide depuis le panneau d'administration |
| **Extensions PHP** | pdo, mbstring, openssl, fileinfo, json, xml, ctype, curl, bcmath | Demande au support si tu n'es pas sûr |

> **Hébergeurs validés** : OVH, o2switch, Infomaniak, IONOS, PlanetHoster, Hostinger Premium, tout VPS avec PHP 8.2.
> **À éviter** : hébergements ultra-bas de gamme avec PHP < 8.2 ou sans MySQL.

---

## Étape 1 — Créer une base de données vide

Connecte-toi au panneau de ton hébergeur, va dans la section **MySQL** ou **Bases de données** et crée :

- **Nom de la base** : `praxitests` (ou ce que tu veux)
- **Utilisateur** : `praxitests` (ou ce que tu veux)
- **Mot de passe** : génère-en un solide (12+ caractères)
- **Hôte** : généralement `localhost` (l'hébergeur te le donnera)

Note ces 4 infos, tu en auras besoin à l'étape 4.

---

## Étape 2 — Uploader les fichiers

### Option A — via le gestionnaire de fichiers cPanel / Plesk / OVH

1. Connecte-toi à ton panneau d'administration
2. Va dans **Gestionnaire de fichiers**
3. Navigue jusqu'au dossier de ton domaine (souvent `public_html/` ou `www/`)
4. Clique **Upload** et choisis le zip `praxitests-1.0.0-alpha.zip`
5. Une fois uploadé, fais clic-droit → **Extraire**
6. Tu obtiens une arborescence avec `app/`, `public/`, `vendor/`, etc.

### Option B — via FTP (FileZilla)

1. Configure FileZilla avec les credentials FTP de ton hébergeur
2. Décompresse le zip sur ton ordinateur d'abord
3. Glisse-dépose tout le contenu dans le dossier de ton domaine
4. **Attention** : c'est plus lent (~10-15 min pour ~50 Mo)

---

## Étape 3 — Pointer le domaine vers `public/`

PraxiTests, comme toute application Laravel, doit servir le dossier `public/` (pas la racine).

### Option A — Hébergement mutualisé (cPanel / OVH / etc.)

Crée un **sous-domaine** ou **modifie le domaine principal** pour qu'il pointe vers le sous-dossier `public/`.

Dans la plupart des panneaux : **Domaines** → **Modifier** → **Document Root** → indique `praxitests/public` (ou le chemin où tu as extrait).

### Option B — Tu n'as pas accès au document root

Place plutôt **un fichier .htaccess** à la racine de ton domaine (à côté du dossier `praxitests/`) avec :

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ praxitests/public/$1 [L]
</IfModule>
```

> Demande à ton hébergeur la procédure exacte si tu hésites.

### Option C — VPS / serveur dédié (Apache/Nginx)

**Apache vhost** :
```apache
<VirtualHost *:80>
    ServerName praxitests.tonsite.com
    DocumentRoot /var/www/praxitests/public
    <Directory /var/www/praxitests/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Nginx** :
```nginx
server {
    server_name praxitests.tonsite.com;
    root /var/www/praxitests/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

## Étape 4 — Lancer l'installeur web

Ouvre simplement dans ton navigateur :

```
https://ton-domaine.com/install.php
```

L'assistant en 7 étapes :

| # | Étape | Ce qu'on te demande |
|---|-------|---------------------|
| 1 | Bienvenue | Rien — clique « Commencer » |
| 2 | Vérification système | Aucune action — l'installeur vérifie PHP, extensions, permissions. **Tout doit être ✓ pour continuer.** |
| 3 | Base de données | Renseigne les 4 infos notées à l'étape 1 |
| 4 | Compte admin | Ton nom, ton email, un mot de passe fort |
| 5 | Marque & URL | Nom de ta plateforme, URL publique, couleur principale |
| 6 | Mail SMTP | Hôte SMTP de ton hébergeur ou Mailgun / Mailjet / Brevo |
| 7 | Licence | Ta clé de licence ou laisse vide pour mode démo |

Puis l'installeur exécute **automatiquement** :

- Création des ~14 tables de base de données
- Insertion des badges, rôles, permissions
- Création de ton compte admin
- Activation des 5 plugins de tests (PraxiMet, PraxiValeurs, PraxiCare, PraxiEmo, PraxiMum)
- Verrouillage de l'installeur (sécurité)

Si tout se passe bien : **page « Installation terminée »** → tu peux te connecter !

---

## Étape 5 — Première connexion

Va sur `https://ton-domaine.com/login` et connecte-toi avec l'email + mot de passe créés à l'étape 4.

Tu arrives sur le **tableau de bord admin**. Tes 5 tests sont déjà disponibles dans l'onglet « Tests ».

---

## Permissions de fichiers (si l'installeur signale un problème)

Sur certains hébergements, il faut donner les permissions d'écriture à 2 dossiers :

```
storage/         (lecture + écriture)
bootstrap/cache/ (lecture + écriture)
```

Via cPanel : clic-droit sur le dossier → **Permissions** → cocher **Lecture/Écriture** pour le propriétaire (ou `755`).
Via FTP : clic-droit → **Permissions** → `755` (ou `775` si nécessaire).

---

## Ajouter / configurer l'IA (synthèse + 15 métiers)

Pour activer la génération IA, après installation :

1. Va dans `app/storage/app/.env` (depuis le gestionnaire de fichiers ou FTP)
2. Modifie ces lignes :

```dotenv
AI_DEFAULT_DRIVER=anthropic
ANTHROPIC_API_KEY=sk-ant-...    # ta clé Anthropic
```

(ou OpenAI : `OPENAI_API_KEY=sk-...`)

3. Sauvegarde, c'est tout.

> Sans clé : les tests fonctionnent normalement, mais la synthèse IA reste vide.

---

## Mise à jour vers une nouvelle version

1. Reçois le zip de la nouvelle version
2. Sauvegarde ta base de données (panneau hébergeur → Export SQL)
3. Sauvegarde ton fichier `.env` actuel (à conserver !)
4. Décompresse le nouveau zip par-dessus l'ancien (écrase tout SAUF `.env`)
5. Ouvre `https://ton-domaine.com/install.php?force=1` — l'installeur détectera l'install existante et lancera juste les nouvelles migrations

---

## Réinstaller / repartir de zéro

1. Supprime le fichier `storage/app/.installed`
2. Vide la base de données (panneau hébergeur → Effacer les tables)
3. Ouvre `https://ton-domaine.com/install.php`

---

## En cas de problème

### « 500 Internal Server Error » après installation

→ Permissions insuffisantes sur `storage/`. Mets `775` sur `storage/` et tous ses sous-dossiers.

### « Class not found » dans les logs

→ Cache pourri. Supprime tous les fichiers dans `bootstrap/cache/` (sauf `.gitignore`).

### « Database connection refused »

→ Vérifie host (souvent `localhost`, parfois IP), port (3306 par défaut), utilisateur, mot de passe.

### Page blanche

→ Active `APP_DEBUG=true` dans `.env` temporairement pour voir l'erreur exacte. **Remets `false` ensuite.**

### Email pas envoyé

→ Vérifie config SMTP, et **ajoute un sender vérifié** chez ton fournisseur (Mailgun, Brevo, etc.).

---

## Ce qui n'est PAS dans le zip (à ajouter selon besoin)

- **Certificat SSL** : généralement géré par ton hébergeur (Let's Encrypt en 1 clic)
- **Backup automatique** : à configurer avec ton hébergeur
- **CDN / cache** : optionnel pour optimiser la performance

---

## Support

Si ton installation bloque, prends une capture d'écran de l'erreur exacte (avec l'URL) et envoie-la au support.

> Conseil : ne change rien dans le code. Si tu as besoin d'une fonctionnalité, attends une mise à jour ou demande au support.
