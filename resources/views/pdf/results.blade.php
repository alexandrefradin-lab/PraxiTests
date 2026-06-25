{{--
  ════════════════════════════════════════════════════════════════════════════
  PraxiQuest — Rapport de synthèse PDF « Conseil »
  Direction artistique : fond blanc professionnel + palette PraxiQuest (or de la
  Fraternité #A67520, encre ancienne #1C1408, cramoisi #7B1515, vert Eagle #3A6B48).
  Titres en Lora (serif), corps en Lato (sans-serif), données en DejaVu Sans Mono.
  Lora + Lato embarquées (resources/fonts, licence OFL) ; repli DejaVu conservé.
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
    $parchment  = '#F0E8D4';   // conservé pour textes sur fond sombre (cover, hero, job-rank)
    $velin      = '#F8F7F4';   // surface cards — neutre professionnel
    $stone      = '#EEECE7';   // fond élevé / tracks
    $goldDark   = '#7D5510';   // or brûlé
    $eagle      = '#3A6B48';   // vert Eagle Vision (succès / matching)
    $hair       = '#E0DBD0';   // filet discret neutre

    $synthesis  = $result?->ai_synthesis;
    /* Normalisation universelle : quel que soit le moteur du test, on obtient
       un résultat-phare + des barres de dimensions + d'éventuelles sous-échelles. */
    $present    = \Praxis\Core\TestEngine\ScoringPresenter::from($result?->scoring);
    $headline   = $present['headline'];
    $dimensions = $present['dimensions'];
    $subscales  = $present['subscales'];
    /* Avertissement non-diagnostique éventuel, fourni par le moteur de scoring
       (tests d'inspiration clinique : burnout, intelligence émotionnelle…). */
    $disclaimer = is_string($result?->scoring['disclaimer'] ?? null) ? $result->scoring['disclaimer'] : null;
    $jobs       = $result?->suggested_jobs ?? [];
    $strengths  = is_array($result?->strengths) ? $result->strengths : [];
    $growth     = is_array($result?->growth_areas) ? $result->growth_areas : [];

    /* ---- Graphiques (PNG haute résolution via GD) -----------------------
       Rendu serveur en images : dompdf n'a pas de support SVG fiable. Échoue
       en silence (null) si GD/FreeType absents → repli sur les barres HTML. */
    $scoringRaw = $result?->scoring ?? [];
    $radarAxes  = [];
    foreach ($dimensions as $d) {
        if (count($radarAxes) >= 12) break;
        $radarAxes[] = ['label' => $d['name'], 'value' => max(0, min(100, (int) $d['pct']))];
    }
    $radarUri = count($radarAxes) >= 3
        ? \App\Support\ChartRenderer::radar($radarAxes, ['accent' => $primary])
        : null;

    $karasek     = is_array($scoringRaw['karasek'] ?? null) ? $scoringRaw['karasek'] : null;
    $quadrantUri = $karasek
        ? \App\Support\ChartRenderer::karasekQuadrant(
            $karasek,
            is_array($scoringRaw['meta_profiles'] ?? null) ? $scoringRaw['meta_profiles'] : [],
            is_string($scoringRaw['profile'] ?? null) ? $scoringRaw['profile'] : null
        )
        : null;

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

    /* Numérotation éditoriale des chapitres (chiffres romains, esprit Codex).
       Incrémenté à l'affichage de chaque en-tête de section → reste séquentiel
       même quand des sections sont masquées. */
    $chapN = 0;
    $roman = function (int $n): string {
        $map = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        return $map[$n] ?? (string) $n;
    };

    /* ---- Mini-convertisseur Markdown → HTML pour la synthèse IA ----------
       L'IA renvoie du markdown (# titres, **gras**, ---, listes). dompdf ne
       sait pas l'interpréter : on le transforme en blocs HTML qui se
       paginent proprement. Pas de dépendance externe (commonmark absent). */
    $mdToHtml = function (?string $md): string {
        if ($md === null || trim($md) === '') return '';
        $md = str_replace(["\r\n", "\r"], "\n", trim($md));

        // Mise en forme inline, appliquée APRÈS échappement (anti-injection).
        $inline = function (string $text): string {
            $t = e($text);
            $t = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $t);
            $t = preg_replace('/(?<!\*)\*(?!\s)(.+?)(?<!\s)\*(?!\*)/s', '<em>$1</em>', $t);
            $t = preg_replace('/(?<![\w*])_(?!\s)(.+?)(?<!\s)_(?![\w*])/s', '<em>$1</em>', $t);
            return $t;
        };

        $lines = explode("\n", $md);
        $html  = '';
        $para  = [];
        $list  = [];
        $flushPara = function () use (&$para, &$html, $inline) {
            if ($para) { $html .= '<p class="synth-p">' . $inline(implode(' ', $para)) . '</p>'; $para = []; }
        };
        $flushList = function () use (&$list, &$html, $inline) {
            if ($list) {
                $html .= '<ul class="synth-ul">';
                foreach ($list as $it) $html .= '<li>' . $inline($it) . '</li>';
                $html .= '</ul>';
                $list = [];
            }
        };

        foreach ($lines as $raw) {
            $line = trim($raw);
            if ($line === '') { $flushPara(); $flushList(); continue; }
            if (preg_match('/^(-{3,}|_{3,}|\*{3,})$/', $line)) {        // filet ---
                $flushPara(); $flushList();
                $html .= '<div class="synth-hr"></div>';
                continue;
            }
            if (preg_match('/^(#{1,6})\s+(.*)$/', $line, $m)) {          // titres #
                $flushPara(); $flushList();
                $lvl = min(strlen($m[1]), 3);
                $html .= '<div class="synth-h synth-h' . $lvl . '">' . $inline($m[2]) . '</div>';
                continue;
            }
            if (preg_match('/^\*\*(.+?)\*\*\s*:?\s*$/', $line, $m)) {     // ligne **gras** seule → sous-titre
                $flushPara(); $flushList();
                $html .= '<div class="synth-h synth-h3">' . $inline($m[1]) . '</div>';
                continue;
            }
            if (preg_match('/^[-*•]\s+(.*)$/', $line, $m)) {             // puces
                $flushPara();
                $list[] = $m[1];
                continue;
            }
            $flushList();
            $para[] = $line;
        }
        $flushPara(); $flushList();
        return $html;
    };
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>{{ $test->name }} — {{ $candidate }}</title>
<style>
    /* ── Polices embarquées (OFL) — Lora (titres) + Lato (corps) ──────────
       Remplacent les DejaVu bureautiques. Chemins absolus lus par DomPDF au
       rendu (puis mis en cache dans storage/fonts). Repli DejaVu conservé
       partout : si l'hôte ne peut pas charger les TTF, le rendu actuel
       reste intact (aucune régression). */
    {{-- embedFonts=false (repli déclenché par le contrôleur si le cache de polices
         est inaccessible sur l'hôte) → aucun @font-face : DomPDF utilise les DejaVu
         déjà mises en cache, le PDF sort toujours. --}}
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

    @page { margin: 132px 0 96px 0; }
    * { box-sizing: border-box; }
    html { background: #FFFFFF; }
    body {
        font-family: "Lato", "DejaVu Sans", sans-serif;
        background: #FFFFFF;
        color: {{ $ink }};
        font-size: 11px;
        line-height: 1.6;
        margin: 0;
    }
    .serif { font-family: "Lora", "DejaVu Serif", serif; }
    .px { padding-left: 50px; padding-right: 50px; }

    /* ── En-tête répété ───────────────────────────────────────────────── */
    .run-header { position: fixed; top: -112px; left: 0; right: 0; height: 74px; padding: 0 50px; }
    .run-header .mark {
        font-family: "Lora", "DejaVu Serif", serif; font-size: 13px; font-weight: bold;
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
        font-family: "Lora", "DejaVu Serif", serif; font-size: 17px; color: {{ $accent }};
        margin: 4px 0 0; padding: 0; letter-spacing: .2px;
    }
    .sec { margin-top: 26px; }
    .avoid-break { page-break-inside: avoid; }

    /* ── Cartes / blocs ───────────────────────────────────────────────── */
    .card { background: {{ $velin }}; border: 0.75px solid {{ $hair }}; border-radius: 10px; }
    .lead-gold { border-left: 4px solid {{ $primary }}; }

    /* ── Synthèse IA (markdown rendu, paginé sur plusieurs pages) ─────────── */
    .synth { border-left: 3px solid {{ $primary }}; padding: 2px 0 2px 20px; }
    .synth-p { font-size: 11.5px; line-height: 1.78; color: {{ $ink }};
        margin: 0 0 10px; text-align: justify; }
    .synth-h { font-family: "Lora", "DejaVu Serif", serif; color: {{ $accent }};
        margin: 15px 0 6px; line-height: 1.35; }
    .synth-h1 { font-size: 14px; font-weight: bold; }
    .synth-h2 { font-size: 12.5px; font-weight: bold; }
    .synth-h3 { font-size: 11.5px; font-weight: bold; }
    .synth-hr { border-top: 0.75px solid {{ $hair }}; margin: 12px 0; height: 0; }
    .synth-ul { margin: 4px 0 11px; padding-left: 17px; }
    .synth-ul li { font-size: 11.5px; line-height: 1.7; color: {{ $ink }}; margin-bottom: 4px; }
    .synth strong { color: {{ $accent }}; }

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
    /* Pastille de rang — centrage vertical via cellule de tableau (dompdf ne
       centre pas via line-height : le chiffre tombait au bas du cercle). */
    .job-rank { display: inline-block; width: 26px; height: 26px;
        border-radius: 13px; background: {{ $primary }}; }
    .job-rank table { width: 26px; height: 26px; border-collapse: collapse; }
    .job-rank td { padding: 0; text-align: center; vertical-align: middle; line-height: 1;
        color: {{ $parchment }}; font-weight: bold; font-size: 12px; font-family: "Lora", "DejaVu Serif", serif; }
    .job-sector { font-size: 8px; text-transform: uppercase; letter-spacing: 1.5px; color: {{ $inkSoft }};
        font-family: "DejaVu Sans Mono", monospace; }
    .job-title { font-family: "Lora", "DejaVu Serif", serif; font-size: 13px; font-weight: bold; color: {{ $accent }}; }
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
    .medallion { display: inline-block; width: 86px; height: 86px;
        border-radius: 43px; border: 3px solid {{ $primary }}; background: {{ $accent }}; }
    .medallion table { width: 80px; height: 80px; border-collapse: collapse; }
    .medallion td { padding: 0; text-align: center; vertical-align: middle; line-height: 1;
        color: {{ $parchment }}; font-family: "Lora", "DejaVu Serif", serif; font-weight: bold; font-size: 30px; }
    .medallion .pctsign { font-size: 14px; color: {{ $primary }}; }
    .medallion-cap { font-size: 7.5px; letter-spacing: 1.5px; text-transform: uppercase;
        color: #9C8A60; text-align: center; margin-top: 7px; font-family: "DejaVu Sans Mono", monospace; }
    .hero-kicker { font-size: 8px; letter-spacing: 3px; text-transform: uppercase;
        color: {{ $primary }}; font-weight: bold; font-family: "DejaVu Sans Mono", monospace; }
    .hero-label  { font-family: "Lora", "DejaVu Serif", serif; font-size: 22px; font-weight: bold;
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
    .fig-cap { font-family: "DejaVu Sans Mono", monospace; font-size: 7.5px; letter-spacing: 1.2px;
        text-transform: uppercase; color: {{ $inkSoft }}; text-align: center; margin-top: 4px; }
    .sub-val { font-family: "DejaVu Sans Mono", monospace; font-size: 10px; font-weight: bold;
        color: {{ $goldDark }}; text-align: right; }
    .sev-pill { display: inline-block; padding: 2px 8px; border-radius: 9px; font-size: 8px;
        font-weight: bold; color: {{ $parchment }}; font-family: "DejaVu Sans Mono", monospace; }

    /* ── Avertissement non-diagnostique ───────────────────────────────── */
    .disclaimer { background: {{ $stone }}; border-left: 3px solid {{ $secondary }};
        border-radius: 0 8px 8px 0; padding: 12px 16px; }
    .disclaimer .dtitle { font-family: "DejaVu Sans Mono", monospace; font-size: 8px;
        letter-spacing: 1.5px; text-transform: uppercase; color: {{ $secondary }}; font-weight: bold; }
    .disclaimer .dbody { font-size: 9.5px; color: {{ $ink }}; line-height: 1.55; margin-top: 4px; }

    /* ════════════════ RENDU D'EXCELLENCE — raffinements ════════════════
       Surcharges en fin de feuille (priorité à l'ordre CSS). Esprit Codex :
       chapitres en chiffres romains, filets or, sceau, barres affinées. */
    .sec { margin-top: 32px; }
    h2.section { font-size: 18px; margin-top: 6px; letter-spacing: .3px; line-height: 1.25; }
    .kicker { letter-spacing: 3.4px; }
    /* Chiffre romain de chapitre, en or serif, devant l'intitulé */
    .chap { font-family: "Lora", "DejaVu Serif", serif; font-weight: bold;
        font-size: 12.5px; color: {{ $primary }}; margin-right: 10px; letter-spacing: 0; }
    /* Filet de section : segment or épais + prolongement filiforme */
    .s-rule { width: 100%; border-collapse: collapse; margin: 11px 0 20px; }
    .s-rule td.g { width: 46px; border-top: 2px solid {{ $primary }}; font-size: 0; line-height: 0; }
    .s-rule td.h { border-top: 0.6px solid {{ $hair }}; font-size: 0; line-height: 0; }
    /* Barres de dimensions affinées */
    .track { height: 7px; border-radius: 4px; background: #DFD6BE; border: 0.5px solid {{ $hair }}; }
    .fill  { height: 7px; border-radius: 4px; }
    table.dims td { padding: 8px 0; }
    .dim-name { font-size: 11px; }
    .dim-score { color: {{ $accent }}; font-size: 11px; }
    /* Cartes & métiers — accent or à gauche */
    .card { border-radius: 12px; }
    .job  { border-left: 3px solid {{ $primary }}; padding: 15px 18px 15px 16px; }
    .hero { border-radius: 16px; }
    .medallion { border-width: 2px; }
    /* Ornement centré (couverture) */
    .ornament { text-align: center; color: {{ $primary }};
        font-family: "DejaVu Sans Mono", monospace; font-size: 9px; letter-spacing: 8px; }
    /* Sceau monogramme (couverture) */
    .seal { display: inline-block; width: 52px; height: 52px; border-radius: 26px;
        border: 1.5px solid {{ $primary }}; background: {{ $accent }}; }
    .seal table { width: 52px; height: 52px; border-collapse: collapse; }
    .seal td { padding: 0; text-align: center; vertical-align: middle; line-height: 1;
        font-family: "Lora", "DejaVu Serif", serif; font-weight: bold; font-size: 22px; color: {{ $primary }}; }
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
            {{-- filet or intérieur + sceau monogramme --}}
            <table style="width:100%; border-collapse:collapse; margin-bottom:24px;"><tr>
                <td style="vertical-align:middle;">
                    <div style="border-top:0.75px solid {{ $primary }}; width:54px;"></div>
                </td>
                <td style="vertical-align:middle; text-align:right; width:60px;">
                    <div class="seal"><table><tr><td>Q</td></tr></table></div>
                </td>
            </tr></table>

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

            <div style="height:20px;"></div>
            <div class="ornament" style="margin-bottom:20px;">✦&nbsp;&nbsp;◆&nbsp;&nbsp;✦</div>
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
    <p class="kicker"><span class="chap">{{ $roman(++$chapN) }}</span>Contexte</p>
    <h2 class="section serif">Profil du candidat</h2>
    <table class="s-rule"><tr><td class="g"></td><td class="h"></td></tr></table>
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
    <p class="kicker"><span class="chap">{{ $roman(++$chapN) }}</span>Votre résultat</p>
    <h2 class="section serif">Verdict de l'évaluation</h2>
    <table class="s-rule"><tr><td class="g"></td><td class="h"></td></tr></table>
    <table class="hero" style="width:100%; border-collapse:separate;">
        <tr>
            @if($headline['pct'] !== null)
            <td style="width:118px; vertical-align:middle; padding:20px 0 20px 22px;">
                <div class="medallion"><table><tr><td>{{ $headline['pct'] }}<span class="pctsign">%</span></td></tr></table></div>
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
    <p class="kicker"><span class="chap">{{ $roman(++$chapN) }}</span>Lecture du profil</p>
    <h2 class="section serif">Synthèse</h2>
    <table class="s-rule"><tr><td class="g"></td><td class="h"></td></tr></table>
    <div class="synth">{!! $mdToHtml($synthesis) !!}</div>
</div>
@endif

{{-- ═══════════════ FORCES & AXES DE PROGRESSION ═══════════════ --}}
@if($sections['strengths'] && (count($strengths) || count($growth)))
<div class="px avoid-break sec">
    <p class="kicker"><span class="chap">{{ $roman(++$chapN) }}</span>Leviers</p>
    <h2 class="section serif">Points forts &amp; axes de progression</h2>
    <table class="s-rule"><tr><td class="g"></td><td class="h"></td></tr></table>
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
<div class="px sec">
    <p class="kicker"><span class="chap">{{ $roman(++$chapN) }}</span>Mesures</p>
    <h2 class="section serif">Profil par dimension</h2>
    <table class="s-rule"><tr><td class="g"></td><td class="h"></td></tr></table>

    @if($radarUri)
    <table style="width:100%; border-collapse:collapse;"><tr><td class="avoid-break" style="text-align:center; padding:2px 0 16px;">
        <img src="{{ $radarUri }}" style="width:330px; height:auto;">
        <div class="fig-cap">Profil en un coup d'œil — score par dimension (sur 100)</div>
    </td></tr></table>
    <div class="sub-title" style="margin-bottom:2px;">Détail par dimension</div>
    @endif

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
    <p class="kicker"><span class="chap">{{ $roman(++$chapN) }}</span>Détail</p>
    <h2 class="section serif">Sous-échelles</h2>
    <table class="s-rule"><tr><td class="g"></td><td class="h"></td></tr></table>

    @if($quadrantUri)
    <table style="width:100%; border-collapse:collapse;"><tr><td class="avoid-break" style="text-align:center; padding:2px 0 14px;">
        <img src="{{ $quadrantUri }}" style="width:330px; height:auto;">
        <div class="fig-cap">Modèle de Karasek — tension perçue × marge de manœuvre</div>
    </td></tr></table>
    @endif

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

{{-- ═══════════════ AVERTISSEMENT NON-DIAGNOSTIQUE ═══════════════ --}}
@if($disclaimer)
<div class="px avoid-break sec">
    <table class="disclaimer" style="width:100%; border-collapse:separate;">
        <tr><td>
            <div class="dtitle">À lire — portée de ce bilan</div>
            <div class="dbody">{{ $disclaimer }}</div>
        </td></tr>
    </table>
</div>
@endif

{{-- ═══════════════ MÉTIERS À EXPLORER ═══════════════ --}}
@if($sections['jobs'] && count($jobs))
<div style="page-break-before: always;"></div>
<div class="px sec">
    <p class="kicker"><span class="chap">{{ $roman(++$chapN) }}</span>Orientation</p>
    <h2 class="section serif">{{ count($jobs) }} métiers à explorer</h2>
    <table class="s-rule"><tr><td class="g"></td><td class="h"></td></tr></table>

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
                    <div class="job-rank"><table><tr><td>{{ $i + 1 }}</td></tr></table></div>
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
