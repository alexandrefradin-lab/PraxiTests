# Protection anti-copie de PraxiQuest

Dispositif en quatre volets, pilotés depuis `config/protection.php` et le `.env`.
Tout ce qui bloque est **désactivé ou en mode `warn` par défaut** : on observe
d'abord, on serre ensuite.

---

## Ce que ce dispositif fait — et ne fait pas

**Il rend la copie coûteuse et traçable.** Il ne la rend pas impossible.

Aucune protection logicielle ne résiste à quelqu'un qui détient le code source :
il peut toujours commenter l'appel au vérificateur de licence. L'objectif réel
est triple :

1. **arrêter la copie opportuniste** — le prestataire qui repart avec le dépôt,
   la sauvegarde qui traîne, le développeur qui « réutilise la base » ;
2. **caractériser l'intention** — contourner un verrou explicite n'est pas un
   accident : c'est un élément à charge en contrefaçon ;
3. **laisser une trace** — savoir *qu'*on est copié, et par qui.

La barrière qui tient vraiment reste juridique : dépôt de preuve d'antériorité
des sources (enveloppe Soleau ou équivalent), mention de licence propriétaire
dans les fichiers, contrats de prestation avec clause de propriété intellectuelle.
Ce dispositif est ce qui donne des faits à ces démarches.

---

## Volet 1 — Licence liée au domaine

Une instance redéployée hors des domaines licenciés refuse de servir.

Le jeton de licence est signé RSA-SHA256 avec une clé privée que **toi seul**
détiens. Le serveur ne porte que la clé publique, figée dans
`config/protection.php`. Un copieur ne peut pas forger un jeton pour son domaine.

### Mise en place (une fois)

```powershell
# 1. Sur TON POSTE — génère la paire de clés
php artisan praxiquest:license:keygen --out=cle-privee-praxiquest.pem
```

- La **clé publique** affichée se colle dans `config/protection.php` →
  `license.public_key` (elle est versionnée, c'est normal).
- La **clé privée** part dans ton gestionnaire de mots de passe, puis le fichier
  est supprimé du disque. **Si tu la perds, tu ne peux plus émettre de licence.**

```powershell
# 2. Sur TON POSTE — émets le jeton
php artisan praxiquest:license:issue `
    --domain=praxiquest.fr --domain='*.praxiquest.fr' `
    --licensee="Praxis Accompagnement" --days=730 `
    --private-key=cle-privee-praxiquest.pem
```

```bash
# 3. Sur le serveur OVH (PuTTY/plink) — .env puis
php artisan config:cache
php artisan praxiquest:license:status
```

### Bascule progressive

| Étape | `.env` | Effet |
|---|---|---|
| Départ | `PRAXIQUEST_LICENSE_ENFORCED=false` | Aucun contrôle |
| Observation | `ENFORCED=true`, `MODE=warn` | Journalise les anomalies, ne coupe rien |
| Application | `MODE=block` | 503 sur toute instance hors licence |

Reste toujours servi, même hors licence : `/up` (supervision), `/stripe/webhook`
(sinon on perd des événements de facturation), les pages légales.

Une licence expirée bénéficie de `PRAXIQUEST_LICENSE_GRACE_DAYS` (14 j) avant
blocage — un renouvellement oublié ne coupe pas la production un dimanche.

---

## Volet 2 — Anti-aspiration du contenu

Le patrimoine de PraxiQuest, ce sont les questionnaires, les barèmes et les
restitutions. Le middleware `protect-content` mesure la cadence de consultation
du contenu protégé par compte (ou par IP si non authentifié).

Deux signaux :
- **outil d'aspiration annoncé** (`curl`, `scrapy`, `puppeteer`…) → blocage ;
- **cadence impossible pour un lecteur humain** → blocage temporisé (60 min).

Un user-agent absent ne bloque pas — certains proxys d'entreprise le suppriment —
mais divise le seuil de cadence par quatre.

Routes couvertes : `/tests`, `/tests/{slug}`, `/attempt/{id}`, `/results/{id}`,
`/grimoire`. Les routes de *polling* (`…/status`) en sont exclues : leur cadence
normale est élevée et fausserait la mesure.

Pour en couvrir une nouvelle :

```php
Route::get('/ma-route', [MonController::class, 'show'])->middleware('protect-content');
```

**Avant de passer en `block`** : laisser tourner une semaine en `warn`, lire
`php artisan praxiquest:protection:report`, et caler `MAX_HITS` au-dessus du
maximum observé chez les candidats réels.

---

## Volet 3 — Partage et revente d'accès

Un organisme qui paie une licence et fait passer les tests de dix structures
duplique le produit sans le payer — c'est une copie au même titre qu'un vol de
code, et statistiquement la plus fréquente en SaaS B2B.

Deux indices croisés, sur les comptes **professionnels uniquement** (candidats
et admins exemptés) :

- nombre d'**appareils** distincts sur 30 jours (défaut : 5) ;
- nombre de **réseaux** distincts (préfixe /24) sur 24 h (défaut : 8).

L'empreinte d'appareil est un condensat du user-agent et de la langue, salé par
`APP_KEY`. Aucune donnée nouvelle n'est collectée — uniquement ce que le
navigateur transmet déjà. Écriture en base limitée à une fois par quart d'heure
et par appareil.

En mode `block`, le compte n'est pas déconnecté sèchement : il est renvoyé vers
la page des offres avec une invitation à souscrire des accès supplémentaires.
Couper un organisme en pleine session de tests coûterait plus cher que le partage.

**RGPD** : à mentionner dans la politique de confidentialité (finalité : sécurité
du compte, base légale : intérêt légitime, conservation : 60 jours). La purge
quotidienne est déjà planifiée (`protection:purge` dans `routes/console.php`).

---

## Volet 4 — Traçage des rapports PDF

Chaque rapport porte en pied de page une mention nominative et une référence
unique au couple (compte, document) — deux personnes qui téléchargent le même
rapport obtiennent deux références différentes.

L'effet principal est **dissuasif** : un lecteur qui voit son nom sur le document
le fait moins circuler. L'effet secondaire est **forensique** :

```bash
php artisan praxiquest:pdf:trace 3F1A-90BC-77DE --document=results:412
```

L'identifiant de document se lit dans le journal (`laravel.log`, entrée
« PDF téléchargé »). La commande balaie les comptes, désigne le titulaire et
consigne une alerte `pdf_leak`.

⚠️ Les références dépendent de `APP_KEY` : changer cette clé rend illisibles
tous les PDF déjà émis.

---

## Revue hebdomadaire

```bash
php artisan praxiquest:protection:report --days=7
```

Affiche l'état des quatre volets, le décompte des anomalies par type, les
alertes non traitées et les comptes aux appareils les plus dispersés.

Sans ce rendez-vous, les alertes s'accumulent sans être lues — et le dispositif
ne sert à rien.

---

## Fichiers

| Rôle | Fichier |
|---|---|
| Configuration | `config/protection.php` |
| Licence | `app/Core/Protection/LicenseService.php`, `LicenseStatus.php` |
| Anti-scraping | `app/Core/Protection/ScrapingGuard.php` |
| Partage de comptes | `app/Core/Protection/DeviceGuard.php` |
| Traçage PDF | `app/Core/Protection/DocumentWatermark.php` |
| Middlewares | `app/Http/Middleware/{VerifyLicense,ProtectContent,TrackDevice}.php` |
| Modèles | `app/Models/{ProtectionAlert,UserDevice}.php` |
| Commandes | `app/Console/Commands/{LicenseKeygen,LicenseIssue,LicenseStatus,PdfTrace,ProtectionReport}.php` |
| Migration | `database/migrations/2026_07_21_000004_create_protection_tables.php` |
| Tests | `tests/Feature/ProtectionTest.php` |
