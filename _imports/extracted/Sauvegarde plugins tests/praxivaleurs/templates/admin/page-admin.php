<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<style>
/* Reset WordPress admin interference */
#pva-app, #pva-app * { box-sizing: border-box; }
#pva-app .pva-btn { cursor: pointer; }
</style>

<div id="pva-app">

    <!-- HEADER -->
    <div class="pva-header">
        <div class="pva-header-brand">
            <div class="pva-header-logo">
                <span class="pva-header-logo-dot"></span>
                <div>
                    <div class="pva-header-name">PraxiValeurs</div>
                    <div class="pva-header-sub">Console d'administration</div>
                </div>
            </div>
        </div>
        <div class="pva-header-actions">
            <span class="pva-total-pill" id="pva-total-badge">— participants</span>
            <a href="<?php echo esc_url( admin_url('admin-ajax.php?action=pv_admin_export_csv&nonce=' . wp_create_nonce('pv_admin_nonce') ) ); ?>"
               class="pva-btn pva-btn-export">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Exporter CSV
            </a>
        </div>
    </div>

    <!-- ONGLETS -->
    <div class="pva-tabs-wrap">
        <nav class="pva-tabs">
            <button class="pva-tab active" data-tab="dashboard">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                Tableau de bord
            </button>
            <button class="pva-tab" data-tab="participants">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Participants
            </button>
            <button class="pva-tab" data-tab="settings">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                Réglages
            </button>
        </nav>
    </div>

    <!-- ═══ DASHBOARD ═══ -->
    <div id="pva-tab-dashboard" class="pva-panel active">

        <div class="pva-kpi-row">
            <div class="pva-kpi">
                <div class="pva-kpi-icon pva-kpi-icon--blue">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                </div>
                <div class="pva-kpi-body">
                    <div class="pva-kpi-val" id="kpi-total">—</div>
                    <div class="pva-kpi-lbl">Total participants</div>
                </div>
            </div>
            <div class="pva-kpi">
                <div class="pva-kpi-icon pva-kpi-icon--orange">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <div class="pva-kpi-body">
                    <div class="pva-kpi-val" id="kpi-month">—</div>
                    <div class="pva-kpi-lbl">Ce mois-ci</div>
                </div>
            </div>
            <div class="pva-kpi">
                <div class="pva-kpi-icon pva-kpi-icon--green">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                </div>
                <div class="pva-kpi-body">
                    <div class="pva-kpi-val" id="kpi-week">—</div>
                    <div class="pva-kpi-lbl">Cette semaine</div>
                </div>
            </div>
            <div class="pva-kpi pva-kpi--accent">
                <div class="pva-kpi-icon pva-kpi-icon--white">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                </div>
                <div class="pva-kpi-body">
                    <div class="pva-kpi-val pva-kpi-val--white" id="kpi-top-dim">—</div>
                    <div class="pva-kpi-lbl pva-kpi-lbl--white">Valeur dominante</div>
                </div>
            </div>
        </div>

        <div class="pva-charts-row">
            <div class="pva-card pva-card--chart pva-card--wide">
                <div class="pva-card-head">
                    <span class="pva-card-title">Participations — 30 derniers jours</span>
                </div>
                <div class="pva-chart-area"><canvas id="chart-evolution"></canvas></div>
            </div>
            <div class="pva-card pva-card--chart">
                <div class="pva-card-head">
                    <span class="pva-card-title">Valeurs dans le Top 5 (% des participants)</span>
                </div>
                <div class="pva-chart-area"><canvas id="chart-freq"></canvas></div>
            </div>
        </div>
    </div>

    <!-- ═══ PARTICIPANTS ═══ -->
    <div id="pva-tab-participants" class="pva-panel">
        <div class="pva-toolbar">
            <div class="pva-search-box">
                <svg class="pva-search-ico" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="pva-search" class="pva-search-input" placeholder="Rechercher par prénom ou email…">
            </div>
            <span id="pva-result-count" class="pva-result-count"></span>
        </div>

        <div class="pva-card">
            <table class="pva-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Top 5 valeurs</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="pva-table-body">
                    <tr><td colspan="6" class="pva-table-empty">Chargement…</td></tr>
                </tbody>
            </table>
        </div>

        <div class="pva-pagination" id="pva-pagination"></div>
    </div>

    <!-- ═══ RÉGLAGES ═══ -->
    <div id="pva-tab-settings" class="pva-panel">
        <div class="pva-settings-grid">

            <div class="pva-card pva-settings-card">
                <div class="pva-card-head">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#E8491D" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    <span class="pva-card-title">Notifications</span>
                </div>
                <div class="pva-field">
                    <label class="pva-label">Email du consultant</label>
                    <input type="email" id="set-email" class="pva-input" placeholder="consultant@praxis.fr">
                    <p class="pva-hint">Reçoit une notification à chaque nouveau test complété.</p>
                </div>
            </div>

            <div class="pva-card pva-settings-card">
                <div class="pva-card-head">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#E8491D" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    <span class="pva-card-title">Configuration SMTP (OVH)</span>
                </div>
                <div class="pva-field">
                    <label class="pva-label">Hôte SMTP</label>
                    <input type="text" id="set-smtp-host" class="pva-input" placeholder="ssl0.ovh.net">
                </div>
                <div class="pva-field">
                    <label class="pva-label">Utilisateur</label>
                    <input type="text" id="set-smtp-user" class="pva-input" placeholder="contact@votredomaine.fr">
                </div>
                <div class="pva-field">
                    <label class="pva-label">Mot de passe</label>
                    <input type="password" id="set-smtp-pass" class="pva-input" placeholder="••••••••">
                </div>
                <p class="pva-hint">Port OVH : 465 (SSL). Identifiants stockés dans wp_options.</p>
            </div>

            <div class="pva-card pva-settings-card">
                <div class="pva-card-head">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#E8491D" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                    <span class="pva-card-title">Intégration shortcode</span>
                </div>
                <div class="pva-shortcode-row">
                    <code class="pva-shortcode-code">[praxivaleurs]</code>
                    <button class="pva-btn pva-btn-sm" onclick="navigator.clipboard.writeText('[praxivaleurs]');this.textContent='Copié ✓';setTimeout(()=>this.textContent='Copier',2000)">Copier</button>
                </div>
                <p class="pva-hint">Collez ce shortcode dans n'importe quelle page WordPress pour afficher le test.</p>
            </div>

        </div>

        <div id="pva-settings-feedback" class="pva-feedback" style="display:none;"></div>
        <button class="pva-btn pva-btn-primary" id="pva-save-settings">Enregistrer les réglages</button>
    </div>

    <!-- MODALE -->
    <div id="pva-modal" class="pva-modal" style="display:none;">
        <div class="pva-modal-overlay"></div>
        <div class="pva-modal-box">
            <div class="pva-modal-hd">
                <h3 id="pva-modal-title" class="pva-modal-title">Profil de valeurs</h3>
                <button id="pva-modal-close" class="pva-modal-close">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="pva-modal-bd">
                <div class="pva-modal-radar-wrap">
                    <canvas id="pva-modal-radar"></canvas>
                </div>
                <div id="pva-modal-top5" class="pva-modal-list"></div>
                <div class="pva-modal-footer">
                    <button id="pva-modal-resend" class="pva-btn pva-btn-primary pva-btn-resend">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        Renvoyer par email
                    </button>
                    <button id="pva-modal-pdf" class="pva-btn pva-btn-pdf">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Télécharger PDF
                    </button>
                    <span id="pva-resend-feedback" class="pva-resend-feedback"></span>
                </div>
            </div>
        </div>
    </div>

</div>
