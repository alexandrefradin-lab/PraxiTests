<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div id="praxivaleurs-app" class="pv-app">

    <!-- ÉCRAN 1 : INTRO -->
    <div id="pv-screen-intro" class="pv-screen active">
        <div class="pv-intro-hero">
            <div class="pv-intro-badge">Praxis Accompagnement · Bilan de compétences</div>
            <h1 class="pv-intro-title">Quelles valeurs guident vraiment votre vie professionnelle&nbsp;?</h1>
            <p class="pv-intro-desc">Ce test révèle vos <strong>5 valeurs dominantes</strong> — le socle invisible de toutes vos décisions de carrière.</p>
            <div class="pv-intro-meta">
                <span>⏱ 10 minutes</span>
                <span>·</span>
                <span>40 questions + 20 comparaisons</span>
                <span>·</span>
                <span>Résultats immédiats</span>
            </div>
            <p class="pv-intro-proof">Utilisé par les consultants Praxis Accompagnement dans le cadre du bilan de compétences.</p>
            <button id="pv-btn-start" class="pv-btn-primary">Découvrir mes valeurs →</button>
        </div>
    </div>

    <!-- ÉCRAN 2 : QUESTIONS LIKERT -->
    <div id="pv-screen-questions" class="pv-screen">
        <div class="pv-question-wrap">
            <div class="pv-progress-bar-wrap">
                <div class="pv-progress-meta">
                    <span id="pv-section-label" class="pv-section-label">Mes valeurs</span>
                    <span id="pv-progress-pct" class="pv-progress-pct">2%</span>
                </div>
                <div class="pv-progress-track">
                    <div id="pv-progress-fill" class="pv-progress-fill" style="width:2.5%"></div>
                </div>
            </div>
            <div class="pv-nav-row">
                <button id="pv-btn-back" class="pv-btn-back" style="visibility:hidden;">← Retour</button>
                <span id="pv-counter" class="pv-counter-badge">1 / 40</span>
            </div>
            <div class="pv-question-card">
                <div id="pv-question-text" class="pv-question-text"></div>
                <div id="pv-likert-btns" class="pv-likert-btns">
                    <button class="pv-likert-btn" data-value="1">Pas important</button>
                    <button class="pv-likert-btn" data-value="3">Assez important</button>
                    <button class="pv-likert-btn" data-value="5">Important</button>
                    <button class="pv-likert-btn" data-value="6">Essentiel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ÉCRAN 3 : COMPARAISONS FORCÉES -->
    <div id="pv-screen-compare" class="pv-screen">
        <div class="pv-question-wrap">

            <!-- Barre de progression comparaisons -->
            <div class="pv-progress-bar-wrap">
                <div class="pv-progress-meta">
                    <span class="pv-section-label">⚖️ Affinez vos priorités</span>
                    <span id="pv-compare-pct" class="pv-progress-pct">0%</span>
                </div>
                <div class="pv-progress-track">
                    <div id="pv-compare-fill" class="pv-progress-fill" style="width:0%"></div>
                </div>
            </div>

            <!-- Compteur + explication -->
            <div class="pv-nav-row">
                <button id="pv-btn-back-compare" class="pv-btn-back" style="visibility:hidden;">← Retour</button>
                <span id="pv-compare-counter" class="pv-counter-badge">1 / 20</span>
            </div>

            <!-- Intro contextuelle (affichée seulement à la 1ère comparaison) -->
            <div id="pv-compare-intro" class="pv-compare-intro">
                <p>Vous avez évalué toutes les valeurs. Maintenant, <strong>choisissez laquelle compte le plus pour vous</strong> entre ces deux propositions.</p>
            </div>

            <!-- Card de comparaison — deux choix côte à côte -->
            <div class="pv-compare-card">
                <p class="pv-compare-question">Laquelle de ces valeurs vous correspond le mieux&nbsp;?</p>
                <div class="pv-compare-choices">
                    <button class="pv-compare-btn" id="pv-choice-a">
                        <span class="pv-compare-icon" id="pv-icon-a"></span>
                        <span class="pv-compare-label" id="pv-label-a"></span>
                        <span class="pv-compare-desc" id="pv-desc-a"></span>
                        <span class="pv-compare-tooltip-wrap">
                            <span class="pv-compare-info" id="pv-info-a" onclick="event.stopPropagation()">?</span>
                            <span class="pv-compare-tooltip" id="pv-tooltip-a"></span>
                        </span>
                    </button>
                    <div class="pv-compare-vs">VS</div>
                    <button class="pv-compare-btn" id="pv-choice-b">
                        <span class="pv-compare-icon" id="pv-icon-b"></span>
                        <span class="pv-compare-label" id="pv-label-b"></span>
                        <span class="pv-compare-desc" id="pv-desc-b"></span>
                        <span class="pv-compare-tooltip-wrap">
                            <span class="pv-compare-info" id="pv-info-b" onclick="event.stopPropagation()">?</span>
                            <span class="pv-compare-tooltip" id="pv-tooltip-b"></span>
                        </span>
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- ÉCRAN 4 : FORMULAIRE -->
    <div id="pv-screen-form" class="pv-screen">
        <div class="pv-form-wrap">
            <div class="pv-form-icon">🎉</div>
            <h2 class="pv-form-title">Vos résultats sont prêts&nbsp;!</h2>
            <p class="pv-form-desc">Entrez votre prénom et votre email pour recevoir votre profil complet de valeurs — avec les implications concrètes pour votre projet professionnel.</p>
            <div id="pv-form-error" class="pv-form-error" style="display:none;"></div>
            <div class="pv-form-fields">
                <input type="text" id="pv-prenom" placeholder="Votre prénom" class="pv-input" autocomplete="given-name" maxlength="100">
                <input type="email" id="pv-email" placeholder="Votre email professionnel" class="pv-input" autocomplete="email" maxlength="200">
                <button id="pv-btn-submit" class="pv-btn-primary">Recevoir mon profil de valeurs →</button>
            </div>
            <p class="pv-form-rgpd">🔒 Vos données sont confidentielles. Utilisées uniquement dans le cadre de votre bilan.</p>
        </div>
    </div>

    <!-- ÉCRAN 5 : RÉSULTATS -->
    <div id="pv-screen-results" class="pv-screen">
        <div class="pv-results-header">
            <h2 class="pv-results-title">Votre profil de valeurs, <span id="pv-results-prenom" style="color:var(--pv-orange)"></span></h2>
            <p class="pv-results-intro">Voici les 5 valeurs qui structurent le plus profondément votre rapport au travail et à la vie. Elles sont le fil conducteur de vos choix — passés, présents et futurs.</p>
        </div>
        <div class="pv-radar-wrap">
            <canvas id="pv-radar-chart" width="420" height="420"></canvas>
        </div>
        <div id="pv-top5-cards" class="pv-top5-cards"></div>
        <div class="pv-results-footer">
            <p>📩 Un email avec votre profil complet vous a été envoyé. Partagez-le avec votre consultant lors de votre prochain rendez-vous.</p>
        </div>
    </div>

    <!-- LOADER -->
    <div id="pv-loader" class="pv-loader" style="display:none;">
        <div class="pv-loader-spinner"></div>
        <p>Calcul de votre profil en cours…</p>
    </div>

</div>
