/**
 * PraxiMet – JS Admin
 * - Mise à jour statut en AJAX depuis la liste des leads
 * - Copie du shortcode en un clic
 */

(function () {
    'use strict';

    // ── Mise à jour statut inline (liste leads) ───────────────────────
    document.querySelectorAll('.praximet-statut-select').forEach(function (select) {
        select.addEventListener('change', function () {
            const leadId = this.dataset.leadId;
            const statut = this.value;
            const nonce  = this.dataset.nonce;
            const row    = this.closest('tr');

            // Feedback visuel immédiat
            select.disabled  = true;
            row.style.opacity = '0.6';

            const body = new URLSearchParams({
                action:  'praximet_update_statut_ajax',
                lead_id: leadId,
                statut:  statut,
                nonce:   nonce,
            });

            fetch( praximet_admin.ajax_url, {
                method:      'POST',
                credentials: 'same-origin',
                headers:     { 'Content-Type': 'application/x-www-form-urlencoded' },
                body:        body.toString(),
            })
            .then( r => r.json() )
            .then( data => {
                if ( data.success ) {
                    // Flash vert de confirmation
                    row.style.background = '#d1fae5';
                    setTimeout( () => {
                        row.style.background = '';
                        row.style.opacity    = '1';
                    }, 800 );

                    // Masque la ligne si filtre statut actif et statut différent
                    const params       = new URLSearchParams( window.location.search );
                    const filtreStatut = params.get('statut');
                    if ( filtreStatut && filtreStatut !== statut ) {
                        setTimeout( () => {
                            row.style.transition = 'opacity .4s';
                            row.style.opacity    = '0';
                            setTimeout( () => row.remove(), 400 );
                        }, 900 );
                    }
                } else {
                    alert( data.data?.message || 'Erreur lors de la mise à jour.' );
                    location.reload();
                }
            })
            .catch( () => {
                alert( 'Erreur réseau. Veuillez recharger la page.' );
                location.reload();
            })
            .finally( () => {
                select.disabled  = false;
                row.style.opacity = '1';
            });
        });
    });

    // ── Copie shortcode ───────────────────────────────────────────────
    const copyBtn = document.querySelector('.praximet-copy-btn');
    if ( copyBtn ) {
        copyBtn.addEventListener('click', function () {
            const text = this.dataset.copy;
            if ( navigator.clipboard ) {
                navigator.clipboard.writeText( text ).then( () => {
                    copyBtn.textContent = '✓ Copié !';
                    setTimeout( () => copyBtn.textContent = 'Copier', 2000 );
                });
            } else {
                // Fallback ancienne méthode
                const el       = document.createElement('textarea');
                el.value       = text;
                el.style.position = 'absolute';
                el.style.left     = '-9999px';
                document.body.appendChild(el);
                el.select();
                document.execCommand('copy');
                document.body.removeChild(el);
                copyBtn.textContent = '✓ Copié !';
                setTimeout( () => copyBtn.textContent = 'Copier', 2000 );
            }
        });
    }

})();


// ── Suppression de lead ───────────────────────────────────────────────
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.praximet-btn-delete');
    if ( ! btn ) return;

    if ( ! confirm('Supprimer définitivement ce lead ? Cette action est irréversible.') ) return;

    var id    = btn.dataset.id;
    var nonce = btn.dataset.nonce;
    var row   = btn.closest('tr');

    btn.disabled = true;

    var body = new URLSearchParams({
        action:   'praximet_supprimer_lead',
        lead_id:  id,
        nonce:    nonce,
    });

    fetch( praximet_admin.ajax_url, {
        method:      'POST',
        credentials: 'same-origin',
        headers:     { 'Content-Type': 'application/x-www-form-urlencoded' },
        body:        body.toString(),
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if ( data.success ) {
            if ( row ) {
                row.style.transition = 'opacity .3s';
                row.style.opacity    = '0';
                setTimeout(function() { row.remove(); }, 300);
            } else {
                window.location.href = praximet_admin.ajax_url.replace('admin-ajax.php', 'admin.php?page=praximet-leads&deleted=1');
            }
        } else {
            alert('Erreur lors de la suppression.');
            btn.disabled = false;
        }
    })
    .catch(function() {
        alert('Impossible de contacter le serveur.');
        btn.disabled = false;
    });
});
