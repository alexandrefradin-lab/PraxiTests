/**
 * PraxiMet – Soumission AJAX + affichage résultat + radar chart RIASEC
 * v1.0.3 — sélecteurs résolus à l'utilisation (pas au chargement)
 */

(function () {
    'use strict';

    // ── Init au chargement du DOM ─────────────────────────────────────
    function init() {
        var form = document.getElementById('praximet-form');
        if ( ! form ) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            cacherErreur();
            soumettre( form );
        });
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // ── Soumission formulaire ─────────────────────────────────────────
    function soumettre( form ) {
        var btnSubmit = document.getElementById('praximet-btn-submit');
        btnSubmit.disabled    = true;
        btnSubmit.textContent = 'Calcul en cours\u2026';

        var formData = new FormData( form );
        formData.append( 'action',          'praximet_submit' );
        formData.append( 'praximet_source', window.location.href );

        var reponsesCachees = formData.get('praximet_reponses');
        if ( ( ! reponsesCachees || reponsesCachees === '' ) && window.praximet_reponses ) {
            formData.set( 'praximet_reponses', window.praximet_reponses );
        }

        fetch( praximet_ajax.url, {
            method:      'POST',
            credentials: 'same-origin',
            body:        formData,
        })
        .then( function (response) {
            if ( ! response.ok ) throw new Error( 'Erreur réseau (' + response.status + ')' );
            return response.json();
        })
        .then( function (data) {
            if ( data.success ) {
                afficherResultat( data.data );
            } else {
                var msg = (data.data && data.data.message) ? data.data.message : 'Une erreur est survenue.';
                afficherErreur( msg );
                reinitialiserBouton();
            }
        })
        .catch( function (err) {
            console.error( 'PraxiMet AJAX error:', err );
            afficherErreur( 'Impossible de contacter le serveur. V\u00e9rifiez votre connexion et r\u00e9essayez.' );
            reinitialiserBouton();
        });
    }

    // ── Affichage du résultat ─────────────────────────────────────────
    function afficherResultat( data ) {

        // Sélecteurs résolus ICI — au moment de l'utilisation
        var elCode     = document.getElementById('praximet-code-riasec');
        var elProfil   = document.getElementById('praximet-profil-detail');
        var elCalendly = document.getElementById('praximet-btn-calendly');
        var etapeForm  = document.getElementById('praximet-etape-form');
        var etapeRes   = document.getElementById('praximet-etape-resultat');

        var lead_id      = data.lead_id || 0;
        var code         = data.code;
        var profil       = data.profil;
        var prenom       = data.prenom;
        var calendly_url = data.calendly_url;
        // Debug complet de la réponse serveur
        if (data.scores) {
            Object.keys(data.scores).forEach(function(k) {
                    });
        }

        // Normalise les clés en majuscules
        var scoresRaw = data.scores || {};
        var scores = {};
        ['R','I','A','S','E','C'].forEach(function(l) {
            scores[l] = parseInt(scoresRaw[l] || scoresRaw[l.toLowerCase()] || 0, 10);
        });


        // Code RIASEC animé
        if ( elCode ) {
            elCode.innerHTML = '';
            code.split('').forEach( function (lettre, i) {
                var span              = document.createElement('span');
                span.className        = 'praximet-lettre-code';
                span.textContent      = lettre;
                span.style.animationDelay = ( i * 0.15 ) + 's';
                elCode.appendChild( span );
            });
        }

        if ( elProfil ) {
            elProfil.innerHTML = '';

            // Intro
            var intro         = document.createElement('p');
            intro.className   = 'praximet-resultat-prenom';
            intro.textContent = prenom
                ? '\ud83c\udfaf ' + prenom + ', voici votre profil professionnel :'
                : '\ud83c\udfaf Voici votre profil professionnel :';
            elProfil.appendChild( intro );

            // Radar chart
            if ( scores ) {
                var radarEl = construireRadar( scores, code );
                        elProfil.appendChild( radarEl );
            }

            // Cartes profil
            profil.forEach( function (type, index) {
                var card       = document.createElement('div');
                card.className = 'praximet-type-card';
                var rang       = ['Dominant', 'Secondaire', 'Tertiaire'][ index ] || '';
                card.innerHTML =
                    '<div class="praximet-type-rang">' + rang + '</div>' +
                    '<h3 class="praximet-type-label">' + escapeHtml( type.label ) + '</h3>' +
                    '<p class="praximet-type-desc">'   + escapeHtml( type.description ) + '</p>';
                elProfil.appendChild( card );
            });

            // Sous-domaines
            if ( data.scores_sd && Object.keys(data.scores_sd).length ) {
                elProfil.appendChild( construireSousDomaines( data.scores_sd, code ) );
            }
        }

        // Bouton Calendly
        if ( elCalendly ) {
            if ( calendly_url ) {
                elCalendly.href          = calendly_url;
                elCalendly.style.display = 'inline-flex';
            } else {
                elCalendly.style.display = 'none';
            }
        }

        // Bloc RGPD suppression
        var rgpdWrap = document.getElementById('praximet-rgpd-wrap');
        if ( rgpdWrap && lead_id ) {
            rgpdWrap.style.display = 'block';
            var btnSupprimer = document.getElementById('praximet-btn-supprimer');
            if ( btnSupprimer ) {
                btnSupprimer.onclick = function() { supprimerProfil( lead_id ); };
            }
        }

        // Bouton PDF
        var btnPdf = document.getElementById('praximet-btn-pdf');
        if ( btnPdf && lead_id ) {
            btnPdf.style.display = 'inline-flex';
            btnPdf.onclick = function() { telechargerPDF( lead_id ); };
        }

        // Transition
        // Signal de succès → quiz.js efface la sauvegarde localStorage
        var form = document.getElementById('praximet-form');
        if (form) form.dispatchEvent(new Event('praximet:succes'));

        if ( etapeForm ) etapeForm.classList.add('praximet-hidden');
        if ( etapeRes  ) {
            etapeRes.classList.remove('praximet-hidden');
            etapeRes.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    // ── Radar Chart SVG ──────────────────────────────────────────────
    function construireRadar( scores, code ) {

        var axes   = ['R','I','A','S','E','C'];
        var labels = { R:'Réaliste', I:'Investigateur', A:'Artistique', S:'Social', E:'Entrepreneur', C:'Conventionnel' };
        var couleurs = { R:'#2d6a9f', I:'#1e8a6e', A:'#8b5cf6', S:'#e05c2a', E:'#d4a017', C:'#4a7c59' };
        var max = 14;
        var n   = 6;
        var NS  = 'http://www.w3.org/2000/svg';

        // Coordonnées en % du viewBox 100×100
        var CX = 50, CY = 50, R = 35;

        function pt( idx, val, rayon ) {
            var angle = ( Math.PI * 2 * idx / n ) - Math.PI / 2;
            var dist  = ( val / max ) * rayon;
            return { x: CX + dist * Math.cos(angle), y: CY + dist * Math.sin(angle) };
        }

        function svgEl( tag, attrs ) {
            var e = document.createElementNS( NS, tag );
            Object.keys(attrs).forEach(function(k){ e.setAttribute(k, attrs[k]); });
            return e;
        }

        function polyPts( niv, r ) {
            return axes.map(function(_,i){ var p=pt(i,niv,r); return p.x+','+p.y; }).join(' ');
        }

        var svg = svgEl('svg', {
            viewBox: '0 0 100 100',
            xmlns: NS,
            style: 'width:100%;height:auto;display:block;',
            role: 'img', 'aria-label': 'Radar RIASEC'
        });

        // Fond
        svg.appendChild( svgEl('circle',{cx:CX,cy:CY,r:R+5,fill:'#eef3fa'}) );

        // Grilles
        [7,14,21,28,35].forEach(function(niv) {
            var ratio = niv/35;
            svg.appendChild( svgEl('polygon',{
                points: polyPts(niv/35*max, R),
                fill:'none',
                stroke: niv===35 ? '#2d5a8e' : '#c8d8ec',
                'stroke-width': niv===35 ? '0.4' : '0.2',
                opacity:'0.8'
            }));
        });

        // Axes
        axes.forEach(function(_,i){
            var p = pt(i,max,R);
            svg.appendChild( svgEl('line',{x1:CX,y1:CY,x2:p.x,y2:p.y,stroke:'#c8d8ec','stroke-width':'0.2',opacity:'0.8'}) );
        });

        // Zone score
        var sPts = axes.map(function(l,i){
            var p = pt(i, scores[l]||0, R);
            return p.x+','+p.y;
        }).join(' ');
        svg.appendChild( svgEl('polygon',{
            points: sPts,
            fill: '#1e3a5f', 'fill-opacity':'0.2',
            stroke:'#1e3a5f', 'stroke-width':'0.5', 'stroke-linejoin':'round'
        }));

        // Points + labels
        axes.forEach(function(lettre,i){
            var val      = scores[lettre] || 0;
            var p        = pt(i, val, R);
            var dominant = code.indexOf(lettre) !== -1;
            var couleur  = couleurs[lettre];

            // Point
            svg.appendChild( svgEl('circle',{
                cx:p.x, cy:p.y,
                r: dominant ? '1.4' : '0.9',
                fill: dominant ? couleur : '#fff',
                stroke: couleur, 'stroke-width':'0.4'
            }));

            // Label position (plus loin)
            var pL   = pt(i, max, R+8);
            var pLib = pt(i, max, R+14);

            var tL = svgEl('text',{
                x:pL.x, y:pL.y,
                'text-anchor':'middle', 'dominant-baseline':'central',
                'font-size': dominant ? '4.5' : '3.8',
                'font-weight': dominant ? 'bold' : 'normal',
                fill: dominant ? couleur : '#64748b'
            });
            tL.textContent = lettre;
            svg.appendChild(tL);

            var tLib = svgEl('text',{
                x:pLib.x, y:pLib.y,
                'text-anchor':'middle', 'dominant-baseline':'central',
                'font-size':'2.5',
                fill: dominant ? couleur : '#94a3b8'
            });
            tLib.textContent = labels[lettre];
            svg.appendChild(tLib);
        });

        var wrap       = document.createElement('div');
        wrap.className = 'praximet-radar-wrap';
        wrap.style.width = '100%';

        var titre        = document.createElement('p');
        titre.className  = 'praximet-radar-titre';
        titre.textContent = 'Votre carte de personnalité';
        wrap.appendChild(titre);
        wrap.appendChild(svg);
        return wrap;
    }


    // ── Helpers ───────────────────────────────────────────────────────
    function afficherErreur(msg) {
        var errorBox = document.getElementById('praximet-form-error');
        if (!errorBox) return;
        errorBox.textContent = msg;
        errorBox.classList.remove('praximet-hidden');
        errorBox.scrollIntoView({behavior:'smooth',block:'nearest'});
    }

    function cacherErreur() {
        var errorBox = document.getElementById('praximet-form-error');
        if (!errorBox) return;
        errorBox.classList.add('praximet-hidden');
        errorBox.textContent = '';
    }

    function reinitialiserBouton() {
        var btnSubmit = document.getElementById('praximet-btn-submit');
        if (!btnSubmit) return;
        btnSubmit.disabled    = false;
        btnSubmit.textContent = 'D\u00e9couvrir mon profil RIASEC \u2192';
    }

    // ── Export PDF ────────────────────────────────────────────────────
    function telechargerPDF( lead_id ) {
        var btnPdf    = document.getElementById('praximet-btn-pdf');
        var loading   = document.getElementById('praximet-pdf-loading');

        btnPdf.style.display   = 'none';
        loading.style.display  = 'block';

        var body = new URLSearchParams({
            action:          'praximet_export_pdf',
            praximet_nonce:  praximet_ajax.nonce,
            lead_id:         lead_id,
        });

        // Sécurité : vérifier que praximet_ajax est disponible
        if ( typeof praximet_ajax === 'undefined' || ! praximet_ajax.url ) {
            afficherErreur( 'Erreur de configuration. Veuillez recharger la page.' );
            reinitialiserBouton();
            return;
        }

        fetch( praximet_ajax.url, {
            method:      'POST',
            credentials: 'same-origin',
            headers:     { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:        body.toString(),
        })
        .then( function(r) { return r.json(); })
        .then( function(data) {
            loading.style.display = 'none';
            btnPdf.style.display  = 'inline-flex';

            if ( ! data.success ) {
                alert('Erreur lors de la génération du rapport.');
                return;
            }

            // Ouvre le rapport dans un nouvel onglet (impression auto déclenchée)
            var html = atob( data.data.html );
            var blob = new Blob([html], { type: 'text/html; charset=utf-8' });
            var url  = URL.createObjectURL( blob );
            var win  = window.open( url, '_blank' );
            if ( ! win ) {
                // Popup bloqué — fallback lien de téléchargement
                var a    = document.createElement('a');
                a.href   = url;
                a.target = '_blank';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            }
        })
        .catch( function() {
            loading.style.display = 'none';
            btnPdf.style.display  = 'inline-flex';
            alert('Impossible de générer le rapport. Veuillez réessayer.');
        });
    }

    // ── Suppression profil (RGPD) ────────────────────────────────────────
    function supprimerProfil( lead_id ) {
        if ( ! confirm('Supprimer définitivement vos données ? Cette action est irréversible.') ) return;

        var quiz     = document.getElementById('praximet-quiz');
        var ajaxUrl  = quiz ? quiz.dataset.ajaxUrl  : '';
        var ajaxNonce = quiz ? quiz.dataset.nonce   : '';

        // Fallback sur praximet_ajax si disponible
        if ( ! ajaxUrl && typeof praximet_ajax !== 'undefined' ) {
            ajaxUrl   = praximet_ajax.url;
            ajaxNonce = praximet_ajax.nonce;
        }

        var btnSupprimer = document.getElementById('praximet-btn-supprimer');
        if ( btnSupprimer ) {
            btnSupprimer.disabled    = true;
            btnSupprimer.textContent = 'Suppression en cours…';
        }

        var body = new URLSearchParams({
            action:         'praximet_supprimer_profil',
            praximet_nonce: ajaxNonce,
            lead_id:        lead_id,
        });

        fetch( ajaxUrl, {
            method:      'POST',
            credentials: 'same-origin',
            headers:     { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:        body.toString(),
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var wrap = document.getElementById('praximet-rgpd-wrap');
            if ( data.success ) {
                if ( wrap ) wrap.innerHTML = '<p class="praximet-rgpd-confirm">✓ Vos données ont été supprimées avec succès.</p>';
            } else {
                if ( btnSupprimer ) {
                    btnSupprimer.disabled    = false;
                    btnSupprimer.textContent = '🗑 Supprimer mes données';
                }
                alert( (data.data && data.data.message) ? data.data.message : 'Erreur lors de la suppression.' );
            }
        })
        .catch(function() {
            if ( btnSupprimer ) {
                btnSupprimer.disabled    = false;
                btnSupprimer.textContent = '🗑 Supprimer mes données';
            }
            alert('Impossible de contacter le serveur.');
        });
    }

    // ── Sous-domaines ─────────────────────────────────────────────────
    function construireSousDomaines( scores_sd, code ) {

        var typeLabels = { R:'Réaliste', I:'Investigateur', A:'Artistique', S:'Social', E:'Entrepreneur', C:'Conventionnel' };
        var typeOrder  = ['R','I','A','S','E','C'];

        // Regroupe les sous-domaines par type
        var parType = {};
        typeOrder.forEach(function(t) { parType[t] = []; });
        Object.keys(scores_sd).forEach(function(sd) {
            var info = scores_sd[sd];
            if (parType[info.type]) parType[info.type].push({ nom: sd, score: info.score, total: info.total, description: info.description || '' });
        });

        var wrap       = document.createElement('div');
        wrap.className = 'praximet-sd-wrap';

        var titre        = document.createElement('p');
        titre.className  = 'praximet-sd-titre';
        titre.textContent = 'Détail par sous-domaine';
        wrap.appendChild(titre);

        typeOrder.forEach(function(type) {
            var sds = parType[type];
            if (!sds || !sds.length) return;

            var isDominant = code.indexOf(type) !== -1;

            var bloc       = document.createElement('div');
            bloc.className = 'praximet-sd-bloc' + (isDominant ? ' praximet-sd-bloc--dominant' : '');

            var head       = document.createElement('div');
            head.className = 'praximet-sd-head';
            head.innerHTML =
                '<span class="praximet-sd-lettre">' + type + '</span>' +
                '<span class="praximet-sd-type-label">' + escapeHtml(typeLabels[type]) + '</span>';
            bloc.appendChild(head);

            sds.forEach(function(sd) {
                var pct  = Math.round((sd.score / sd.total) * 100);
                var row  = document.createElement('div');
                row.className = 'praximet-sd-row';
                var desc = sd.description ? '<p class="praximet-sd-desc">' + escapeHtml(sd.description) + '</p>' : '';
                row.innerHTML =
                    '<div class="praximet-sd-row-top">' +
                        '<span class="praximet-sd-nom">' + escapeHtml(sd.nom) + '</span>' +
                        '<span class="praximet-sd-pct">' + Math.round(sd.score) + '/' + Math.round(sd.total) + '</span>' +
                    '</div>' +
                    desc +
                    '<div class="praximet-sd-bar-track">' +
                        '<div class="praximet-sd-bar-fill" style="width:' + pct + '%"></div>' +
                    '</div>';
                bloc.appendChild(row);
            });

            wrap.appendChild(bloc);
        });

        return wrap;
    }

    function escapeHtml(str) {
        var d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

})();
