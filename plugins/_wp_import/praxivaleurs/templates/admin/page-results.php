<?php if ( ! defined('ABSPATH') ) exit; ?>
<div class="pv-admin-wrap">

    <div class="pv-admin-header">
        <div class="pv-admin-header-left">
            <div class="pv-admin-logo">
                <span class="pv-admin-logo-icon">◆</span>
                <div>
                    <div class="pv-admin-logo-name">PraxiValeurs</div>
                    <div class="pv-admin-logo-sub">Résultats &amp; Sessions</div>
                </div>
            </div>
        </div>
        <div class="pv-admin-header-right">
            <button id="pv-btn-export" class="pv-admin-btn pv-admin-btn--outline">⬇ Exporter CSV</button>
        </div>
    </div>

    <div class="pv-admin-card pv-admin-card--full">
        <!-- Barre de recherche -->
        <div class="pv-admin-toolbar">
            <div class="pv-admin-search-wrap">
                <span class="pv-search-icon">🔍</span>
                <input type="text" id="pv-search" placeholder="Rechercher par prénom ou email…" class="pv-admin-search">
            </div>
            <div class="pv-admin-toolbar-info" id="pv-count-label">—</div>
        </div>

        <!-- Table -->
        <div class="pv-table-wrap">
            <table class="pv-table" id="pv-sessions-table">
                <thead>
                    <tr>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Top 5 valeurs</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="pv-table-body">
                    <tr><td colspan="5" class="pv-admin-loading">Chargement…</td></tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pv-pagination" id="pv-pagination"></div>
    </div>

    <!-- Modal détail -->
    <div id="pv-modal" class="pv-modal" style="display:none;">
        <div class="pv-modal-overlay" id="pv-modal-overlay"></div>
        <div class="pv-modal-box">
            <div class="pv-modal-header">
                <div class="pv-modal-title" id="pv-modal-title">Détail du profil</div>
                <button class="pv-modal-close" id="pv-modal-close">✕</button>
            </div>
            <div class="pv-modal-body" id="pv-modal-body"></div>
        </div>
    </div>

</div>
