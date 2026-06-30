{{--
  ════════════════════════════════════════════════════════════════════════════
  PraxiQuest — Rapport de synthèse PDF « Consulting Navy »
  Direction artistique : fond encre ancienne #1C1408 en header/cover,
  corps blanc professionnel, palette or #A67520 / cramoisi #7B1515 / vert #3A6B48.
  Titres en Lora (serif), corps en Lato (sans-serif), données en DejaVu Sans Mono.
  Lora + Lato embarquées (resources/fonts, licence OFL) ; repli DejaVu conservé.
  Moteur : barryvdh/laravel-dompdf (CSS 2.1 → mise en page par <table>).
  AUCUN flexbox, AUCUN grid, AUCUN SVG, AUCUN pseudo-élément ::before/::after.

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
        'legal'   => 'Document confidentiel — usage personnel. Données traitées conformément au RGPD. '
            . "Outil d'auto-évaluation et de développement personnel : contenus générés par IA à titre informatif, "
            . "ne constituant pas un avis professionnel et ne remplaçant pas un psychologue, un médecin ou un coach.",
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
    $ink        = '#1C1408';   // texte principal (encre ancienne)
    $inkSoft    = '#6B5A3E';   // texte secondaire / labels
    $parchment  = '#F0E8D4';   // textes sur fond sombre (header, cover, hero)
    $velin      = '#FAF8F4';   // surface cards — neutre chaud
    $stone      = '#EDE8DE';   // fond tracks / élevé
    $goldDark   = '#7D5510';   // or brûlé
    $eagle      = '#3A6B48';   // vert Eagle Vision (succès / matching)
    $hair       = '#E2D8C8';   // filet discret chaud

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
                $html .= '<table style="width:100%;border-collapse:collapse;margin:4px 0 11px;">';
                foreach ($list as $it) {
                    $html .= '<tr>'
                        . '<td style="width:10px;vertical-align:top;padding-right:8px;padding-bottom:4px;font-size:12px;color:#A67520;font-weight:bold;line-height:1.7;">&#8250;</td>'
                        . '<td style="font-size:11px;line-height:1.7;color:#1C1408;padding-bottom:4px;">' . $inline($it) . '</td>'
                        . '</tr>';
                }
                $html .= '</table>';
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

    /* @page margin : top=header (72px fond sombre + 4px accent bar = 76px, zéro gap)
                      bottom=footer (64px + 20px espace = 84px) */
    @page { margin: 76px 0 84px 0; }
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
    .mono  { font-family: "DejaVu Sans Mono", monospace; }
    .px    { padding-left: 44px; padding-right: 44px; }

    /* ══════════════════════════════════════════════════════
       EN-TÊTE RÉPÉTÉ (running header — dompdf fixed)
       Fond encre ancienne, hauteur 72px + barre accent 4px
       ══════════════════════════════════════════════════════ */
    .run-header {
        position: fixed;
        top: -76px;
        left: 0; right: 0;
        height: 72px;
        background: {{ $accent }};
        padding: 0 44px;
    }
    .run-header table { width: 100%; height: 72px; border-collapse: collapse; }
    .run-header td { vertical-align: middle; padding: 0; }

    /* Logo PraxiQuest côté gauche */
    .rh-brand {
        font-family: "Lora", "DejaVu Serif", serif;
        font-size: 13px;
        font-weight: bold;
        color: #FFFFFF;
        letter-spacing: 0.3px;
    }
    .rh-brand-q { color: {{ $primary }}; }

    /* Infos test + candidat */
    .rh-info {
        font-size: 8px;
        color: #C8B48A;
        letter-spacing: 1px;
        text-transform: uppercase;
        margin-top: 3px;
    }

    /* Date côté droit */
    .rh-date {
        font-size: 9px;
        color: #8A7050;
        text-align: right;
    }

    /* Barre accent or sous le header — colle exactement sous run-header */
    .run-accent {
        position: fixed;
        top: -4px;
        left: 0; right: 0;
        height: 4px;
        background: {{ $primary }};
    }

    /* ══════════════════════════════════════════════════════
       PIED DE PAGE RÉPÉTÉ (running footer)
       ══════════════════════════════════════════════════════ */
    .run-footer {
        position: fixed;
        bottom: -84px;
        left: 0; right: 0;
        height: 68px;
        background: {{ $velin }};
        border-top: 2px solid {{ $primary }};
        padding: 10px 44px 0;
    }
    .run-footer table { width: 100%; border-collapse: collapse; }
    .run-footer td { vertical-align: top; padding: 0; }
    .rf-legal {
        font-size: 7.5px;
        color: #9A8870;
        line-height: 1.5;
    }
    .rf-brand {
        font-size: 8px;
        font-weight: bold;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: {{ $primary }};
        text-align: right;
    }
    .rf-date {
        font-size: 9px;
        color: {{ $ink }};
        font-weight: bold;
        text-align: right;
        margin-top: 2px;
    }

    /* ══════════════════════════════════════════════════════
       TITRES DE SECTION
       Filet or 2px sur 36px + filet fin #E2D8C8 qui s'étend
       Simulé via tableau 2 cellules (dompdf-safe)
       ══════════════════════════════════════════════════════ */
    .kicker {
        font-size: 8.5px;
        letter-spacing: 2.5px;
        text-transform: uppercase;
        color: {{ $ink }};
        font-weight: bold;
        margin: 0;
        font-family: "DejaVu Sans Mono", monospace;
    }
    /* Filet de section : segment or épais + prolongement filiforme */
    .s-rule { width: 100%; border-collapse: collapse; margin: 10px 0 18px; }
    .s-rule td.g { width: 36px; border-top: 2px solid {{ $primary }}; font-size: 0; line-height: 0; padding: 0; }
    .s-rule td.h { border-top: 0.75px solid {{ $hair }}; font-size: 0; line-height: 0; padding: 0; }

    .sec { margin-top: 28px; }
    .avoid-break { page-break-inside: avoid; }

    /* ══════════════════════════════════════════════════════
       SCORE HERO — bloc résultat-phare
       border-left 4px encre + fond velin + cercle de score
       via tableau (pas de SVG)
       ══════════════════════════════════════════════════════ */
    .score-hero {
        border-left: 4px solid {{ $accent }};
        background: {{ $velin }};
        border-top: 0.75px solid {{ $hair }};
        border-right: 0.75px solid {{ $hair }};
        border-bottom: 0.75px solid {{ $hair }};
        padding: 0;
        margin-bottom: 24px;
    }
    .score-hero table { width: 100%; border-collapse: collapse; }
    .score-hero td { vertical-align: middle; padding: 20px 20px; }

    /* Cercle de score : tableau centré, border-radius, bordure or */
    .score-circle {
        width: 72px;
        height: 72px;
        border-radius: 36px;
        border: 3px solid {{ $primary }};
        background: {{ $accent }};
    }
    .score-circle table { width: 66px; height: 66px; border-collapse: collapse; }
    .score-circle td {
        padding: 0;
        text-align: center;
        vertical-align: middle;
        line-height: 1;
    }
    .score-number {
        font-family: "Lora", "DejaVu Serif", serif;
        font-size: 22px;
        font-weight: bold;
        color: {{ $parchment }};
    }
    .score-denom {
        font-size: 9px;
        color: {{ $primary }};
        font-weight: bold;
        font-family: "DejaVu Sans Mono", monospace;
    }

    /* Badge profil — fond cramoisi */
    .profile-badge {
        background: {{ $secondary }};
        color: #F5DDB0;
        font-size: 9px;
        font-weight: bold;
        letter-spacing: 1px;
        text-transform: uppercase;
        padding: 3px 10px;
        margin-bottom: 6px;
        font-family: "DejaVu Sans Mono", monospace;
    }
    .hero-label {
        font-family: "Lora", "DejaVu Serif", serif;
        font-size: 17px;
        font-weight: bold;
        color: {{ $ink }};
        line-height: 1.25;
        margin-bottom: 4px;
    }
    .hero-score-line {
        font-family: "DejaVu Sans Mono", monospace;
        font-size: 9.5px;
        color: {{ $inkSoft }};
        margin-top: 4px;
    }
    .hero-score-line b { color: {{ $goldDark }}; }
    .hero-phrase {
        font-size: 11px;
        color: {{ $inkSoft }};
        line-height: 1.6;
        margin-top: 6px;
    }

    /* ══════════════════════════════════════════════════════
       DIMENSIONS — barres de scores
       Nom 31% | barre 55% | score 14%
       Track fond #EDE8DE, hauteur 7px, radius 3px
       Fill fond or uniforme #A67520 (pas de gradient dompdf)
       ══════════════════════════════════════════════════════ */
    table.dims { width: 100%; border-collapse: collapse; }
    table.dims td { padding: 7px 0; vertical-align: middle; }
    .dim-name  { font-size: 11px; color: {{ $ink }}; }
    .dim-level { font-size: 8px; color: {{ $inkSoft }}; text-transform: uppercase;
                 letter-spacing: 0.8px; font-family: "DejaVu Sans Mono", monospace; }
    .track { background: {{ $stone }}; height: 7px; border-radius: 3px; width: 100%; }
    .fill  { height: 7px; border-radius: 3px; background: {{ $primary }}; }
    .dim-score { font-family: "DejaVu Sans Mono", monospace; font-size: 11px; font-weight: bold;
                 text-align: right; color: {{ $goldDark }}; }

    /* ══════════════════════════════════════════════════════
       SYNTHÈSE IA — bloc border-left or + fond velin
       ══════════════════════════════════════════════════════ */
    .synth-box {
        border-left: 3px solid {{ $primary }};
        background: {{ $velin }};
        padding: 14px 18px;
    }
    .synth-p  { font-size: 11px; line-height: 1.75; color: {{ $ink }};
                margin: 0 0 10px; text-align: justify; }
    .synth-h  { font-family: "Lora", "DejaVu Serif", serif; color: {{ $accent }};
                margin: 14px 0 6px; line-height: 1.35; }
    .synth-h1 { font-size: 14px; font-weight: bold; }
    .synth-h2 { font-size: 12.5px; font-weight: bold; }
    .synth-h3 { font-size: 11.5px; font-weight: bold; }
    .synth-hr { border-top: 0.75px solid {{ $hair }}; margin: 12px 0; height: 0; }
    /* synth-ul supprimé : rendu via table (dompdf-safe) */
    .synth strong { color: {{ $accent }}; }
    .ai-badge {
        font-family: "DejaVu Sans Mono", monospace;
        font-size: 7.5px;
        font-weight: bold;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: {{ $inkSoft }};
        margin-top: 10px;
    }

    /* ══════════════════════════════════════════════════════
       POINTS FORTS / AXES DE DÉVELOPPEMENT
       Deux colonnes via <table>
       Bullets: cercles colorés avec chiffre/lettre centrés
       via cellule de tableau
       ══════════════════════════════════════════════════════ */
    .bullet-wrap {
        width: 20px;
        height: 20px;
        border-radius: 10px;
    }
    .bullet-wrap table { width: 20px; height: 20px; border-collapse: collapse; }
    .bullet-wrap td {
        padding: 0;
        text-align: center;
        vertical-align: middle;
        line-height: 1;
        font-size: 9px;
        font-weight: bold;
        font-family: "DejaVu Sans Mono", monospace;
        color: #FFFFFF;
    }
    .bullet-strength { background: {{ $eagle }}; }
    .bullet-dev      { background: {{ $secondary }}; }
    .bullet-dev td   { color: #F5DDB0; }

    .points-list { width: 100%; border-collapse: collapse; }
    .points-list td { padding: 0 0 9px 0; vertical-align: top; }
    .point-label { font-size: 11px; line-height: 1.5; color: {{ $ink }}; padding-left: 10px; }
    .point-label strong { color: {{ $accent }}; }

    /* Séparateur entre points forts et axes */
    .pts-separator { border-top: 0.75px solid {{ $hair }}; margin: 10px 0 12px; }

    /* ══════════════════════════════════════════════════════
       MÉTIERS — cards avec border-left or
       ══════════════════════════════════════════════════════ */
    table.kv { width: 100%; border-collapse: collapse; }
    table.kv td { padding: 8px 14px; vertical-align: top; border-bottom: 0.75px solid {{ $hair }}; }
    table.kv td.k { width: 34%; color: {{ $inkSoft }}; font-size: 8.5px; text-transform: uppercase;
                    letter-spacing: 1.2px; font-family: "DejaVu Sans Mono", monospace; }
    table.kv td.v { font-size: 11.5px; font-weight: bold; color: {{ $accent }}; }

    .job-card {
        border-left: 3px solid {{ $primary }};
        background: {{ $velin }};
        border-top: 0.75px solid {{ $hair }};
        border-right: 0.75px solid {{ $hair }};
        border-bottom: 0.75px solid {{ $hair }};
        margin-bottom: 11px;
        padding: 0;
    }
    .job-card table { width: 100%; border-collapse: collapse; }
    .job-card td { vertical-align: top; padding: 13px 14px; }

    /* Pastille rang : cercle or avec chiffre centré via cellule */
    .job-rank {
        width: 26px;
        height: 26px;
        border-radius: 13px;
        background: {{ $primary }};
    }
    .job-rank table { width: 26px; height: 26px; border-collapse: collapse; }
    .job-rank td {
        padding: 0;
        text-align: center;
        vertical-align: middle;
        line-height: 1;
        color: {{ $accent }};
        font-weight: bold;
        font-size: 12px;
        font-family: "Lora", "DejaVu Serif", serif;
    }
    .job-sector {
        font-size: 8px;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: {{ $inkSoft }};
        font-family: "DejaVu Sans Mono", monospace;
        margin-bottom: 3px;
    }
    .job-title {
        font-family: "Lora", "DejaVu Serif", serif;
        font-size: 13px;
        font-weight: bold;
        color: {{ $accent }};
    }
    .job-why  { font-size: 10.5px; color: {{ $ink }}; margin-top: 5px; }
    .job-next { font-size: 10px; color: {{ $secondary }}; margin-top: 6px; font-weight: bold; }
    .fit-pill {
        font-family: "DejaVu Sans Mono", monospace;
        font-size: 11px;
        font-weight: bold;
        color: #FFFFFF;
        padding: 4px 10px;
    }

    /* ══════════════════════════════════════════════════════
       SOUS-ÉCHELLES groupées
       ══════════════════════════════════════════════════════ */
    .sub-group { margin-top: 14px; }
    .sub-title { font-family: "DejaVu Sans Mono", monospace; font-size: 8.5px; font-weight: bold;
                 letter-spacing: 1.5px; text-transform: uppercase; color: {{ $goldDark }}; margin-bottom: 6px; }
    .fig-cap   { font-family: "DejaVu Sans Mono", monospace; font-size: 7.5px; letter-spacing: 1.2px;
                 text-transform: uppercase; color: {{ $inkSoft }}; text-align: center; margin-top: 4px; }
    .sub-val   { font-family: "DejaVu Sans Mono", monospace; font-size: 10px; font-weight: bold;
                 color: {{ $goldDark }}; text-align: right; }
    .sev-pill  { padding: 2px 8px; font-size: 8px;
                 font-weight: bold; color: {{ $parchment }}; font-family: "DejaVu Sans Mono", monospace; }

    /* ══════════════════════════════════════════════════════
       AVERTISSEMENT NON-DIAGNOSTIQUE
       ══════════════════════════════════════════════════════ */
    .disclaimer {
        background: {{ $stone }};
        border-left: 3px solid {{ $secondary }};
        padding: 12px 16px;
    }
    .disclaimer .dtitle { font-family: "DejaVu Sans Mono", monospace; font-size: 8px;
                          letter-spacing: 1.5px; text-transform: uppercase; color: {{ $secondary }}; font-weight: bold; }
    .disclaimer .dbody  { font-size: 9.5px; color: {{ $ink }}; line-height: 1.55; margin-top: 4px; }

    /* ══════════════════════════════════════════════════════
       PAGE DE COUVERTURE
       Bloc encre ancienne pleine largeur
       ══════════════════════════════════════════════════════ */
    .cover-block {
        background: {{ $accent }};
        padding: 0;
    }
    .cover-block table { width: 100%; border-collapse: collapse; }
    .cover-block td { padding: 0; }

    /* Barre accent or sous le bloc cover */
    .cover-accent { height: 4px; background: {{ $primary }}; }

    /* Chip de score inline dans hero */
    .code-chip {
        margin-left: 8px;
        padding: 2px 10px;
        background: {{ $primary }};
        color: {{ $accent }};
        font-size: 11px;
        font-weight: bold;
        font-family: "DejaVu Sans Mono", monospace;
        letter-spacing: 2px;
    }

    /* Chip points forts / axes (section strengths, fallback) */
    .chip { padding: 4px 10px; margin: 0 5px 6px 0;
            font-size: 10px; border: 0.5px solid; }
    .chip-up   { background: #EAF1E9; color: {{ $eagle }};     border-color: #BCD3BE; }
    .chip-grow { background: #F3E4DF; color: {{ $secondary }}; border-color: #E0BFB6; }

    /* Bloc coordonnées */
    .contact-block { background: {{ $stone }}; padding: 18px 22px; }
</style>
</head>
<body>

{{-- ═══════════════════════════════════════════════════════
     EN-TÊTE RÉPÉTÉ — fond encre ancienne #1C1408
     Gauche : PraxiQuest + test + candidat
     Droite : date du rapport
     Dessous : barre accent 4px or
     ═══════════════════════════════════════════════════════ --}}
<div class="run-header">
    <table>
        <tr>
            <td style="vertical-align:middle;">
                <div class="rh-brand">Praxi<span class="rh-brand-q">Quest</span></div>
                <div class="rh-info">{{ $test->name }} &middot; {{ $candidate }}</div>
            </td>
            <td style="vertical-align:middle; text-align:right;">
                <div class="rh-date">{{ $dateDone->format('d/m/Y') }}</div>
            </td>
        </tr>
    </table>
</div>
<div class="run-accent"></div>

{{-- ═══════════════════════════════════════════════════════
     PIED DE PAGE RÉPÉTÉ
     ═══════════════════════════════════════════════════════ --}}
@if($sections['footer'])
<div class="run-footer">
    <table>
        <tr>
            <td style="width:68%;">
                <div class="rf-legal">{{ $org['legal'] }}</div>
            </td>
            <td style="width:32%; text-align:right;">
                <div class="rf-brand">{{ $brand['name'] }}</div>
                <div class="rf-date">{{ $dateDone->format('d/m/Y') }}</div>
            </td>
        </tr>
    </table>
</div>
@endif

{{-- Numérotation de page (méthode DomPDF native) --}}
<script type="text/php">
    if (isset($pdf)) {
        $font = $fontMetrics->get_font("DejaVu Sans Mono", "normal");
        $pdf->page_text(500, 812, "— {PAGE_NUM} / {PAGE_COUNT} —", $font, 7.5, array(0.42,0.35,0.24));
    }
</script>

{{-- ═══════════════════════════════════════════════════════
     PAGE DE COUVERTURE
     Grand bloc sombre encre ancienne
     Gauche : kicker + nom candidat + test + méta-données
     Droite : logo PraxiQuest + date
     ═══════════════════════════════════════════════════════ --}}
@if($sections['cover'])
{{-- ══════════════════════════════════════════════════════
     COVER — hauteur naturelle (padding 54/50px), pas de height forcée
     dompdf : height ignoré sur <table> mais respecté sur <div> —
     ici on laisse le contenu définir la hauteur naturellement.
     ══════════════════════════════════════════════════════ --}}
<div style="background:{{ $accent }};">
<table style="width:100%; border-collapse:collapse;">
    <tr>
        {{-- Gauche : kicker + nom + test + méta --}}
        <td style="vertical-align:middle; padding:52px 32px 48px 44px; width:60%;">

            {{-- Kicker : RAPPORT D'ÉVALUATION --}}
            <div style="font-size:7.5px; font-weight:bold; letter-spacing:2.5px; text-transform:uppercase;
                        color:{{ $primary }}; font-family:'DejaVu Sans Mono',monospace; margin-bottom:14px;">
                RAPPORT D'&Eacute;VALUATION
            </div>
            {{-- Filet court or --}}
            <div style="height:2px; width:44px; background:{{ $primary }}; margin-bottom:20px;"></div>

            {{-- Nom du candidat --}}
            <div style="font-family:'Lora','DejaVu Serif',serif; font-size:34px; font-weight:bold;
                        color:#FFFFFF; line-height:1.1; margin-bottom:10px;">
                {{ $candidate }}
            </div>

            {{-- Nom du test --}}
            <div style="font-family:'Lora','DejaVu Serif',serif; font-size:14px; color:{{ $primary }};
                        font-style:italic; line-height:1.4; margin-bottom:28px;">
                {{ $test->name }}
            </div>

            {{-- Méta-données : statut | ancienneté | date | pistes --}}
            <table style="border-collapse:collapse; margin-bottom:0;">
                <tr>
                    @if($statusLabel)
                    <td style="padding-right:24px; vertical-align:top; padding-bottom:0;">
                        <div style="font-size:6.5px; font-weight:bold; letter-spacing:1.5px;
                                    text-transform:uppercase; color:{{ $primary }};
                                    font-family:'DejaVu Sans Mono',monospace; margin-bottom:2px;">Statut</div>
                        <div style="font-size:11px; font-weight:bold; color:#E8D8B8; font-family:'Lora','DejaVu Serif',serif;">{{ $statusLabel }}</div>
                    </td>
                    @endif
                    @if($seniority)
                    <td style="padding-right:24px; vertical-align:top; padding-bottom:0;">
                        <div style="font-size:6.5px; font-weight:bold; letter-spacing:1.5px;
                                    text-transform:uppercase; color:{{ $primary }};
                                    font-family:'DejaVu Sans Mono',monospace; margin-bottom:2px;">Anciennet&eacute;</div>
                        <div style="font-size:11px; font-weight:bold; color:#E8D8B8; font-family:'Lora','DejaVu Serif',serif;">{{ $seniority }}</div>
                    </td>
                    @endif
                    <td style="padding-right:24px; vertical-align:top; padding-bottom:0;">
                        <div style="font-size:6.5px; font-weight:bold; letter-spacing:1.5px;
                                    text-transform:uppercase; color:{{ $primary }};
                                    font-family:'DejaVu Sans Mono',monospace; margin-bottom:2px;">Date</div>
                        <div style="font-size:11px; font-weight:bold; color:#E8D8B8; font-family:'Lora','DejaVu Serif',serif;">{{ $dateDone->format('d/m/Y') }}</div>
                    </td>
                    @if(count($jobs))
                    <td style="vertical-align:top; padding-bottom:0;">
                        <div style="font-size:6.5px; font-weight:bold; letter-spacing:1.5px;
                                    text-transform:uppercase; color:{{ $primary }};
                                    font-family:'DejaVu Sans Mono',monospace; margin-bottom:2px;">Pistes m&eacute;tiers</div>
                        <div style="font-size:11px; font-weight:bold; color:#E8D8B8; font-family:'Lora','DejaVu Serif',serif;">{{ count($jobs) }} voies explor&eacute;es</div>
                    </td>
                    @endif
                </tr>
            </table>
        </td>

        {{-- Droite : médaillon + PraxiQuest + score/profil --}}
        <td style="vertical-align:middle; padding:52px 44px 48px 28px; width:40%; text-align:center;
                   border-left:1px solid #2A1E0E;">

            {{-- Logo ou médaillon Q --}}
            @if(!empty($brand['logo']))
                <img src="{{ $brand['logo'] }}" alt="{{ $brand['name'] }}"
                     style="max-height:52px; margin-bottom:12px;">
            @else
                <table style="border-collapse:collapse; margin:0 auto 10px;">
                    <tr>
                        <td style="width:56px; height:56px; border-radius:28px;
                                   background:{{ $primary }}; text-align:center;
                                   vertical-align:middle; padding:0;">
                            <span style="font-family:'Lora','DejaVu Serif',serif; font-size:26px;
                                         font-weight:bold; color:{{ $accent }};">Q</span>
                        </td>
                    </tr>
                </table>
                <div style="font-family:'Lora','DejaVu Serif',serif; font-size:16px; font-weight:bold;
                             color:#FFFFFF; letter-spacing:.3px; margin-bottom:4px;">
                    Praxi<span style="color:{{ $primary }};">Quest</span>
                </div>
            @endif

            <div style="font-size:8px; color:#6A5838; letter-spacing:1.2px;
                        font-family:'DejaVu Sans Mono',monospace; margin-bottom:24px;">
                &Eacute;MIS LE {{ strtoupper($dateDone->format('d/m/Y')) }}
            </div>

            {{-- Score ou profil en grand --}}
            @if($headline)
            <div style="background:#140E06; padding:16px 20px 14px; display:block;">
                @if($headline['pct'] !== null)
                <div style="font-family:'Lora','DejaVu Serif',serif; font-size:48px; font-weight:bold;
                             color:{{ $primary }}; line-height:1;">
                    {{ $headline['pct'] }}
                </div>
                <div style="font-size:7px; color:#7A6040; letter-spacing:2px;
                             text-transform:uppercase; font-family:'DejaVu Sans Mono',monospace; margin-top:4px;">
                    Score &mdash; /100
                </div>
                @else
                <div style="font-family:'Lora','DejaVu Serif',serif; font-size:24px; font-weight:bold;
                             color:#FFFFFF; line-height:1.2; margin-bottom:6px;">
                    {{ $headline['label'] ?? 'Profil &eacute;tabli' }}
                </div>
                @if($headline['code'])
                <div style="font-family:'DejaVu Sans Mono',monospace; font-size:8px; font-weight:bold;
                             letter-spacing:2px; text-transform:uppercase; color:{{ $primary }};">
                    {{ $headline['code'] }}
                </div>
                @endif
                @endif
            </div>
            @endif

        </td>
    </tr>
</table>
</div>{{-- fin div cover --}}

{{-- Barre accent or pleine largeur --}}
<div style="height:4px; background:{{ $primary }};"></div>

{{-- Bandeau de synthèse : 3 KPIs sur fond velin --}}
<table style="width:100%; border-collapse:collapse; background:{{ $velin }};">
    <tr>
        {{-- KPI 1 : dimensions (si disponible), sinon : rapport complet --}}
        <td style="padding:18px 28px; vertical-align:middle;
                   border-right:1px solid {{ $hair }}; width:33%;">
            @if(count($dimensions))
            <div style="font-family:'Lora','DejaVu Serif',serif; font-size:28px; font-weight:bold;
                         color:{{ $ink }}; line-height:1; margin-bottom:3px;">
                {{ count($dimensions) }}
            </div>
            <div style="font-size:7px; text-transform:uppercase; letter-spacing:1.5px;
                         color:{{ $goldDark }}; font-family:'DejaVu Sans Mono',monospace;">
                Dimension{{ count($dimensions) > 1 ? 's' : '' }} analys&eacute;e{{ count($dimensions) > 1 ? 's' : '' }}
            </div>
            @else
            <div style="font-size:7px; text-transform:uppercase; letter-spacing:1.5px;
                         color:{{ $goldDark }}; font-family:'DejaVu Sans Mono',monospace; margin-bottom:3px;">
                Rapport
            </div>
            <div style="font-family:'Lora','DejaVu Serif',serif; font-size:13px; font-weight:bold;
                         color:{{ $ink }};">
                Complet
            </div>
            @endif
        </td>

        {{-- KPI 2 : score ou profil --}}
        <td style="padding:18px 28px; vertical-align:middle;
                   border-right:1px solid {{ $hair }}; width:33%;">
            @if($headline)
                @if($headline['pct'] !== null)
                <div style="font-family:'Lora','DejaVu Serif',serif; font-size:28px; font-weight:bold;
                             color:{{ $ink }}; line-height:1; margin-bottom:3px;">
                    {{ $headline['pct'] }}<span style="font-size:14px; color:{{ $inkSoft }};">/100</span>
                </div>
                <div style="font-size:7px; text-transform:uppercase; letter-spacing:1.5px;
                             color:{{ $goldDark }}; font-family:'DejaVu Sans Mono',monospace;">
                    Score global
                </div>
                @elseif($headline['code'])
                <div style="font-family:'Lora','DejaVu Serif',serif; font-size:18px; font-weight:bold;
                             color:{{ $ink }}; line-height:1.2; margin-bottom:3px;">
                    {{ $headline['code'] }}
                </div>
                <div style="font-size:7px; text-transform:uppercase; letter-spacing:1.5px;
                             color:{{ $goldDark }}; font-family:'DejaVu Sans Mono',monospace;">
                    Profil identifi&eacute;
                </div>
                @else
                <div style="font-family:'Lora','DejaVu Serif',serif; font-size:18px; font-weight:bold;
                             color:{{ $ink }}; line-height:1.2; margin-bottom:3px;">
                    {{ $headline['label'] ?? '—' }}
                </div>
                <div style="font-size:7px; text-transform:uppercase; letter-spacing:1.5px;
                             color:{{ $goldDark }}; font-family:'DejaVu Sans Mono',monospace;">
                    R&eacute;sultat
                </div>
                @endif
            @else
            <div style="font-size:7px; text-transform:uppercase; letter-spacing:1.5px;
                         color:{{ $goldDark }}; font-family:'DejaVu Sans Mono',monospace; margin-bottom:3px;">
                &Eacute;valuation
            </div>
            <div style="font-family:'Lora','DejaVu Serif',serif; font-size:13px; font-weight:bold;
                         color:{{ $ink }};">
                Compl&egrave;te
            </div>
            @endif
        </td>

        {{-- KPI 3 : pistes métiers --}}
        <td style="padding:18px 28px; vertical-align:middle; width:34%;">
            <div style="font-family:'Lora','DejaVu Serif',serif; font-size:28px; font-weight:bold;
                         color:{{ $ink }}; line-height:1; margin-bottom:3px;">
                {{ count($jobs) ?: '—' }}
            </div>
            <div style="font-size:7px; text-transform:uppercase; letter-spacing:1.5px;
                         color:{{ $goldDark }}; font-family:'DejaVu Sans Mono',monospace;">
                Piste{{ count($jobs) > 1 ? 's' : '' }} m&eacute;tier{{ count($jobs) > 1 ? 's' : '' }} explor&eacute;e{{ count($jobs) > 1 ? 's' : '' }}
            </div>
        </td>
    </tr>
</table>
{{-- Pas de page-break : la cover coule sur la page 1, le contenu commence juste en dessous --}}
@endif

{{-- ═══════════════════════════════════════════════════════
     PROFIL DU CANDIDAT
     ═══════════════════════════════════════════════════════ --}}
@if($sections['profile'] && $profile)
<div class="px avoid-break sec">
    <div class="kicker">{{ $roman(++$chapN) }}. Contexte</div>
    <table class="s-rule"><tr><td class="g"></td><td class="h"></td></tr></table>
    <table class="kv">
        <tr><td class="k">Nom</td><td class="v">{{ $candidate }}</td></tr>
        @if($statusLabel)<tr><td class="k">Statut</td><td class="v">{{ $statusLabel }}</td></tr>@endif
        @if($seniority)<tr><td class="k">Anciennet&eacute;</td><td class="v">{{ $seniority }}</td></tr>@endif
        @if($profile->current_role)<tr><td class="k">Poste actuel</td><td class="v">{{ $profile->current_role }}</td></tr>@endif
        @if($profile->industry)<tr><td class="k">Secteur</td><td class="v">{{ $profile->industry }}</td></tr>@endif
        @if($profile->cv_original_name)<tr><td class="k">CV fourni</td><td class="v">{{ $profile->cv_original_name }}</td></tr>@endif
        <tr><td class="k">&Eacute;valuation</td><td class="v">{{ $test->name }}</td></tr>
    </table>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════
     RÉSULTAT-PHARE — Verdict / Score hero
     Cercle de score via tableau + badge profil cramoisi
     ═══════════════════════════════════════════════════════ --}}
@if($headline)
<div class="px sec avoid-break">
    <div class="kicker">{{ $roman(++$chapN) }}. Votre r&eacute;sultat</div>
    <table class="s-rule"><tr><td class="g"></td><td class="h"></td></tr></table>
    <div class="score-hero">
        <table>
            <tr>
                @if($headline['pct'] !== null)
                <td style="width:110px; vertical-align:middle; padding:20px 16px 20px 20px;">
                    <div class="score-circle">
                        <table>
                            <tr>
                                <td>
                                    <div class="score-number">{{ $headline['pct'] }}</div>
                                    <div class="score-denom">/100</div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
                @endif
                <td style="vertical-align:middle; padding:20px 20px;">
                    @if($headline['code'])
                        <table style="border-collapse:collapse;margin-bottom:6px;">
                            <tr><td class="profile-badge">&#9670; Profil&nbsp;: {{ $headline['code'] }}</td></tr>
                        </table>
                    @endif
                    <div class="hero-label">
                        {{ $headline['label'] ?? 'Profil établi' }}
                    </div>
                    @if($headline['score'] !== null && $headline['score_max'])
                        <div class="hero-score-line">
                            Score global&nbsp;&nbsp;<b>{{ $headline['score'] }}</b> / {{ $headline['score_max'] }}
                        </div>
                    @endif
                    @if($headline['phrase'])
                        <div class="hero-phrase">{{ $headline['phrase'] }}</div>
                    @endif
                </td>
            </tr>
        </table>
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════
     DIMENSIONS — barres de score
     Nom 31% | barre 55% | score 14%
     ═══════════════════════════════════════════════════════ --}}
@if($sections['dimensions'] && count($dimensions))
<div class="px sec">
    <div class="kicker">{{ $roman(++$chapN) }}. Profil dimensionnel</div>
    <table class="s-rule"><tr><td class="g"></td><td class="h"></td></tr></table>

    @if($radarUri)
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td class="avoid-break" style="text-align:center; padding:2px 0 16px;">
                <img src="{{ $radarUri }}" style="width:330px; height:auto;">
                <div class="fig-cap">Profil en un coup d'oeil — score par dimension (sur 100)</div>
            </td>
        </tr>
    </table>
    <div class="sub-title" style="margin-bottom:4px;">D&eacute;tail par dimension</div>
    @endif

    <table class="dims">
        @foreach($dimensions as $dim)
            @php $sc = max(0, min(100, (int) $dim['pct'])); @endphp
            <tr>
                <td style="width:31%;">
                    <div class="dim-name">{{ $dim['name'] }}</div>
                    @if(!empty($dim['level']))<div class="dim-level">{{ $dim['level'] }}</div>@endif
                </td>
                <td style="width:55%; padding-left:12px; padding-right:12px;">
                    <div class="track"><div class="fill" style="width:{{ $sc }}%; background:{{ $fitColor($sc) }};"></div></div>
                </td>
                <td style="width:14%;">
                    <div class="dim-score">{{ $sc }}</div>
                </td>
            </tr>
        @endforeach
    </table>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════
     SYNTHÈSE IA — bloc or à gauche + fond velin
     ═══════════════════════════════════════════════════════ --}}
@if($sections['synthesis'] && $synthesis)
<div class="px sec">
    <div class="kicker">{{ $roman(++$chapN) }}. Synth&egrave;se analytique</div>
    <table class="s-rule"><tr><td class="g"></td><td class="h"></td></tr></table>
    <div class="synth-box">
        <div>{!! $mdToHtml($synthesis) !!}</div>
        <div class="ai-badge">&#9679; Analyse g&eacute;n&eacute;r&eacute;e par {{ $brand['name'] }} IA</div>
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════
     POINTS FORTS & AXES DE DÉVELOPPEMENT
     Deux colonnes via <table> — bullets cercles colorés
     ═══════════════════════════════════════════════════════ --}}
@if($sections['strengths'] && (count($strengths) || count($growth)))
<div class="px sec avoid-break">
    <div class="kicker">{{ $roman(++$chapN) }}. Leviers de d&eacute;veloppement</div>
    <table class="s-rule"><tr><td class="g"></td><td class="h"></td></tr></table>
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            @if(count($strengths))
            <td style="width:50%; vertical-align:top; padding-right:14px;">
                <div style="font-size:8px; font-weight:bold; letter-spacing:2px; text-transform:uppercase;
                            color:{{ $eagle }}; font-family:'DejaVu Sans Mono',monospace; margin-bottom:11px;">
                    Points forts identifi&eacute;s
                </div>
                <table class="points-list">
                    @foreach($strengths as $i => $s)
                    <tr>
                        <td style="width:20px; vertical-align:top; padding-bottom:9px; padding-right:0;">
                            <div class="bullet-wrap bullet-strength">
                                <table><tr><td>{{ $i + 1 }}</td></tr></table>
                            </div>
                        </td>
                        <td class="point-label">
                            {{ is_array($s) ? ($s['label'] ?? $s['name'] ?? reset($s)) : $s }}
                        </td>
                    </tr>
                    @endforeach
                </table>
            </td>
            @endif
            @if(count($growth))
            <td style="width:50%; vertical-align:top; padding-left:14px; border-left:0.75px solid {{ $hair }};">
                <div style="font-size:8px; font-weight:bold; letter-spacing:2px; text-transform:uppercase;
                            color:{{ $secondary }}; font-family:'DejaVu Sans Mono',monospace; margin-bottom:11px;">
                    Axes de d&eacute;veloppement
                </div>
                <table class="points-list">
                    @foreach($growth as $i => $g)
                    <tr>
                        <td style="width:20px; vertical-align:top; padding-bottom:9px; padding-right:0;">
                            <div class="bullet-wrap bullet-dev">
                                <table><tr><td>{{ chr(65 + $i) }}</td></tr></table>
                            </div>
                        </td>
                        <td class="point-label">
                            {{ is_array($g) ? ($g['label'] ?? $g['name'] ?? reset($g)) : $g }}
                        </td>
                    </tr>
                    @endforeach
                </table>
            </td>
            @endif
        </tr>
    </table>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════
     SOUS-ÉCHELLES (Karasek, MBI, facettes…)
     ═══════════════════════════════════════════════════════ --}}
@if($sections['dimensions'] && count($subscales))
<div class="px avoid-break sec">
    <div class="kicker">{{ $roman(++$chapN) }}. Sous-&eacute;chelles</div>
    <table class="s-rule"><tr><td class="g"></td><td class="h"></td></tr></table>

    @if($quadrantUri)
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td class="avoid-break" style="text-align:center; padding:2px 0 14px;">
                <img src="{{ $quadrantUri }}" style="width:330px; height:auto;">
                <div class="fig-cap">Mod&egrave;le de Karasek — tension per&ccedil;ue &times; marge de manoeuvre</div>
            </td>
        </tr>
    </table>
    @endif

    @foreach($subscales as $group)
        <div class="sub-group avoid-break">
            <div class="sub-title">{{ $group['title'] }}</div>
            <table class="dims">
                @foreach($group['items'] as $item)
                    @php $sc = $item['pct'] !== null ? max(0, min(100, (int) $item['pct'])) : null; @endphp
                    <tr>
                        <td style="width:34%;"><div class="dim-name">{{ $item['name'] }}</div></td>
                        <td style="width:42%; padding-left:12px; padding-right:12px;">
                            @if($sc !== null)
                                <div class="track"><div class="fill" style="width:{{ $sc }}%; background:{{ $primary }};"></div></div>
                            @endif
                        </td>
                        <td style="width:12%;">
                            <div class="sub-val">
                                @if($item['value'] !== null)
                                    {{ $item['value'] }}@if($item['max'])<span style="color:{{ $inkSoft }}; font-weight:normal;">/{{ $item['max'] }}</span>@endif
                                @endif
                            </div>
                        </td>
                        <td style="width:12%; text-align:right;">
                            @if(!empty($item['level']))
                            <table style="border-collapse:collapse;float:right;">
                                <tr><td class="sev-pill" style="background:{{ $goldDark }};">{{ $item['level'] }}</td></tr>
                            </table>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endforeach
</div>
@endif

{{-- ═══════════════════════════════════════════════════════
     AVERTISSEMENT NON-DIAGNOSTIQUE
     ═══════════════════════════════════════════════════════ --}}
@if($disclaimer)
<div class="px avoid-break sec">
    <table class="disclaimer" style="width:100%; border-collapse:separate;">
        <tr><td>
            <div class="dtitle">&Agrave; lire — port&eacute;e de ce bilan</div>
            <div class="dbody">{{ $disclaimer }}</div>
        </td></tr>
    </table>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════
     MÉTIERS À EXPLORER
     Cards avec border-left or, pastille rang, fit-pill
     ═══════════════════════════════════════════════════════ --}}
@if($sections['jobs'] && count($jobs))
<div style="page-break-before: always;"></div>
<div class="px sec">
    <div class="kicker">{{ $roman(++$chapN) }}. Orientation</div>
    <table class="s-rule"><tr><td class="g"></td><td class="h"></td></tr></table>
    <div style="font-family:'Lora','DejaVu Serif',serif; font-size:16px; font-weight:bold;
                color:{{ $accent }}; margin-bottom:16px;">
        {{ count($jobs) }} m&eacute;tiers &agrave; explorer
    </div>

    @foreach($jobs as $i => $job)
        @php
            $titre   = $job['titre'] ?? $job['title'] ?? '';
            $secteur = $job['secteur'] ?? $job['sector'] ?? '';
            $fit     = $job['fit_score'] ?? $job['fit'] ?? null;
            $why     = $job['pourquoi'] ?? $job['why'] ?? '';
            $next    = $job['prochaine_étape'] ?? $job['prochaine_etape'] ?? $job['next_step'] ?? null;
            $fitPct  = $fit !== null ? min(100, max(0, (int) $fit)) : null;
        @endphp
        <div class="job-card avoid-break">
            <table>
                <tr>
                    <td style="width:42px; vertical-align:top; padding:13px 6px 13px 14px;">
                        <div class="job-rank">
                            <table><tr><td>{{ $i + 1 }}</td></tr></table>
                        </div>
                    </td>
                    <td style="vertical-align:top; padding:13px 14px;">
                        <div class="job-sector">{{ $secteur }}</div>
                        <div class="job-title">{{ $titre }}</div>
                        @if($why)<div class="job-why">{{ $why }}</div>@endif
                        @if($next)<div class="job-next">&rarr; {{ $next }}</div>@endif
                    </td>
                    @if($fitPct !== null)
                    <td style="width:60px; text-align:right; vertical-align:top; padding:13px 14px 13px 6px;">
                        <table style="border-collapse:collapse;float:right;">
                            <tr><td class="fit-pill" style="background:{{ $fitColor($fitPct) }};">{{ $fitPct }}%</td></tr>
                        </table>
                    </td>
                    @endif
                </tr>
            </table>
        </div>
    @endforeach
</div>
@endif

{{-- ═══════════════════════════════════════════════════════
     BLOC COORDONNÉES / CONTACT
     ═══════════════════════════════════════════════════════ --}}
@if($sections['footer'] && ($org['advisor'] || $org['email'] || $org['phone'] || $org['website'] || $org['address']))
<div class="px avoid-break sec">
    <div class="contact-block" style="border-left:3px solid {{ $primary }};">
        <div style="font-size:8px; font-weight:bold; letter-spacing:2px; text-transform:uppercase;
                    color:{{ $primary }}; font-family:'DejaVu Sans Mono',monospace; margin-bottom:10px;">
            Pour aller plus loin
        </div>
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="vertical-align:top; width:55%;">
                    <div class="serif" style="font-size:13px; font-weight:bold; color:{{ $accent }};">{{ $org['name'] }}</div>
                    @if($org['advisor'])<div style="font-size:10.5px; color:{{ $ink }}; margin-top:2px;">{{ $org['advisor'] }}</div>@endif
                    @if($org['address'])<div style="font-size:10px; color:{{ $inkSoft }}; margin-top:3px;">{{ $org['address'] }}</div>@endif
                </td>
                
                <td style="vertical-align:top; text-align:right; font-size:10.5px; color:{{ $ink }}; width:45%;">
                    @if($org['email'])<div>{{ $org['email'] }}</div>@endif
                    @if($org['phone'])<div>{{ $org['phone'] }}</div>@endif
                    @if($org['website'])<div style="color:{{ $goldDark }}; font-weight:bold;">{{ $org['website'] }}</div>@endif
                </td>
            </tr>
        </table>
    </div>
</div>
@endif

</body>
</html>
