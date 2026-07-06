# Sécurité, sauvegardes & risques — PraxiQuest

> Document de référence rédigé le 6 juillet 2026, à l'issue de l'audit pré-lancement.
> À relire avant l'ouverture commerciale, puis à chaque trimestre.

---

## 1. Sauvegardes — état des lieux

### Ce qui est déjà sauvegardé ✅

| Donnée | Où | Rétention | Restauration |
|---|---|---|---|
| **Base de données** `decisivpraxitest` (candidats, réponses, résultats, leads) | Sauvegardes automatiques OVH | **31 jours** (quotidien) | Manager OVH → Hébergement → Bases de données → ⋯ → Restaurer |
| **Fichiers du site** (dont CV uploadés dans `storage/`) | Snapshots automatiques OVH | J-1 à ~J-14 | Manager OVH → Hébergement → FTP-SSH → Restaurer une sauvegarde |
| **Code source** (+ assets buildés) | GitHub `alexandrefradin-lab/PraxiTests` | Illimité | `git clone` + deploy |

### Les points faibles identifiés ⚠️

1. **Le `.env` de production n'existe QUE sur le serveur** (exclu de git, à juste titre).
   Il contient l'`APP_KEY` : sa perte rend **définitivement indéchiffrables** les clés API
   chiffrées en base (Anthropic, etc.), même avec toutes les sauvegardes du monde.
2. **Tout vit chez OVH** : le site, la base ET ses sauvegardes. Un sinistre majeur
   (précédent réel : incendie du datacenter OVH Strasbourg, 2021) ou un piratage du
   compte OVH peut tout emporter d'un coup.
3. **Aucune restauration n'a encore été testée.** Une sauvegarde non testée est un espoir,
   pas une garantie.

### La cible : règle « 3-2-1 »

3 copies des données, sur 2 supports, dont 1 hors site. Plan à coût nul :

- [ ] **Copie locale du `.env`** (à refaire après chaque modification du fichier) :
  ```powershell
  & "C:\Program Files\PuTTY\pscp.exe" decisiv@ssh.cluster121.hosting.ovh.net:praxiquest/.env "C:\Users\Fradin Alexandre\Documents\praxiquest-env-backup.txt"
  ```
  Ranger la copie dans un gestionnaire de mots de passe (idéal) ou hors du dossier git.
- [ ] **Dump quotidien de la base** vers `~/backups/` (tâche à ajouter au scheduler Laravel,
  rotation 7 jours) + **rapatriement hebdomadaire** en local via pscp.
- [ ] **Exercice de restauration** (une fois avant le lancement) : restaurer le dump de la
  veille dans la base secondaire vide (`decisiv42` ou la 3e base « à créer » du manager)
  et vérifier que les tables sont lisibles.

---

## 2. Risque de perte de données — scénarios

| Scénario | Probabilité | Perte max | Protection |
|---|---|---|---|
| Suppression accidentelle (lead, test, campagne, invitation) | Courante | Aucune | ✅ Corbeilles restaurables + journal d'audit + sauvegardes OVH |
| Panne / corruption de la base | Faible | **24 h de saisies** | ✅ Sauvegarde quotidienne OVH |
| Piratage du compte OVH ou d'un compte admin | Faible | Potentiellement tout | ⚠️ Dépend des mots de passe + 2FA (voir §3) |
| Sinistre majeur OVH (datacenter) | Très faible | Tout | ❌ tant que pas de copie hors OVH (voir §1) |
| Perte du `.env` / `APP_KEY` | Faible | Secrets chiffrés **irrécupérables** | ❌ tant que pas de copie locale |

**Rappels rassurants côté produit :**
- Chaque réponse de test est enregistrée **au clic** (pas à la fin du test) — un candidat
  interrompu reprend exactement où il s'était arrêté, même depuis un autre appareil.
- Une restauration de la veille ne fait donc perdre au pire que les réponses du jour.
- L'expiration d'une invitation (30 j) ne concerne que le lien d'inscription — un compte
  créé garde son accès et sa progression pour toujours.

---

## 3. Plan de sécurisation priorisé

### 🔴 Avant l'ouverture commerciale

- [ ] **Roter les 2 secrets exposés** lors de la session du 2026-07-04/06 (apparus en clair
  dans des terminaux/conversations) :
  - mot de passe de la boîte `contact@praxiquest.fr` (manager OVH → Zimbra → compte → modifier) ;
  - clé API Brevo `praxiquest-prod` (app.brevo.com → SMTP & API → régénérer, puis mettre à
    jour `BREVO_API_KEY` dans le `.env` + `php artisan config:cache`).
- [ ] **Activer la vérification d'email** : `REQUIRE_EMAIL_VERIFICATION=true` dans le `.env`
  (les emails fonctionnent via Brevo depuis le 2026-07-06, plus de raison de la laisser
  éteinte). Sans elle, inscription possible avec des adresses bidon.
- [ ] **Copie locale du `.env`** (cf. §1).
- [ ] **2FA sur le compte OVH** (le compte qui contrôle domaine + serveur + sauvegardes).
- [ ] **Headers de sécurité HTTP** (middleware Laravel) : `X-Frame-Options`,
  `X-Content-Type-Options`, `Referrer-Policy`, HSTS. ~30 min de dev.
- [ ] **Audit des dépendances** : `php composer.phar audit` (sur le serveur) et `npm audit`
  (en local) — vérifier qu'aucune librairie n'a de CVE connue.

### 🟠 Premières semaines

- [ ] **2FA obligatoire** pour les comptes `admin` (aujourd'hui optionnelle).
- [ ] **Race conditions** de l'audit 2026-06 : QC-14 (double-clic « Terminer » = double
  tentative) et QC-13 (XP compté deux fois).
- [ ] **Sentry** (plan gratuit) : être alerté par email des erreurs de production au lieu
  de consulter les logs à la main.
- [ ] **Captcha Turnstile** sur l'inscription — déjà scaffoldé dans `config/praxiquest.php`,
  à activer si le honeypot laisse passer du spam.
- [ ] Mettre en place les **dumps quotidiens + rapatriement hebdo** (cf. §1).

### 🟢 Post-lancement

- [ ] Scan externe type OWASP ZAP sur le site en production.
- [ ] Adresse de signalement sécurité (ex. `security@praxiquest.fr` ou mention dédiée).
- [ ] Exercice de restauration semestriel.

### Déjà en place (ne pas refaire) ✅

- Installeur `install.php` neutralisé à chaque déploiement (C-1).
- Rate limiting : login 5/min, inscription 10/h, reset 3/10 min, Oracle 20/min.
- XSS campagnes email corrigé (`HtmlSanitizer::clean`).
- 2FA disponible admins/pros (codes de secours hachés SHA-256).
- Honeypot anti-bot à l'inscription.
- Clés API IA chiffrées en base (AES), masquées dans l'admin.
- Policies multi-tenant (Lead / EmailCampaign / TestInvitation) — un professionnel ne voit
  que ses propres données.
- Journal d'audit consultable (`/admin/audit-logs`) : leads, tests, campagnes, utilisateurs,
  invitations, réglages, exports.
- Consentement RGPD au partage des résultats (SEC-M12) capturé à l'inscription via invitation.
- Corbeilles restaurables sur toutes les entités admin.

---

## 4. Rappels d'exploitation

| Sujet | Détail |
|---|---|
| **Envoi d'emails** | API Brevo uniquement (HTTPS). SMTP/sendmail **bloqués** sur OVH mutualisé — ne jamais y revenir. Ne jamais tester l'envoi depuis SSH (tout le réseau sortant y est bloqué) : tester depuis le site. |
| **Statistiques emails** | app.brevo.com → Transactionnel → Logs (délivrés / ouverts / cliqués). |
| **File d'attente** | Cron OVH horaire (minute 42) → `cron-scheduler.php` → `schedule:run` → `queue:work --stop-when-empty`. Les invitations partent en direct (pas par la file) ; la file ne porte que campagnes / relances / IA en retard. |
| **Erreurs prod** | `grep 'production.ERROR' ~/praxiquest/storage/logs/laravel.log \| tail -3` via plink. |
| **Déploiement** | Local : build + commit + push → serveur : `cd ~/praxiquest && bash deploy-server.sh`. |
| **Montée en charge** | ~150-200 utilisateurs simultanés sur l'infra actuelle. Goulot : les synthèses IA à la fin des tests (attention aux passations en cohorte). Plan détaillé : `SCALING_1000_USERS.md` (VPS 12-20 €/mois → 800-1 200 simultanés). |

---

*Document maintenu avec l'assistance de Claude. Dernière mise à jour : 2026-07-06.*
