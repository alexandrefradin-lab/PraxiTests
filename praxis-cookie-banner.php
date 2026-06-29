<?php
/**
 * Plugin Name: Praxis Cookie Banner
 * Description: Bannière de consentement cookies avec lien vers PraxiQuest.fr
 * Version: 1.0
 * Author: Praxis Accompagnement
 */

if (!defined('ABSPATH')) exit;

add_action('wp_footer', function () {
    if (isset($_COOKIE['praxis_cookie_consent'])) return;
    ?>
    <div id="praxis-cookie-banner">
        <div id="praxis-cookie-inner">
            <div id="praxis-cookie-text">
                <strong>🍪 Nous utilisons des cookies</strong>
                <p>Ce site utilise des cookies pour améliorer votre expérience et analyser le trafic. Vous pouvez accepter ou refuser leur utilisation.</p>
            </div>
            <div id="praxis-cookie-actions">
                <button id="praxis-cookie-refuse" onclick="praxisCookieChoice('refuse')">Refuser</button>
                <button id="praxis-cookie-accept" onclick="praxisCookieChoice('accept')">Accepter</button>
            </div>
        </div>
        <div id="praxis-cookie-discover" style="display:none;">
            <div id="praxis-cookie-inner">
                <div id="praxis-cookie-text">
                    <strong>✅ Préférences enregistrées</strong>
                    <p>Découvrez <strong>PraxiQuest</strong> — Partez à la découverte de vous-même : vos forces, vos valeurs, votre chemin.</p>
                </div>
                <div id="praxis-cookie-actions">
                    <a href="https://www.praxiquest.fr" target="_blank" rel="noopener noreferrer" id="praxis-cookie-cta">Découvrir PraxiQuest →</a>
                    <button onclick="document.getElementById('praxis-cookie-banner').remove()" id="praxis-cookie-close">✕ Fermer</button>
                </div>
        </div>
    </div>

    <style>
    /* Palette Praxis Accompagnement : navy #002345 / blanc #fff */
    #praxis-cookie-banner {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 99999;
        background: #002345;
        color: #fff;
        box-shadow: 0 -4px 24px rgba(0, 35, 69, 0.4);
        font-family: 'Avenir', 'Segoe UI', sans-serif;
        animation: slideUpCookie 0.4s ease;
    }
    @keyframes slideUpCookie {
        from { transform: translateY(100%); opacity: 0; }
        to   { transform: translateY(0);   opacity: 1; }
    }
    #praxis-cookie-inner {
        max-width: 1100px;
        margin: 0 auto;
        padding: 16px 24px;
        display: flex;
        align-items: center;
        gap: 24px;
        flex-wrap: wrap;
    }
    #praxis-cookie-text {
        flex: 1;
        min-width: 240px;
    }
    #praxis-cookie-text strong {
        font-size: 15px;
        display: block;
        margin-bottom: 4px;
        color: #fff;
    }
    #praxis-cookie-text p {
        font-size: 13px;
        color: rgba(255,255,255,0.75);
        margin: 0;
        line-height: 1.5;
    }
    #praxis-cookie-actions {
        display: flex;
        gap: 10px;
        flex-shrink: 0;
    }
    #praxis-cookie-actions button {
        padding: 10px 22px;
        border: none;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: opacity .2s, background .2s;
        font-family: inherit;
    }
    #praxis-cookie-actions button:hover { opacity: 0.85; }
    #praxis-cookie-refuse {
        background: transparent;
        border: 2px solid rgba(255,255,255,0.5) !important;
        color: rgba(255,255,255,0.85);
    }
    #praxis-cookie-refuse:hover {
        border-color: #fff !important;
        color: #fff;
    }
    #praxis-cookie-accept {
        background: #fff;
        color: #002345;
    }
    #praxis-cookie-accept:hover {
        background: rgba(255,255,255,0.9);
    }
    #praxis-cookie-cta {
        display: inline-block;
        padding: 10px 22px;
        background: #fff;
        color: #002345;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        transition: opacity .2s;
        font-family: inherit;
    }
    #praxis-cookie-cta:hover { opacity: 0.88; color: #002345; }
    #praxis-cookie-close {
        padding: 10px 16px;
        background: transparent;
        border: 2px solid rgba(255,255,255,0.5) !important;
        color: rgba(255,255,255,0.75);
        border-radius: 4px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: opacity .2s;
        font-family: inherit;
    }
    #praxis-cookie-close:hover { opacity: 0.85; }
    </style>

    <script>
    function praxisCookieChoice(choice) {
        // 1. Sauvegarder le choix (cookie 365 jours)
        var expires = new Date();
        expires.setFullYear(expires.getFullYear() + 1);
        document.cookie = 'praxis_cookie_consent=' + choice
            + '; expires=' + expires.toUTCString()
            + '; path=/; SameSite=Lax';

        // 2. Basculer vers le message de découverte
        document.getElementById('praxis-cookie-discover').style.display = 'block';
        document.querySelector('#praxis-cookie-banner > div:first-child').style.display = 'none';
    }
    </script>
    <?php
});
