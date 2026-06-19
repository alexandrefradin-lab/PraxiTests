<?php
/**
 * Squelette de l'application de passation (rempli par main.js).
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="p360-app">
    <div id="p360-screen" style="display:none;">
        <div class="p360-progress">
            <div class="p360-progress-head">
                <span id="p360-section">&nbsp;</span>
                <span class="p360-progress-counter" id="p360-counter">&nbsp;</span>
            </div>
            <div class="p360-progress-bar"><div class="p360-progress-fill" id="p360-fill"></div></div>
        </div>
        <div class="p360-card">
            <button class="p360-btn-ghost p360-back" id="p360-back" disabled>&larr; Retour</button>
            <div id="p360-question-area"></div>
        </div>
    </div>
</div>
