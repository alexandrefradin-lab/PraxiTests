# PraxiSens — Test d'Hypersensibilité

Questionnaire d'hypersensibilité fondé sur la **Sensory Processing Sensitivity** (Aron & Aron, 1997 ;
structure factorielle Smolewska et al., 2006). 18 items, 3 sous-dimensions, restitution immédiate + e-mail.

## Cadre psychométrique
- **EOE — Sur-stimulation** : facilité de saturation, besoin de retrait (6 items)
- **AES — Sensibilité esthétique & profondeur** : subtilités, vie intérieure, émotion esthétique (6 items)
- **LST — Seuil sensoriel bas** : bruit, lumière, douleur, textures, caféine, faim (6 items)

Échelle de Likert 1→5 (émet 1..5, jamais 0 — conforme au contrat d'échelle PraxiQuest).
Scoring normalisé en % par dimension `(somme−n)/(4n)×100` et global. Paliers : faible <50 %,
présente 50-75 %, haute sensibilité marquée >75 %.

## Installation (OVH mutualisé)
1. Téléverser le dossier `praxisens/` dans `wp-content/plugins/` (FTP) **ou** installer `praxisens.zip`
   via Extensions → Ajouter → Téléverser.
2. Activer le plugin (crée la table `wp_praxisens_results`).
3. Insérer le shortcode **`[praxisens]`** sur une page.

## Configuration e-mail (SMTP OVH)
Dans `includes/email-functions.php`, renseigner :
```php
define( 'PRAXISENS_SMTP_USER', 'contact@votre-domaine.fr' );
define( 'PRAXISENS_SMTP_PASS', 'votre-mot-de-passe' );
define( 'PRAXISENS_FROM', 'contact@votre-domaine.fr' );
```
Hôte `ssl0.ovh.net`, port `465`, SSL (déjà configurés). Tant que `PRAXISENS_SMTP_PASS`
vaut `A_DEFINIR`, le plugin utilise le `wp_mail()` par défaut.

## UX (standard Cali)
Une question par écran · auto-avancement 280 ms · barre de progression fixe · bouton Précédent
uniquement · JS vanilla, aucune dépendance.

## Sécurité
Nonce WordPress sur l'AJAX, `sanitize_text_field` / `sanitize_email` / `absint`, valeurs bornées 1-5,
échappement HTML côté affichage.

## Import PraxiQuest
Ce plugin WordPress fait foi (source de vérité). Il pourra être converti en module Laravel
PraxiQuest selon le process habituel (discover --sync + plugins:activate praxisens).
