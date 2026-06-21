<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<div id="praxisens-app" class="praxisens">

    <!-- Barre de progression fixe -->
    <div class="px-progress" id="px-progress" hidden>
        <div class="px-progress-head">
            <span id="px-section">Hypersensibilité</span>
            <span id="px-counter">1 / 18</span>
        </div>
        <div class="px-progress-track"><div class="px-progress-bar" id="px-bar"></div></div>
    </div>

    <!-- Écran d'introduction -->
    <section class="px-screen px-intro" id="px-intro">
        <span class="px-badge">Test validé · 18 questions · ~5 min</span>
        <h1>Êtes-vous une personne hypersensible&nbsp;?</h1>
        <p class="px-lead">
            Ce test évalue votre <strong>sensibilité de traitement sensoriel</strong> (Sensory Processing Sensitivity)
            selon le modèle scientifique d'Elaine Aron : votre rapport à la sur-stimulation, votre seuil sensoriel
            et votre profondeur de perception.
        </p>
        <ul class="px-points">
            <li>3 dimensions analysées en profondeur</li>
            <li>Résultat immédiat + envoi par e-mail</li>
            <li>Répondez spontanément, il n'y a pas de bonne réponse</li>
        </ul>
        <button class="px-btn px-btn-primary" id="px-start">Commencer le test</button>
    </section>

    <!-- Écran des questions (rempli par JS) -->
    <section class="px-screen" id="px-questions" hidden></section>

    <!-- Écran de collecte e-mail -->
    <section class="px-screen px-form" id="px-collect" hidden>
        <h2>Presque terminé&nbsp;!</h2>
        <p class="px-lead">Indiquez votre prénom et votre e-mail pour découvrir votre profil et le recevoir par e-mail.</p>
        <div class="px-field">
            <label for="px-firstname">Prénom</label>
            <input type="text" id="px-firstname" autocomplete="given-name" placeholder="Votre prénom">
        </div>
        <div class="px-field">
            <label for="px-email">E-mail</label>
            <input type="email" id="px-email" autocomplete="email" placeholder="vous@exemple.fr" required>
        </div>
        <p class="px-error" id="px-error" hidden></p>
        <button class="px-btn px-btn-primary" id="px-submit">Voir mon résultat</button>
        <p class="px-consent">En validant, vous acceptez de recevoir votre résultat par e-mail.</p>
    </section>
</div>
