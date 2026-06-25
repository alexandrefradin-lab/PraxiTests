{{--
  PraxiQuest — Le Grimoire (relecture globale transversale) — PDF DomPDF.
  Variables :
    $user      App\Models\User (->profile)
    $grimoire  App\Models\ProfileGrimoire (->synthesis, ->voies, ->tests_included)
    $brand     ['name','tagline','logo','primary','secondary','accent']
    $org       ['name','advisor','email','phone','website','address','legal']
--}}
@php
    $brand = array_merge([
        'name'      => config('praxiquest.branding.name', 'PraxiQuest'),
        'tagline'   => config('praxiquest.branding.tagline', ''),
        'primary'   => config('praxiquest.branding.primary_color', '#A67520'),
        'secondary' => config('praxiquest.branding.secondary_color', '#7B1515'),
        'accent'    => '#1C1408',
    ], $brand ?? []);

    $org = array_merge([
        'name'  => $brand['name'],
        'legal' => 'Document confidentiel — usage personnel. Données traitées conformément au RGPD.',
        'advisor' => null, 'email' => null, 'phone' => null, 'website' => null,
    ], $org ?? []);

    $candidate = $user->name ?? 'Candidat';
    $profile   = $user->profile ?? null;
    $statuses  = config('praxiquest.profile.statuses', []);
    $statut    = $profile?->status ? ($statuses[$profile->status] ?? $profile->status) : null;
    $date      = $grimoire->generated_at ?? now();

    $synthesis = $grimoire->synthesis;
    $voies     = is_array($grimoire->voies) ? $grimoire->voies : [];
    $tests     = is_array($grimoire->tests_included) ? $grimoire->tests_included : [];

    $ink       = '#2A1E08';
    $inkSoft   = '#6B5A3E';
    $parchment = '#F0E8D4';
    $velin     = '#F8F7F4';
    $stone     = '#EEECE7';
    $hair      = '#E0DBD0';
    $primary   = $brand['primary'];
    $secondary = $brand['secondary'];
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<style>
    /* ── Polices embarquées (OFL) — Lora (titres) + Lato (corps). Repli DejaVu. ── */
    @if(($embedFonts ?? true))
    @font-face { font-family:'Lora'; font-style:normal; font-weight:normal; src:url("{{ resource_path('fonts/Lora-Regular.ttf') }}") format("truetype"); }
    @font-face { font-family:'Lora'; font-style:normal; font-weight:bold;   src:url("{{ resource_path('fonts/Lora-Bold.ttf') }}") format("truetype"); }
    @font-face { font-family:'Lora'; font-style:italic; font-weight:normal; src:url("{{ resource_path('fonts/Lora-Italic.ttf') }}") format("truetype"); }
    @font-face { font-family:'Lora'; font-style:italic; font-weight:bold;   src:url("{{ resource_path('fonts/Lora-BoldItalic.ttf') }}") format("truetype"); }
    @font-face { font-family:'Lato'; font-style:normal; font-weight:normal; src:url("{{ resource_path('fonts/Lato-Regular.ttf') }}") format("truetype"); }
    @font-face { font-family:'Lato'; font-style:normal; font-weight:bold;   src:url("{{ resource_path('fonts/Lato-Bold.ttf') }}") format("truetype"); }
    @font-face { font-family:'Lato'; font-style:italic; font-weight:normal; src:url("{{ resource_path('fonts/Lato-Italic.ttf') }}") format("truetype"); }
    @font-face { font-family:'Lato'; font-style:italic; font-weight:bold;   src:url("{{ resource_path('fonts/Lato-BoldItalic.ttf') }}") format("truetype"); }
    @endif

    @page {
        margin-top: 70px;
        margin-bottom: 52px;
        margin-left: 0px;
        margin-right: 0px;
    }

    * { font-family: 'Lato', DejaVu Sans, sans-serif; }

    body {
        color: {{ $ink }};
        font-size: 11px;
        line-height: 1.6;
        background: #FFFFFF;
        margin: 0;
        padding: 0 34px;
    }

    /* ── Running header (fixed, repeated on each page) ── */
    .running-header {
        position: fixed;
        top: -70px;
        left: 0;
        right: 0;
        background: #1C1408;
        height: 48px;
        padding: 0;
    }
    .running-header-inner {
        width: 100%;
        border-collapse: collapse;
        height: 48px;
    }
    .running-header-inner td {
        padding: 0 20px;
        vertical-align: middle;
    }
    .running-header-brand {
        font-size: 13px;
        font-weight: bold;
        color: #FFFFFF;
        font-family: 'Lato', DejaVu Sans, sans-serif;
    }
    .running-header-brand span {
        color: #A67520;
    }
    .running-header-title {
        font-size: 10px;
        color: #C8B48A;
        text-align: right;
        font-family: 'Lato', DejaVu Sans, sans-serif;
    }
    .running-header-bar {
        height: 2px;
        background: #A67520;
        position: fixed;
        top: -22px;
        left: 0;
        right: 0;
    }

    /* ── Running footer (fixed, repeated on each page) ── */
    .running-footer {
        position: fixed;
        bottom: -52px;
        left: 0;
        right: 0;
        border-top: 2px solid #A67520;
        background: #FAF8F4;
        padding: 0;
        height: 40px;
    }
    .running-footer-inner {
        width: 100%;
        border-collapse: collapse;
        height: 40px;
    }
    .running-footer-inner td {
        padding: 0 20px;
        vertical-align: middle;
    }
    .footer-legal-text {
        font-size: 8px;
        color: #9A8870;
        line-height: 1.4;
    }
    .footer-right-col {
        text-align: right;
    }
    .footer-brand-name {
        font-size: 8px;
        color: #A67520;
        font-weight: bold;
        letter-spacing: 1px;
        text-transform: uppercase;
    }
    .footer-date-text {
        font-size: 8px;
        color: #9A8870;
        margin-top: 2px;
    }

    /* ── Cover / header block ── */
    .cover {
        background: #1C1408;
        margin: 0 -34px 0 -34px;
        padding: 28px 36px 0 36px;
    }
    .cover-inner {
        width: 100%;
        border-collapse: collapse;
    }
    .cover-inner td {
        padding: 0;
        vertical-align: top;
    }
    .cover-left {
        padding-right: 20px;
    }
    .cover-kicker {
        font-size: 8.5px;
        font-weight: bold;
        letter-spacing: 2.5px;
        color: #A67520;
        text-transform: uppercase;
        margin-bottom: 8px;
        font-family: 'Lato', DejaVu Sans, sans-serif;
    }
    .cover-name {
        font-size: 26px;
        font-weight: bold;
        color: #FFFFFF;
        line-height: 1.1;
        margin-bottom: 4px;
        font-family: 'Lora', DejaVu Serif, serif;
    }
    .cover-tagline {
        font-size: 12px;
        color: #C8B48A;
        font-style: italic;
        margin-bottom: 14px;
        font-family: 'Lora', DejaVu Serif, serif;
    }
    .cover-meta-table {
        border-collapse: collapse;
        margin-bottom: 16px;
    }
    .cover-meta-table td {
        padding: 0 16px 0 0;
        vertical-align: top;
    }
    .meta-label {
        font-size: 8px;
        font-weight: bold;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: #A67520;
        margin-bottom: 2px;
        display: block;
    }
    .meta-value {
        font-size: 11px;
        font-weight: 500;
        color: #E8D8B8;
    }
    .cover-right {
        text-align: right;
        vertical-align: top;
        padding-top: 4px;
        width: 130px;
    }
    /* Logo circle — tableau pour centrage dompdf */
    .logo-circle-wrap {
        display: inline-block;
        width: 44px;
        height: 44px;
        border-radius: 22px;
        background: #A67520;
        margin-bottom: 8px;
    }
    .logo-circle-wrap table {
        width: 44px;
        height: 44px;
        border-collapse: collapse;
    }
    .logo-circle-wrap td {
        text-align: center;
        vertical-align: middle;
        padding: 0;
        font-family: 'Lora', DejaVu Serif, serif;
        font-weight: bold;
        font-size: 22px;
        color: #1C1408;
        line-height: 1;
    }
    .cover-logo-brand {
        font-size: 15px;
        font-weight: bold;
        color: #FFFFFF;
        margin-bottom: 6px;
        font-family: 'Lato', DejaVu Sans, sans-serif;
    }
    .cover-logo-brand span {
        color: #A67520;
    }
    .cover-logo-date {
        font-size: 9px;
        color: #8A7050;
    }
    /* Chips row */
    .cover-chips-row {
        padding: 12px 0 16px 0;
    }
    .chip {
        display: inline-block;
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(166,117,32,0.4);
        border-radius: 10px;
        padding: 2px 9px;
        margin: 2px 3px 2px 0;
        font-size: 9px;
        color: #C8B48A;
        font-family: 'Lato', DejaVu Sans, sans-serif;
    }
    /* Accent bar below cover */
    .accent-bar {
        height: 4px;
        background: #A67520;
        margin: 0 -34px 0 -34px;
        margin-bottom: 24px;
    }

    /* ── Section title — consulting style ── */
    .section-title-wrap {
        margin-bottom: 12px;
        margin-top: 22px;
    }
    .section-title-table {
        width: 100%;
        border-collapse: collapse;
    }
    .section-title-table td {
        padding: 0;
        vertical-align: middle;
    }
    .section-title-text {
        font-size: 8.5px;
        font-weight: bold;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: #1C1408;
        white-space: nowrap;
        padding-right: 10px;
        font-family: 'Lato', DejaVu Sans, sans-serif;
    }
    .section-title-line-gold {
        width: 28px;
        border-top: 1.5px solid #A67520;
        padding-right: 4px;
    }
    .section-title-line-gray {
        border-top: 1px solid #E2D8C8;
    }

    /* ── Synthesis block ── */
    .synthesis-block {
        background: #FAF8F4;
        border-left: 3px solid #A67520;
        padding: 14px 16px;
        margin-bottom: 6px;
    }
    .synthesis-para {
        font-size: 11px;
        line-height: 1.7;
        color: #2A1E08;
        text-align: justify;
        margin-bottom: 10px;
        font-family: 'Lato', DejaVu Sans, sans-serif;
    }
    .synthesis-para:last-child {
        margin-bottom: 0;
    }

    /* ── Voie card ── */
    .voie-card {
        background: #FAF8F4;
        border: 0.75px solid #E2D8C8;
        border-left: 3px solid #A67520;
        padding: 12px 14px;
        margin-bottom: 10px;
    }
    .voie-layout {
        width: 100%;
        border-collapse: collapse;
    }
    .voie-layout td {
        padding: 0;
        vertical-align: top;
    }
    /* Row 1: number circle | title | fit badge */
    .voie-num-cell {
        width: 32px;
        vertical-align: top;
        padding-right: 10px;
        padding-top: 1px;
    }
    .voie-num-circle {
        width: 26px;
        height: 26px;
        border-radius: 13px;
        background: #A67520;
        display: inline-block;
    }
    .voie-num-circle table {
        width: 26px;
        height: 26px;
        border-collapse: collapse;
    }
    .voie-num-circle td {
        text-align: center;
        vertical-align: middle;
        padding: 0;
        font-family: 'Lato', DejaVu Sans, sans-serif;
        font-weight: bold;
        font-size: 11px;
        color: #1C1408;
        line-height: 1;
    }
    .voie-title-cell {
        vertical-align: top;
        padding-top: 3px;
    }
    .voie-title-text {
        font-size: 13px;
        font-weight: bold;
        color: #2A1E08;
        font-family: 'Lora', DejaVu Serif, serif;
        line-height: 1.2;
    }
    .voie-fit-cell {
        width: 56px;
        text-align: right;
        vertical-align: top;
        padding-top: 2px;
    }
    .voie-fit-badge {
        display: inline-block;
        background: #7B1515;
        color: #F5DDB0;
        font-size: 9px;
        font-weight: bold;
        letter-spacing: 0.5px;
        padding: 3px 7px;
        font-family: 'Lato', DejaVu Sans, sans-serif;
    }
    /* Row 2: secteur */
    .voie-secteur {
        font-size: 9px;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        color: #6B5A3E;
        margin-top: 4px;
        margin-bottom: 5px;
        font-family: DejaVu Sans Mono, monospace;
    }
    /* Row 3: pourquoi */
    .voie-why {
        font-size: 11px;
        line-height: 1.65;
        color: #2A1E08;
        text-align: justify;
        margin-bottom: 6px;
        font-family: 'Lato', DejaVu Sans, sans-serif;
    }
    /* Row 4: appui */
    .voie-appui {
        font-size: 9px;
        color: #6B5A3E;
        margin-bottom: 5px;
        font-family: 'Lato', DejaVu Sans, sans-serif;
    }
    /* Row 5: next step */
    .voie-next {
        font-size: 10px;
        color: #2A1E08;
        font-family: 'Lato', DejaVu Sans, sans-serif;
    }
    .voie-next-label {
        font-weight: bold;
        color: #7D5510;
    }
</style>
</head>
<body>

{{-- ── Running header (dompdf fixed positioning) ── --}}
<div class="running-header">
    <table class="running-header-inner">
        <tr>
            <td class="running-header-brand">Praxi<span>Quest</span></td>
            <td class="running-header-title">Le Grimoire &middot; {{ $candidate }}</td>
        </tr>
    </table>
</div>
<div class="running-header-bar"></div>

{{-- ── Running footer ── --}}
<div class="running-footer">
    <table class="running-footer-inner">
        <tr>
            <td>
                <div class="footer-legal-text">{{ $org['legal'] }}</div>
            </td>
            <td class="footer-right-col" style="width:160px;">
                <div class="footer-brand-name">PraxiQuest</div>
                <div class="footer-date-text">{{ $date->format('d/m/Y') }}</div>
            </td>
        </tr>
    </table>
</div>

{{-- ── COVER BLOCK ── --}}
<div class="cover">
    <table class="cover-inner">
        <tr>
            <td class="cover-left">
                <div class="cover-kicker">Le Grimoire &middot; Relecture Globale</div>
                <div class="cover-name">{{ $candidate }}</div>
                @if($brand['tagline'])
                <div class="cover-tagline">{{ $brand['tagline'] }}</div>
                @endif
                <table class="cover-meta-table">
                    <tr>
                        @if($statut)
                        <td>
                            <span class="meta-label">Statut</span>
                            <span class="meta-value">{{ $statut }}</span>
                        </td>
                        @endif
                        <td>
                            <span class="meta-label">Date</span>
                            <span class="meta-value">{{ $date->format('d/m/Y') }}</span>
                        </td>
                        <td>
                            <span class="meta-label">Épreuves croisées</span>
                            <span class="meta-value">{{ count($tests) }}</span>
                        </td>
                    </tr>
                </table>
                @if(count($tests))
                <div class="cover-chips-row">
                    @foreach($tests as $t)
                        <span class="chip">{{ $t['test'] ?? 'Test' }}</span>
                    @endforeach
                </div>
                @else
                <div style="height:16px;"></div>
                @endif
            </td>
            <td class="cover-right">
                <div class="logo-circle-wrap">
                    <table><tr><td>Q</td></tr></table>
                </div>
                <div class="cover-logo-brand">Praxi<span>Quest</span></div>
                <div class="cover-logo-date">Émis le {{ $date->format('d/m/Y') }}</div>
            </td>
        </tr>
    </table>
</div>
<div class="accent-bar"></div>

{{-- ── SECTION : Le fil conducteur ── --}}
@if($synthesis)
<div class="section-title-wrap">
    <table class="section-title-table">
        <tr>
            <td class="section-title-text">Le fil conducteur</td>
            <td class="section-title-line-gold" style="width:28px;"></td>
            <td class="section-title-line-gray"></td>
        </tr>
    </table>
</div>
<div class="synthesis-block">
    @foreach(array_filter(explode("\n\n", $synthesis), fn($p) => trim($p) !== '') as $para)
        <div class="synthesis-para">{{ trim($para) }}</div>
    @endforeach
</div>
@endif

{{-- ── SECTION : Tes Voies Possibles ── --}}
@if(count($voies))
<div class="section-title-wrap">
    <table class="section-title-table">
        <tr>
            <td class="section-title-text">Tes Voies Possibles</td>
            <td class="section-title-line-gold" style="width:28px;"></td>
            <td class="section-title-line-gray"></td>
        </tr>
    </table>
</div>

@foreach($voies as $i => $v)
<div class="voie-card">
    <table class="voie-layout">
        {{-- Row 1 : numéro | titre | fit score --}}
        <tr>
            <td class="voie-num-cell">
                <div class="voie-num-circle">
                    <table><tr><td>{{ $i + 1 }}</td></tr></table>
                </div>
            </td>
            <td class="voie-title-cell">
                <div class="voie-title-text">{{ $v['titre'] ?? '' }}</div>
            </td>
            @if(isset($v['fit_score']))
            <td class="voie-fit-cell">
                <span class="voie-fit-badge">{{ min(100, (int)$v['fit_score']) }}%</span>
            </td>
            @endif
        </tr>
        {{-- Row 2 : secteur --}}
        @if(!empty($v['secteur']))
        <tr>
            <td></td>
            <td colspan="2">
                <div class="voie-secteur">{{ $v['secteur'] }}</div>
            </td>
        </tr>
        @endif
        {{-- Row 3 : pourquoi --}}
        @if(!empty($v['pourquoi']))
        <tr>
            <td></td>
            <td colspan="2">
                <div class="voie-why">{{ $v['pourquoi'] }}</div>
            </td>
        </tr>
        @endif
        {{-- Row 4 : appuyé par --}}
        @if(!empty($v['appui']))
        <tr>
            <td></td>
            <td colspan="2">
                <div class="voie-appui">Appuyé par : {{ $v['appui'] }}</div>
            </td>
        </tr>
        @endif
        {{-- Row 5 : prochaine étape --}}
        @if(!empty($v['prochaine_etape']))
        <tr>
            <td></td>
            <td colspan="2">
                <div class="voie-next">
                    <span class="voie-next-label">Prochaine étape &rarr;</span>
                    {{ $v['prochaine_etape'] }}
                </div>
            </td>
        </tr>
        @endif
    </table>
</div>
@endforeach
@endif

</body>
</html>
