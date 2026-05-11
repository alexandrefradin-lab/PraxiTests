# PraxiMum — Contexte Plugin WordPress v1.4.1
## État au 17 mars 2026

## IDENTITÉ DU PROJET
- **Plugin** : PraxiMum — Test de personnalité Big Five (OCEAN)
- **Site** : https://www.praxis-accompagnement.com
- **Auteur** : Alexandre Fradin
- **Version actuelle** : 1.4.1
- **Shortcode principal** : `[test_personnalite_solo]`
- **Page test** : https://www.praxis-accompagnement.com/test-personnalite-gratuit-praximum/
- **Page profil** : `/profil/[token-32-chars]/`

---

## STRUCTURE DES FICHIERS

```
plugin-personnalite/
├── plugin-personnalite.php          ← fichier principal v1.4.1
├── includes/
│   ├── class-pp-archetypes.php      ← 16 archétypes + descriptions longues (~70 mots)
│   ├── class-pp-calculator.php      ← calcul scores T, échelle 1-4, amplification ×1.15
│   ├── class-pp-db.php              ← VERSION 1.3 — colonne relance_bloquee ajoutée
│   ├── class-pp-mailer.php          ← SMTP natif via phpmailer_init, from contact@praxis
│   ├── class-pp-public-profil.php
│   ├── class-pp-questions.php       ← 128 questions + normes recalibrées pour échelle 1-4
│   ├── class-pp-shortcode.php
│   ├── class-pp-rgpd.php
│   ├── class-pp-relances.php
│   ├── class-pp-batch.php
│   ├── class-pp-codes.php
│   ├── class-pp-health.php
│   ├── class-pp-logger.php
│   ├── class-pp-pdf.php
│   └── class-pp-security.php
├── templates/
│   ├── form-solo.php                ← shortcode solo, design v1, bandeau mi-parcours q64
│   ├── form.php
│   ├── public-profil.php            ← page profil public, design v1, couleurs hardcodées
│   ├── email-resultats.php
│   ├── equipe.php
│   └── politique.php
├── assets/
│   ├── js/
│   │   ├── front.js
│   │   └── pp-pdf-client.js
│   └── css/
│       └── front.css
└── admin/
    ├── class-pp-admin.php           ← SMTP settings + toggle relance_bloquee
    └── views/
        ├── resultats.php            ← colonne Relances avec case à cocher AJAX
        └── ...
```

---

## CHOIX TECHNIQUES IMPORTANTS

### Échelle de réponse
- **4 choix** : `Pas moi / Un peu moi / Assez moi / Tout à fait moi`
- Inversion : `5 - val` (pour val 1-4)
- Normes recalibrées ×0.8 depuis base 1-5 dans `get_normes()`
- Amplification scores : ×1.15 dans `compute_T()`

### SMTP natif (sans plugin externe)
- Hook `phpmailer_init` dans `PP_Mailer::init()`
- Réglages dans WP Admin → PraxiMum → Réglages → 📧 Envoi des emails
- Options : `pp_smtp_host`, `pp_smtp_port`, `pp_smtp_user`, `pp_smtp_pass`, `pp_smtp_secure`, `pp_smtp_from`
- Expéditeur fixe : `contact@praxis-accompagnement.com`
- Serveur OVH : `ssl0.ovh.net`, port `465`, SSL
- `Reply-To` → `pp_admin_email` (alex.fradin@gmail.com)

### Relances automatiques
- J+3 : *"Alexandre, votre profil [archétype] vous réserve encore des surprises"*
- J+8 : *"8 jours déjà — et si on transformait votre profil [archétype] en plan d'action, Alexandre ?"*
- Textes orientés bilan de compétences (modifiables dans Réglages)
- Colonne `relance_bloquee` en BDD — case à cocher par candidat dans la liste admin
- `get_pending_relances()` exclut les candidats avec `relance_bloquee = 1`

### Design pages résultats (v1)
- **Hero sombre** (`couleur2`) avec emoji, prénom, archétype, badge rareté
- **Description** avec bordure gauche `couleur1`
- **Traits** en pills sombres
- **5 dimensions** dans card blanche
- **Téléchargements** regroupés (PDF + carte)
- **CTA bilan** fond `#1E2A3A`, label + bouton `#E8541A` hardcodés (résistance Avada)
- Appliqué sur : `form-solo.php` (renderResults JS) + `public-profil.php`

### Bandeau encouragement mi-parcours
- Apparaît à la **question 64** (index 63)
- Disparaît dès qu'on clique sur une réponse
- Masqué aussi lors de `submitSolo()`

### BDD — VERSION 1.3
- Nouvelle colonne `relance_bloquee TINYINT(1) DEFAULT 0`
- Migration auto au chargement si `pp_db_version !== '1.3'`
- `$cols` rechargé après chaque ALTER TABLE pour fiabilité

---

## BUGS RÉSOLUS (ne pas réintroduire)

1. ✅ `render_carte()` : réécriture sans `ob_start()` — évite conflits AJAX
2. ✅ `id="pp-carte-result"` : sur le div principal de la carte
3. ✅ `PP_AJAX` : objet `{url, nonce}` → utiliser `PP_AJAX_URL` dans les templates
4. ✅ Bandeau "Progression sauvegardée" : masqué dans `showQuestion()`
5. ✅ OG image dynamique : **abandonnée** — utiliser logo site
6. ✅ PHP GD pour OG image : **ne pas réessayer**
7. ✅ `handle_compat()` : `wp_die()` après chaque `wp_send_json_error()`
8. ✅ Prénom page profil : `color:#fff` dans le h1
9. ✅ Mails en spam : expéditeur `contact@praxis-accompagnement.com` + SMTP OVH
10. ✅ Dernière question visible après résultats : masquée dans `ppSoloAnswer` + `submitSolo` + `renderResults`
11. ✅ Bouton PDF remonté avant carte de partage dans `renderResults`
12. ✅ Boutons carte centrés : `justify-content:center`
13. ✅ Couleurs CTA page profil : `#1E2A3A` fond, `#E8541A` bouton — hardcodés avec `!important` (résistance Avada)
14. ✅ `PP_RELANCE_NONCE` dans resultats.php : guillemets simples dans `wp_create_nonce()`
15. ✅ Erreur fatale PHP site : guillemets doubles dans script inline → corrigé guillemets simples

---

## RÉGLAGES ADMIN (WP Admin → PraxiMum → Réglages)

| Option | Valeur configurée |
|--------|------------------|
| Email admin | alex.fradin@gmail.com |
| URL RDV | https://calendly.com/alex-fradin/15min |
| URL page test | https://www.praxis-accompagnement.com/ |
| URL politique | https://www.praxis-accompagnement.com/politique-confidentialite |
| URL page Merci | **VIDE** (laisser vide) |
| SMTP From | contact@praxis-accompagnement.com |
| SMTP Host | ssl0.ovh.net |
| SMTP Port | 465 |
| SMTP Secure | ssl |
| SMTP User | contact@praxis-accompagnement.com |
| SMTP Pass | *(à saisir — mot de passe webmail OVH)* |

---

## CHECKLIST À NE PAS OUBLIER

- [ ] Après chaque install : vider cache WP Rocket + Ctrl+Shift+R navigateur
- [ ] Réglages → Permaliens → Enregistrer (flush rewrite rules)
- [ ] URL page Merci = VIDE (sinon redirige vers mauvaise page)
- [ ] WP_DEBUG désactivé en production
- [ ] Saisir mot de passe SMTP dans Réglages après installation
- [ ] Vérifier que `relance_bloquee` est bien créée (Admin → État du plugin)

---

## CE QUI RESTE À FAIRE (backlog)

- Tracking clics RDV dans les mails (colonne `rdv_clique` existe mais non exploitée visuellement)
- Mail J+21 "dernier contact"
- Variables `{prenom}` et `{archetype}` dans les textes de relance admin
- Tableau de bord stats : taux clics RDV, tests/semaine, archétypes fréquents
- Filtre admin par statut relance
- Bouton "Renvoyer le mail de résultats" depuis fiche candidat
- Tests PDF sur Safari/Firefox
- Vérifier affichage mobile mode solo
- Futur : test d'orientation `[praxis_orientation_test]`
- Futur V2 : UTM tracking, score désirabilité sociale
