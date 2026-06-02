<?php if ( ! defined('ABSPATH') ) exit; ?>
<div class="pv-admin-wrap">

    <!-- HEADER -->
    <div class="pv-admin-header">
        <div class="pv-admin-header-left">
            <div class="pv-admin-logo">
                <span class="pv-admin-logo-icon">◆</span>
                <div>
                    <div class="pv-admin-logo-name">PraxiValeurs</div>
                    <div class="pv-admin-logo-sub">Console d'administration</div>
                </div>
            </div>
        </div>
        <div class="pv-admin-header-right">
            <a href="<?php echo esc_url(admin_url('admin.php?page=praxivaleurs-results')); ?>" class="pv-admin-btn">Voir les résultats →</a>
        </div>
    </div>

    <!-- KPI CARDS -->
    <div class="pv-admin-kpis" id="pv-kpis">
        <div class="pv-kpi-card">
            <div class="pv-kpi-icon">👥</div>
            <div class="pv-kpi-value" id="kpi-total">—</div>
            <div class="pv-kpi-label">Profils complétés</div>
        </div>
        <div class="pv-kpi-card">
            <div class="pv-kpi-icon">📅</div>
            <div class="pv-kpi-value" id="kpi-month">—</div>
            <div class="pv-kpi-label">Ce mois-ci</div>
        </div>
        <div class="pv-kpi-card">
            <div class="pv-kpi-icon">⭐</div>
            <div class="pv-kpi-value" id="kpi-top-dim">—</div>
            <div class="pv-kpi-label">Valeur dominante globale</div>
        </div>
        <div class="pv-kpi-card pv-kpi-card--shortcode">
            <div class="pv-kpi-icon">🔗</div>
            <div class="pv-kpi-value pv-kpi-code">[praxivaleurs]</div>
            <div class="pv-kpi-label">Shortcode à coller dans une page</div>
        </div>
    </div>

    <!-- GRAPHIQUES -->
    <div class="pv-admin-charts">
        <div class="pv-admin-card">
            <div class="pv-admin-card-title">Profils par mois</div>
            <div class="pv-chart-wrap">
                <canvas id="chart-monthly" height="220"></canvas>
            </div>
        </div>
        <div class="pv-admin-card">
            <div class="pv-admin-card-title">Radar des valeurs (moyenne globale)</div>
            <div class="pv-chart-wrap pv-chart-wrap--radar">
                <canvas id="chart-radar" height="260"></canvas>
            </div>
        </div>
    </div>

    <!-- DERNIERS PROFILS -->
    <div class="pv-admin-card pv-admin-card--full">
        <div class="pv-admin-card-header">
            <div class="pv-admin-card-title">Derniers profils</div>
            <a href="<?php echo esc_url(admin_url('admin.php?page=praxivaleurs-results')); ?>" class="pv-admin-link">Voir tout →</a>
        </div>
        <div id="pv-dashboard-table">
            <div class="pv-admin-loading">Chargement…</div>
        </div>
    </div>

</div>
