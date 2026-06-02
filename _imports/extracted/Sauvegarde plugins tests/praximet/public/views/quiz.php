<?php
/**
 * PraxiMet – Template du quiz RIASEC
 * Affiché via le shortcode [praximet_quiz]
 * Les réponses sont traitées via AJAX (module 2)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Charge les questions et les encode en JSON pour le JS
require_once PRAXIMET_PATH . 'data/questions-riasec.php';
$questions      = praximet_get_questions();
shuffle( $questions ); // Ordre aléatoire à chaque chargement
$questions_json = wp_json_encode( $questions );
?>

<div id="praximet-quiz"
     class="praximet-wrapper"
     data-questions="<?php echo esc_attr( $questions_json ); ?>">

    <!-- ── ÉTAPE 1 : Quiz ─────────────────────────────────────────── -->
    <div id="praximet-etape-quiz">

        <div class="praximet-header">
            <h2 class="praximet-titre">Découvrez votre profil professionnel</h2>
            <p class="praximet-sous-titre">
                Répondez honnêtement à chaque affirmation.<br>
                Il n'y a pas de bonne ou de mauvaise réponse.
            </p>
        </div>

        <!-- Badge PraxiMet -->
        <div class="praximet-badge-wrap">
            <span class="praximet-badge">PraxiMet</span>
        </div>

        <!-- Barre de progression -->
        <div class="praximet-progress-container" role="progressbar"
             aria-valuemin="0" aria-valuemax="100">
            <div class="praximet-progress-track">
                <div id="praximet-progress-bar"
                     class="praximet-progress-fill"
                     style="width: 0%"
                     aria-valuenow="0"></div>
            </div>
            <span id="praximet-progress-label" class="praximet-progress-label">
                0% complété
            </span>
        </div>

        <!-- Numéro de question -->
        <p id="praximet-question-num" class="praximet-question-num">
            1 / <?php echo count( $questions ); ?>
        </p>

        <!-- Texte de la question -->
        <div class="praximet-question-card">
            <p id="praximet-question-texte" class="praximet-question-texte praximet-fade-in">
                Chargement…
            </p>
        </div>

        <!-- Boutons de réponse -->
        <div class="praximet-btns-reponse">
            <button id="praximet-btn-non"
                    class="praximet-btn praximet-btn--non"
                    type="button">
                ✗ Non
            </button>
            <button id="praximet-btn-oui"
                    class="praximet-btn praximet-btn--oui"
                    type="button">
                ✓ Oui
            </button>
        </div>

        <!-- Bouton Précédent -->
        <div class="praximet-nav-prev">
            <button id="praximet-btn-prev"
                    class="praximet-btn praximet-btn--prev"
                    type="button"
                    style="display:none">
                ← Question précédente
            </button>
        </div>

    </div><!-- /etape-quiz -->


    <!-- ── ÉTAPE 2 : Formulaire de contact ───────────────────────── -->
    <div id="praximet-etape-form" class="praximet-hidden">

        <div class="praximet-resultat-apercu">
            <div class="praximet-code-flou">
                <span class="praximet-lettre-floue">?</span>
                <span class="praximet-lettre-floue">?</span>
                <span class="praximet-lettre-floue">?</span>
            </div>
            <p class="praximet-apercu-texte">
                🎉 Votre profil est prêt !<br>
                <strong>Entrez vos coordonnées pour découvrir votre code RIASEC.</strong>
            </p>
        </div>

        <form id="praximet-form"
              class="praximet-form"
              method="post"
              novalidate>

            <?php wp_nonce_field( 'praximet_submit', 'praximet_nonce' ); ?>

            <!-- Champ caché : réponses JSON -->
            <input type="hidden"
                   id="praximet-input-reponses"
                   name="praximet_reponses"
                   value="" />

            <div class="praximet-form-row">
                <div class="praximet-form-group">
                    <label for="praximet-prenom">Prénom *</label>
                    <input type="text"
                           id="praximet-prenom"
                           name="praximet_prenom"
                           placeholder="Votre prénom"
                           required
                           autocomplete="given-name" />
                </div>
                <div class="praximet-form-group">
                    <label for="praximet-nom">Nom *</label>
                    <input type="text"
                           id="praximet-nom"
                           name="praximet_nom"
                           placeholder="Votre nom"
                           required
                           autocomplete="family-name" />
                </div>
            </div>

            <div class="praximet-form-group">
                <label for="praximet-email">Email *</label>
                <input type="email"
                       id="praximet-email"
                       name="praximet_email"
                       placeholder="votre@email.fr"
                       required
                       autocomplete="email" />
            </div>

            <div class="praximet-form-group praximet-form-group--rgpd">
                <label class="praximet-label-checkbox">
                    <input type="checkbox"
                           id="praximet-rgpd"
                           name="praximet_rgpd"
                           required />
                    <span>
                        J'accepte que mes données soient utilisées pour me recontacter
                        dans le cadre de mon projet professionnel.
                        <a href="<?php echo esc_url( get_privacy_policy_url() ); ?>"
                           target="_blank" rel="noopener">
                            Politique de confidentialité
                        </a>
                    </span>
                </label>
            </div>

            <div id="praximet-form-error"
                 class="praximet-error praximet-hidden"
                 role="alert">
            </div>

            <button type="submit"
                    id="praximet-btn-submit"
                    class="praximet-btn praximet-btn--submit">
                Découvrir mon profil RIASEC →
            </button>

        </form>

    </div><!-- /etape-form -->


    <!-- ── ÉTAPE 3 : Résultat ─────────────────────────────────────── -->
    <div id="praximet-etape-resultat" class="praximet-hidden">

        <div class="praximet-resultat-header">
            <p class="praximet-resultat-intro">Votre code de personnalité professionnelle</p>
            <div id="praximet-code-riasec" class="praximet-code-riasec">
                <!-- Rempli dynamiquement par AJAX -->
            </div>
        </div>

        <div id="praximet-profil-detail" class="praximet-profil-detail">
            <!-- Rempli dynamiquement par AJAX -->
        </div>

        <!-- ── Bandeau CTA principal ─────────────────────────────── -->
        <div class="praximet-cta-bandeau">
            <div class="praximet-cta-bandeau__icon">📅</div>
            <div class="praximet-cta-bandeau__content">
                <p class="praximet-cta-bandeau__titre">
                    Passez à l'étape suivante
                </p>
                <p class="praximet-cta-bandeau__texte">
                    Prenez rendez-vous avec un conseiller en bilan de compétences
                    pour travailler votre orientation professionnelle et construire
                    un projet qui vous ressemble vraiment.
                </p>
            </div>
            <a id="praximet-btn-calendly"
               href="#"
               class="praximet-btn praximet-btn--calendly"
               target="_blank"
               rel="noopener">
                Prendre rendez-vous →
            </a>
        </div>

        <!-- Suppression données RGPD -->
        <div class="praximet-rgpd-wrap" id="praximet-rgpd-wrap" style="display:none;">
            <button id="praximet-btn-supprimer" type="button" class="praximet-btn-supprimer">
                🗑 Supprimer mes données
            </button>
            <p class="praximet-rgpd-note">
                Conformément au RGPD, vous pouvez demander la suppression immédiate de vos données personnelles.
            </p>
        </div>

    </div><!-- /etape-resultat -->

</div><!-- /praximet-quiz -->
