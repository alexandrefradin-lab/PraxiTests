# Plugin Personnalité — V1

## Installation

1. Compresser le dossier `plugin-personnalite/` en `.zip`
2. WordPress Admin → Extensions → Ajouter → Téléverser
3. Activer le plugin

## Configuration

WordPress Admin → **Personnalité → Réglages**

| Option | Description |
|--------|-------------|
| Email admin | Adresse qui reçoit une copie de chaque test |
| URL rendez-vous | Lien vers votre page de prise de RDV (ex: Calendly) |

## Utilisation

Insérez le shortcode dans n'importe quelle page ou article :

```
[test_personnalite]
```

## Fonctionnement

1. L'utilisateur répond aux **36 questions** (Big Five + Désirabilité Sociale) en 6 étapes de 6 questions
2. Il saisit prénom + email et accepte le RGPD
3. Les scores sont calculés (O / C / E / A / N / DS) en pourcentage
4. Un mail de résultat est envoyé à l'utilisateur **et** en copie à l'admin
5. Un CTA vers la prise de RDV s'affiche
6. Des relances automatiques partent **J+3** et **J+8**

## Structure des fichiers

```
plugin-personnalite/
├── plugin-personnalite.php       ← Fichier principal
├── includes/
│   ├── class-pp-db.php           ← BDD (création + requêtes)
│   ├── class-pp-questions.php    ← Banque de 36 questions
│   ├── class-pp-calculator.php   ← Calcul des scores
│   ├── class-pp-mailer.php       ← Envoi de mails
│   ├── class-pp-shortcode.php    ← Rendu du formulaire + AJAX
│   └── class-pp-relances.php     ← Cron relances J+3 / J+8
├── admin/
│   ├── class-pp-admin.php        ← Interface admin
│   └── views/
│       ├── resultats.php         ← Liste des résultats
│       └── detail.php            ← Détail d'un résultat
├── templates/
│   ├── form.php                  ← Formulaire front-end
│   └── email-resultats.php       ← Template mail utilisateur
└── assets/
    ├── css/front.css             ← Styles front
    ├── css/admin.css             ← Styles admin
    └── js/front.js               ← JavaScript du formulaire
```

## Table BDD

`{prefix}_pp_resultats` — colonnes :

`id`, `prenom`, `email`, `reponses` (JSON), `score_O/C/E/A/N/DS`, `date_soumis`, `consentement`, `source`, `relance_3j`, `relance_8j`, `rdv_clique`

## Critères V1 atteints ✅

- [x] Formulaire multi-étapes qui fonctionne
- [x] Calcul fiable (score inversé pris en compte)
- [x] Affichage résultats avec barres animées
- [x] Envoi mail utilisateur + copie admin
- [x] Interface admin (liste + détail)
- [x] Sécurité : nonce AJAX, sanitize, escape
- [x] RGPD : consentement obligatoire
- [x] Compatible mobile
- [x] Relances automatiques J+3 et J+8
- [x] Aucune dépendance externe
