{{--
  ════════════════════════════════════════════════════════════════════════════
  PraxiQuest — Rapport de synthèse PDF « Codex »
  Direction artistique : palette Assassin's Creed du site (parchemin / or de la
  Fraternité / cramoisi / encre ancienne). Titres en DejaVu Serif (manuscrit),
  corps en DejaVu Sans, données en DejaVu Sans Mono — toutes livrées avec DomPDF
  (accents OK, aucun chargement de police distant).
  Moteur : barryvdh/laravel-dompdf (CSS 2.1 → mise en page par <table>).

  Variables (toutes optionnelles, valeurs par défaut gérées ici) :
    $attempt   App\Models\TestAttempt (->test, ->result, ->user.profile)
    $brand     ['name','tagline','logo','primary','secondary','accent']
    $org       ['name','advisor','email','phone','website','address','legal']
    $sections  ['cover','profile','synthesis','strengths','dimensions','jobs','footer']
    $statuses  map code => libellé (config praxiquest.profile.statuses)
  ════════════════════════════════════════════════════════════════════════════
--}}
@php
    /* ---- Palette « Codex » par défaut (alignée sur le design system) ------ */
    $brand = array_merge([
        'name'      => config('praxiquest.branding.name', 'PraxiQuest'),
        'tagline'   => config('praxiquest.branding.tagline', "Deviens le personnage que tu n'avais pas encore nommé"),
        'logo'      => config('praxiquest.branding.logo'),
        'primary'   => config('praxiquest.branding.primary_color', '#A67520'),   // Or de la Fraternité
        'secondary' => config('praxiquest.branding.secondary_color', '#7B1515'), // Cramoisi
        'accent'    => '#1C1408',                                                // Encre ancienne
    ], $brand ?? []);

    $org = array_merge([
        'name'    => $brand['name'],
        'advisor' => null, 'email' => null, 'phone' => null,
        'website' => null, 'address' => null,
        'legal'   => 'Document confidentiel — usage personnel. Données traitées conformément au RGPD.',
    ], $org ?? []);

    $sections = array_merge([
        'cover' => true, 'profile' => true, 'synthesis' => true,
        'strengths' => true, 'dimensions' => true, 'jobs' => true, 'footer' => true,
    ], $sections ?? []);

    $statuses = $statuses ?? config('praxiquest.profile.statuses', []);

    /* ---- Données dérivées ----------------------------------------------- */
    $result    = $attempt->result;
    $profile   = $attempt->user->profile ?? null;
    $test      = $attempt->test;
    $candidate = $attempt->user->name ?? 'Candidat';
    $dateDone  = $attempt->completed_at ?? $attempt->updated_at ?? now();

    $primary   = $brand['primary'];     // or
    $secondary = $brand['secondary'];   // cramoisi
    $accent    = $brand['accent'];      // encre

    /* Tokens issus du design brief */
    $ink        = '#2A1E08';   // texte principal
    $inkSoft    = '#6B5A3E';   // texte secondaire / labels
    $parchment  = '#F0E8D4';   // fond base
    $velin      = '#E5DAC2';   // surface cards
    $stone      = '#D8CEB5';   // fond élevé / tracks
    $goldDark   = '#7D5510';   // or brûlé
    $eagle      = '#3A6B48';   // vert Eagle Vision (succès / matching)
    $hair       = '#CBBE9E';   // filet discret sur parchemin

    $synthesis  = $result?->ai_synthesis;
    /* Normalisation universelle : quel que soit le moteur du test, on obtient
       un résultat-phare + des barres de dimensions + d'éventuelles sous-échelles. */
    $present    = \Praxis\Core\TestEngine\ScoringPresenter::from($result?->scoring);
    $headline   = $present['headline'];
    $dimensions = $present['dimensions'];
    $subscales  = $present['subscales'];
    $jobs       = $result?->suggested_jobs ?? [];
    $strengths  = is_array($result?->strengths) ? $result->strengths : [];
    $growth     = is_array($result?->growth_areas) ? $result->growth_areas : [];

    /* Helpers locaux */
    $statusLabel = $profile?->status ? ($statuses[$profile->status] ?? $profile->status) : null;
    $seniority = null;
    if ($profile?->status_months !== null) {
        $m = (int) $profile->status_months;
        $y = intdiv($m, 12); $rem = $m % 12;
        $seniority = trim(($y ? "{$y} an" . ($y > 1 ? 's' : '') . ' ' : '') . ($rem ? "{$rem} mois" : '')) ?: "moins d'un mois";
    }
    /* Échelle de correspondance — vert Eagle Vision → or → cramoisi */
    $fitColor = function ($score) use ($eagle, $primary, $goldDark, $secondary) {
        $s = (int) $score;
        if ($s >= 80) return $eagle;
        if ($s >= 60) return $primary;
        if ($s >= 40) return $goldDark;
        return $secondary;
    };
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>{{ $test->name }} — {{ $candidate }}</title>
<style>
    @page { margin: 132px 0 96px 0; }
    * { box-sizing: border-box; }
    html { background: {{ $parchment }}; }
    body {
        font-family: "DejaVu Sans", sans-serif;
        background: {{ $parchment }};
        color: {{ $ink }};
        font-size: 11px;
        line-height: 1.6;
        margin: 0;
    }
    .serif { font-family: "DejaVu Serif", serif; }
    .px { padding-left: 50px; padding-right: 50px; }

    /* ── En-tête répété ───────────────────────────────────────────────── */
    .run-header { position: fixed; top: -112px; left: 0; right: 0; height: 74px; padding: 0 50px; }
    .run-header .mark {
        font-family: "DejaVu Serif", serif; font-size: 13px; font-weight: bold;
        color: {{ $accent }}; letter-spacing: .5px;
    }
    .run-header .mark .q { color: {{ $primary }}; }
    .run-header .doc { font-size: 8.5px; color: {{ $inkSoft }}; text-align: right;
        text-transform: uppercase; letter-spacing: 1.2px; padding-top: 4px; }
    /* double filet or — épais + fin */
    .run-rule  { position: fixed; top: -52px; left: 50px; right: 50px; border-top: 2px solid {{ $primary }}; }
    .run-rule2 { position: fixed; top: -48px; left: 50px; right: 50px; border-top: 0.5px solid {{ $hair }}; }

    /* ── Pied répété ──────────────────────────────────────────────────── */
    .run-footer {
        position: fixed; bottom: -74px; left: 0; right: 0; height: 64px;
        padding: 10px 50px 0; font-size: 7.8px; color: {{ $inkSoft }};
        border-top: 0.75px solid {{ $hair }};
    }

    /* ── Titres de section ────────────────────────────────────────────── */
    .kicker {
        font-size: 8px; letter-spacing: 3px; text-transform: uppercase;
        color: {{ $primary }}; font-weight: bold; margin: 0; font-family: "DejaVu Sans Mono", monospace;
    }
    h2.section {
        font-family: "DejaVu Serif", serif; font-size: 17px; color: {{ $accent }};
        margin: 4px 0 0; padding: 0; letter-spacing: .2px;
    }
    .section-rule { border-bottom: 1.5px solid {{ $primary }}; margin: 9px 0 0; width: 46px; }
    .section-hair { border-bottom: 0.75px solid {{ $hair }}; margin: 4px 0 16px; }
    .sec { margin-top: 26px; }
    .avoid-break { page-break-inside: avoid; }

    /* ── Cartes / blocs ───────────────────────────────────────────────── */
    .card { background: {{ $velin }}; border: 0.75px solid {{ $hair }}; border-radius: 10px; }
    .lead-gold { border-left: 4px solid {{ $primary }}; }

    /* Profil — table d'identité */
    table.kv { width: 100%; border-collapse: collapse; }
    table.kv td { padding: 8px 14px; vertical-align: top; border-bottom: 0.75px solid {{ $hair }}; }
    table.kv td.k { width: 34%; color: {{ $inkSoft }}; font-size: 8.5px; text-transform: uppercase;
        letter-spacing: 1.2px; font-family: "DejaVu Sans Mono", monospace; }
    table.kv td.v { font-size: 11.5px; font-weight: bold; color: {{ $accent }}; }

    /* Dimensions — barres */
    table.dims { width: 100%; border-collapse: collapse; }
    table.dims td { padding: 7px 0; vertical-align: middle; }
    .dim-name { font-size: 10.5px; text-transform: capitalize; color: {{ $ink }}; }
    .track { background: {{ $stone }}; height: 10px; border-radius: 6px; width: 100%; border: 0.5px solid {{ $hair }}; }
    .fill { height: 10px; border-radius: 6px; }
    .dim-score { font-family: "DejaVu Sans Mono", monospace; font-size: 11px; font-weight: bold;
        text-align: right; color: {{ $goldDark }}; }

    /* Métiers */
    .job { padding: 13px 16px; margin-bottom: 11px; }
    .job-rank {
        display: inline-block; width: 26px; height: 26px; line-height: 26px;
        text-align: center; border-radius: 13px; color: {{ $parchment }}; font-weight: bold;
        font-size: 12px; background: {{ $primary }}; font-family: "DejaVu Serif", serif;
    }
    .job-sector { font-size: 8px; text-transform: uppercase; letter-spacing: 1.5px; color: {{ $inkSoft }};
        font-family: "DejaVu Sans Mono", monospace; }
    .job-title { font-family: "DejaVu Serif", serif; font-size: 13px; font-weight: bold; color: {{ $accent }}; }
    .job-why  { font-size: 10.5px; color: {{ $ink }}; margin: 5px 0 0; }
    .job-next { font-size: 10px; color: {{ $secondary }}; margin-top: 6px; font-weight: bold; }
    .fit-pill { font-family: "DejaVu Sans Mono", monospace; font-size: 11px; font-weight: bold;
        color: {{ $parchment }}; padding: 4px 10px; border-radius: 11px; }

    .chip { display: inline-block; padding: 5px 11px; margin: 0 5px 6px 0; border-radius: 13px;
        font-size: 10px; border: 0.5px solid; }
    .chip-up   { background: #EAF1E9; color: {{ $eagle }};     border-color: #BcD3BE; }
    .chip-grow { background: #F3E4DF; color: {{ $secondary }}; border-color: #E0BFb6; }

    /* ── Carte « Verdict » — résultat-phare ───────────────────────────── */
    .hero { background: {{ $accent }}; border: 1.5px solid {{ $primary }}; border-radius: 14px; }
    .medallion {
        width: 86px; height: 86px; line-height: 86px; text-align: center;
        border-radius: 43px; border: 3px solid {{ $primary }};
        background: {{ $accent }}; color: {{ $parchment }};
        font-family: "DejaVu Serif", serif; font-weight: bold; font-size: 30px;
    }
    .medallion .pctsign { font-size: 14px; color: {{ $primary }}; }
    .medallion-cap { font-size: 7.5px; letter-spacing: 1.5px; text-transform: uppercase;
        color: #9C8A60; text-align: center; margin-top: 7px; font-family: "DejaVu Sans Mono", monospace; }
    .hero-kicker { font-size: 8px; letter-spacing: 3px; text-transform: uppercase;
        color: {{ $primary }}; font-weight: bold; font-family: "DejaVu Sans Mono", monospace; }
    .hero-label  { font-family: "DejaVu Serif", serif; font-size: 22px; font-weight: bold;
        color: {{ $parchment }}; line-height: 1.2; margin-top: 5px; }
    .code-chip { display: inline-block; margin-left: 8px; padding: 2px 10px; border-radius: 11px;
        background: {{ $primary }}; color: {{ $accent }}; font-size: 12px; font-weight: bold;
        font-family: "DejaVu Sans Mono", monospace; letter-spacing: 2px; vertical-align: middle; }
    .hero-score { font-family: "DejaVu Sans Mono", monospace; font-size: 9px; color: #B9A87E;
        text-transform: uppercase; letter-spacing: 1.2px; margin-top: 8px; }
    .hero-score b { color: {{ $primary }}; font-size: 11px; }
    .hero-phrase { font-size: 11px; color: #E9DFC6; line-height: 1.65; margin-top: 9px; }

    /* ── Niveau (étiquette à droite des barres) ───────────────────────── */
    .dim-level { font-size: 8px; color: {{ $inkSoft }}; text-transform: uppercase;
        letter-spacing: .8px; font-family: "DejaVu Sans Mono", monospace; }

    /* ── Sous-échelles groupées ───────────────────────────────────────── */
    .sub-group { margin-top: 14px; }
    .sub-title { font-family: "DejaVu Sans Mono", monospace; font-size: 8.5px; font-weight: bold;
        letter-spacing: 1.5px; text-transform: uppercase; color: {{ $goldDark }}; margin-bottom: 6px; }
    .sub-val { font-family: "DejaVu Sans Mono", monospace; font-size: 10px; font-weight: bold;
        color: {{ $goldDark }}; text-align: right; }
    .sev-pill { display: inline-block; padding: 2px 8px; border-radius: 9px; font-size: 8px;
        font-weight: bold; color: {{ $parchment }}; font-family: "DejaVu Sans Mono", monospace; }
</style>
</head>
<body>

{{-- ═══════════════ EN-TÊTE & PIED RÉPÉTÉS ═══════════════ --}}
<div class="run-header">
    <table style="width:100%"><tr>
        <td class="mark serif">Praxi<span class="q">Quest</span></td>
        <td class="doc">{{ $test->name }} · {{ $candidate }}</td>
    </tr></table>
</div>
<div class="run-rule"></div>
<div class="run-rule2"></div>

@if($sections['footer'])
<div class="run-footer">
    <table style="width:100%"><tr>
        <td style="width:68%">{{ $org['legal'] }}</td>
        <td style="width:32%; text-align:right">{{ $org['website'] ?? $brand['name'] }} · {{ $dateDone->format('d/m/Y') }}</td>
    </tr></table>
</div>
@endif

{{-- Numérotation de page (méthode DomPDF native) --}}
<script type="text/php">
    if (isset($pdf)) {
        $font = $fontMetrics->get_font("DejaVu Sans Mono", "normal");
        $pdf->page_text(500, 812, "— {PAGE_NUM} / {PAGE_COUNT} —", $font, 7.5, array(0.42,0.35,0.24));
    }
</script>

{{-- ═══════════════ PAGE DE GARDE — PLAQUE CODEX ═══════════════ --}}
@if($sections['cover'])
<div style="height: 26px;"></div>
<div class="px">
    {{-- Plaque d'encre encadrée d'or, façon planche de manuscrit --}}
    <table style="width:100%; border-collapse:separate; background:{{ $accent }}; border:1.5px solid {{ $primary }}; border-radius:14px;">
        <tr><td style="padding:42px 40px 38px;">
            {{-- filet or intérieur --}}
            <div style="border-top:0.75px solid {{ $primary }}; width:54px; margin-bottom:22px;"></div>

            @if(!empty($brand['logo']))
                <img src="{{ $brand['logo'] }}" alt="{{ $brand['name'] }}" style="max-height:48px; margin-bottom:18px;">
            @else
                <div class="serif" style="font-size:24px; font-weight:bold; color:{{ $parchment }}; letter-spacing:.5px;">
                    Praxi<span style="color:{{ $primary }};">Quest</span>
                </div>
            @endif
            <div style="font-size:9px; color:#B9A87E; margin-top:4px; font-style:italic;">{{ $brand['tagline'] }}</div>

            <div style="height:30px;"></div>
            <div class="kicker" style="color:{{ $primary }};">Rapport de synthèse &amp; orientation</div>
            <div class="serif" style="font-size:30px; font-weight:bold; color:{{ $parchment }}; line-height:1.18; margin-top:8px;">
                {{ $test->name }}
            </div>

            <div style="height:26px;"></div>
            <table style="width:100%; border-collapse:collapse;">
                <tr>
                    <td style="vertical-align:top; border-top:0.75px solid #463a22; padding-top:14px; width:55%;">
                        <div class="kicker" style="color:#9C8A60;">Établi pour</div>
                        <div class="serif" style="font-size:17px; font-weight:bold; color:{{ $parchment }}; margin-top:4px;">{{ $candidate }}</div>
                        @if($statusLabel)
                            <div style="font-size:10px; color:#B9A87E; margin-top:3px;">{{ $statusLabel }}@if($seniority) · {{ $seniority }} d'ancienneté @endif</div>
                        @endif
                    </td>
                    <td style="vertical-align:top; border-top:0.75px solid #463a22; padding-top:14px; padding-left:18px; text-align:right;">
                        <div class="kicker" style="color:#9C8A60;">Date</div>
                        <div class="serif" style="font-size:15px; font-weight:bold; color:{{ $parchment }}; margin-top:4px;">{{ $dateDone->format('d/m/Y') }}</div>
                        @if(count($jobs))
                            <div style="font-size:10px; color:#B9A87E; margin-top:3px;">{{ count($jobs) }} pistes métiers</div>
                        @endif
                    </td>
                </tr>
            </table>
        </td></tr>
    </table>

    @if($org['advisor'] || $org['email'] || $org['phone'])
    <div style="margin-top:20px; font-size:10px; color:{{ $inkSoft }};">
        Accompagné par
        <strong style="color:{{ $accent }}">{{ $org['advisor'] ?? $org['name'] }}</strong>@if($org['email']) · {{ $org['email'] }}@endif @if($org['phone']) · {{ $org['phone'] }}@endif
    </div>
    @endif
</div>
<div style="page-break-after: always;"></div>
@endif

{{-- ═══════════════ PROFIL DU CANDIDAT ═══════════════ --}}
@if($sections['profile'] && $profile)
<div class="px avoid-break sec">
    <p class="kicker">Contexte</p>
    <h2 class="section serif">Profil du candidat</h2>
    <div class="section-rule"></div>
    <div class="section-hair"></div>
    <table class="kv">
        <tr><td class="k">Nom</td><td class="v">{{ $candidate }}</td></tr>
        @if($statusLabel)<tr><td class="k">Statut</td><td class="v">{{ $statusLabel }}</td></tr>@endif
        @if($seniority)<tr><td class="k">Ancienneté</td><td class="v">{{ $seniority }}</td></tr>@endif
        @if($profile->current_role)<tr><td class="k">Poste actuel</td><td class="v">{{ $profile->current_role }}</td></tr>@endif
        @if($profile->industry)<tr><td class="k">Secteur</td><td class="v">{{ $profile->industry }}</td></tr>@endif
        @if($profile->cv_original_name)<tr><td class="k">CV fourni</td><td class="v">{{ $profile->cv_original_name }}</td></tr>@endif
        <tr><td class="k">Évaluation</td><td class="v">{{ $test->name }}</td></tr>
    </table>
</div>
@endif

{{-- ═══════════════ RÉSULTAT-PHARE (toujours affiché si disponible) ═══════════════ --}}
@if($headline)
<div class="px sec avoid-break">
    <p class="kicker">Votre résultat</p>
    <h2 class="section serif">Verdict de l'évaluation</h2>
    <div class="section-rule"></div>
    <div class="section-hair"></div>
    <table class="hero" style="width:100%; border-collapse:separate;">
        <tr>
            @if($headline['pct'] !== null)
            <td style="width:118px; vertical-align:middle; padding:20px 0 20px 22px;">
                <div class="medallion">{{ $headline['pct'] }}<span class="pctsign">%</span></div>
                <div class="medallion-cap">Indice global</div>
            </td>
            @endif
            <td style="vertical-align:middle; padding:20px 24px;">
                <div class="hero-kicker">Profil identifié</div>
                <div class="hero-label">
                    {{ $headline['label'] ?? 'Profil établi' }}@if($headline['code'])<span class="code-chip">{{ $headline['code'] }}</span>@endif
                </div>
                @if($headline['score'] !== null && $headline['score_max'])
                    <div class="hero-score">Score global&nbsp; <b>{{ $headline['score'] }}</b> / {{ $headline['score_max'] }}</div>
                @endif
                @if($headline['phrase'])
                    <div class="hero-phrase">{{ $headline['phrase'] }}</div>
                @endif
            </td>
        </tr>
    </table>
</div>
@endif

{{-- ═══════════════ SYNTHÈSE IA ═══════════════ --}}
@if($sections['synthesis'] && $synthesis)
<div class="px sec">
    <p class="kicker">Lecture du profil</p>
    <h2 class="section serif">Synthèse</h2>
    <div class="section-rule"></div>
    <div class="section-hair"></div>
    <table class="card lead-gold" style="width:100%; border-collapse:separate;">
        <tr><td style="padding:18px 22px;">
            <div style="white-space:pre-line; font-size:11.5px; line-height:1.78; color:{{ $ink }};">{{ $synthesis }}</div>
        </td></tr>
    </table>
</div>
@endif

{{-- ═══════════════ FORCES & AXES DE PROGRESSION ═══════════════ --}}
@if($sections['strengths'] && (count($strengths) || count($growth)))
<div class="px avoid-break sec">
    <p class="kicker">Leviers</p>
    <h2 class="section serif">Points forts &amp; axes de progression</h2>
    <div class="section-rule"></div>
    <div class="section-hair"></div>
    <table style="width:100%; border-collapse:separate;">
        <tr>
            @if(count($strengths))
            <td style="width:50%; vertical-align:top; padding-right:10px;">
                <div class="kicker" style="color:{{ $eagle }}; margin-bottom:9px;">● Points forts</div>
                @foreach($strengths as $s)
                    <span class="chip chip-up">{{ is_array($s) ? ($s['label'] ?? $s['name'] ?? reset($s)) : $s }}</span>
                @endforeach
            </td>
            @endif
            @if(count($growth))
            <td style="width:50%; vertical-align:top; padding-left:10px;">
                <div class="kicker" style="color:{{ $secondary }}; margin-bottom:9px;">▲ Axes de progression</div>
                @foreach($growth as $g)
                    <span class="chip chip-grow">{{ is_array($g) ? ($g['label'] ?? $g['name'] ?? reset($g)) : $g }}</span>
                @endforeach
            </td>
            @endif
        </tr>
    </table>
</div>
@endif

{{-- ═══════════════ DIMENSIONS ═══════════════ --}}
@if($sections['dimensions'] && count($dimensions))
<div class="px avoid-break sec">
    <p class="kicker">Mesures</p>
    <h2 class="section serif">Profil par dimension</h2>
    <div class="section-rule"></div>
    <div class="section-hair"></div>
    <table class="dims">
        @foreach($dimensions as $dim)
            @php $sc = max(0, min(100, (int) $dim['pct'])); @endphp
            <tr>
                <td style="width:31%;">
                    <span class="dim-name">{{ $dim['name'] }}</span>
                    @if(!empty($dim['level']))<div class="dim-level">{{ $dim['level'] }}</div>@endif
                </td>
                <td style="width:55%; padding-left:12px; padding-right:12px;">
                    <div class="track"><div class="fill" style="width:{{ $sc }}%; background:{{ $fitColor($sc) }};"></div></div>
                </td>
                <td style="width:14%;"><div class="dim-score">{{ $sc }}</div></td>
            </tr>
        @endforeach
    </table>
</div>
@endif

{{-- ═══════════════ SOUS-ÉCHELLES (Karasek, MBI, facettes…) ═══════════════ --}}
@if($sections['dimensions'] && count($subscales))
<div class="px avoid-break sec">
    <p class="kicker">Détail</p>
    <h2 class="section serif">Sous-échelles</h2>
    <div class="section-rule"></div>
    <div class="section-hair"></div>
    @foreach($subscales as $group)
        <div class="sub-group avoid-break">
            <div class="sub-title">{{ $group['title'] }}</div>
            <table class="dims">
                @foreach($group['items'] as $item)
                    @php $sc = $item['pct'] !== null ? max(0, min(100, (int) $item['pct'])) : null; @endphp
                    <tr>
                        <td style="width:34%;"><span class="dim-name">{{ $item['name'] }}</span></td>
                        <td style="width:42%; padding-left:12px; padding-right:12px;">
                            @if($sc !== null)
                                <div class="track"><div class="fill" style="width:{{ $sc }}%; background:{{ $primary }};"></div></div>
                            @endif
                        </td>
                        <td style="width:12%;">
                            <div class="sub-val">@if($item['value'] !== null){{ $item['value'] }}@if($item['max'])<span style="color:{{ $inkSoft }}; font-weight:normal;">/{{ $item['max'] }}</span>@endif @endif</div>
                        </td>
                        <td style="width:12%; text-align:right;">
                            @if(!empty($item['level']))<span class="sev-pill" style="background:{{ $goldDark }};">{{ $item['level'] }}</span>@endif
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endforeach
</div>
@endif

{{-- ═══════════════ MÉTIERS À EXPLORER ═══════════════ --}}
@if($sections['jobs'] && count($jobs))
<div style="page-break-before: always;"></div>
<div class="px sec">
    <p class="kicker">Orientation</p>
    <h2 class="section serif">{{ count($jobs) }} métiers à explorer</h2>
    <div class="section-rule"></div>
    <div class="section-hair"></div>

    @foreach($jobs as $i => $job)
        @php
            $titre   = $job['titre'] ?? $job['title'] ?? '';
            $secteur = $job['secteur'] ?? $job['sector'] ?? '';
            $fit     = $job['fit_score'] ?? $job['fit'] ?? null;
            $why     = $job['pourquoi'] ?? $job['why'] ?? '';
            $next    = $job['prochaine_étape'] ?? $job['prochaine_etape'] ?? $job['next_step'] ?? null;
        @endphp
        <table class="card job avoid-break" style="width:100%; border-collapse:separate;">
            <tr>
                <td style="width:34px; vertical-align:top; padding-top:1px;">
                    <span class="job-rank">{{ $i + 1 }}</span>
                </td>
                <td style="vertical-align:top;">
                    <div class="job-sector">{{ $secteur }}</div>
                    <div class="job-title serif">{{ $titre }}</div>
                    @if($why)<div class="job-why">{{ $why }}</div>@endif
                    @if($next)<div class="job-next">→ {{ $next }}</div>@endif
                </td>
                @if($fit !== null)
                <td style="width:56px; text-align:right; vertical-align:top;">
                    <span class="fit-pill" style="background:{{ $fitColor($fit) }};">{{ (int) $fit }}%</span>
                </td>
                @endif
            </tr>
        </table>
    @endforeach
</div>
@endif

{{-- ═══════════════ BLOC COORDONNÉES / CONTACT ═══════════════ --}}
@if($sections['footer'] && ($org['advisor'] || $org['email'] || $org['phone'] || $org['website'] || $org['address']))
<div class="px avoid-break sec">
    <table class="card" style="width:100%; border-collapse:separate; background:{{ $stone }};">
        <tr><td style="padding:18px 22px;">
            <div class="kicker" style="margin-bottom:8px;">Pour aller plus loin</div>
            <table style="width:100%"><tr>
                <td style="vertical-align:top;">
                    <div class="serif" style="font-size:13px; font-weight:bold; color:{{ $accent }};">{{ $org['name'] }}</div>
                    @if($org['advisor'])<div style="font-size:10.5px; color:{{ $ink }};">{{ $org['advisor'] }}</div>@endif
                    @if($org['address'])<div style="font-size:10px; color:{{ $inkSoft }}; margin-top:3px;">{{ $org['address'] }}</div>@endif
                </td>
                <td style="vertical-align:top; text-align:right; font-size:10.5px; color:{{ $ink }};">
                    @if($org['email'])<div>{{ $org['email'] }}</div>@endif
                    @if($org['phone'])<div>{{ $org['phone'] }}</div>@endif
                    @if($org['website'])<div style="color:{{ $goldDark }}; font-weight:bold;">{{ $org['website'] }}</div>@endif
                </td>
            </tr></table>
        </td></tr>
    </table>
</div>
@endif

</body>
</html>
