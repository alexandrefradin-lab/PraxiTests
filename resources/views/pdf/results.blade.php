{{--
  ════════════════════════════════════════════════════════════════════════════
  PraxiQuest — Rapport de synthèse PDF « Éditorial clair »
  Direction artistique : papier blanc, typographie généreuse, filets fins.
  Une seule couleur d'accent (or #A67520) employée en trait, jamais en aplat.
  L'encre ancienne #1C1408 ne subsiste que sur le bandeau d'en-tête répété.
  Titres en Lora (serif), tout le reste en Lato (sans-serif).
  Lora + Lato embarquées (resources/fonts, licence OFL) ; repli DejaVu conservé.
  Moteur : barryvdh/laravel-dompdf (CSS 2.1 → mise en page par <table>).
  AUCUN flexbox, AUCUN grid, AUCUN SVG. Seule exception ::after : le folio,
  via counter(page) — le seul compteur que dompdf résout (pages renvoie 0).
  Toute table de mise en page prend sa largeur de la classe .t100, jamais d'un
  style inline : dompdf décale alors son contenu d'1px (vérifié au rendu).

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
        /* Mentions complètes : rendues UNE fois, en fin de rapport. */
        'legal'   => 'Données traitées conformément au RGPD. '
            . "Outil d'auto-évaluation et de développement personnel : contenus générés par IA à titre informatif, "
            . "ne constituant pas un avis professionnel et ne remplaçant pas un psychologue, un médecin ou un coach.",
        /* Ligne courte du pied répété : le pied doit rester libre pour le
           tatouage de traçage nominatif, qui est la mention réellement
           dissuasive et doit figurer sur chaque page. */
        'legal_short' => 'Document confidentiel — usage personnel.',
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

    $primary   = $brand['primary'];     // or — employé en filet, jamais en aplat
    $accent    = $brand['accent'];      // encre — bandeau d'en-tête uniquement

    /* Tokens « éditorial clair » : un seul accent, une seule famille de gris chauds */
    $ink        = '#1C1408';   // titres
    $body       = '#2B2318';   // texte courant
    $soft       = '#6B5A3E';   // texte secondaire
    $muted      = '#8A7C64';   // labels, kickers, mentions
    $hair       = '#E4DED2';   // filet de séparation
    $rail       = '#EFEAE0';   // fond des jauges
    $velin      = '#FAF8F4';   // surface très légère (usage rare)
    $goldDark   = '#7D5510';   // or brûlé — or lisible sur blanc

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
    $mdToHtml = function (?string $md) use ($body, $muted): string {
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
        /* Puces : tiret cadratin discret plutôt qu'un chevron coloré. */
        $flushList = function () use (&$list, &$html, $inline, $body, $muted) {
            if ($list) {
                $html .= '<table class="md-list">';
                foreach ($list as $it) {
                    $html .= '<tr>'
                        . '<td style="width:14px;vertical-align:top;padding-right:8px;padding-bottom:5px;font-size:10px;color:' . $muted . ';line-height:1.75;">&mdash;</td>'
                        . '<td style="font-size:10.5px;line-height:1.75;color:' . $body . ';padding-bottom:5px;">' . $inline($it) . '</td>'
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
    {{-- Space Grotesk Bold : police du wordmark PraxiQuest, identique à celle de
         l'application (--font-display). Réservée au logo, jamais au texte.
         Instance STATIQUE : php-font-lib n'interpole pas les axes d'une police
         variable et rendrait le logo en Regular. Licence OFL, cf. resources/fonts. --}}
    @font-face { font-family:'SpaceGrotesk'; font-style:normal; font-weight:normal; src:url("{{ resource_path('fonts/SpaceGrotesk-Bold.ttf') }}") format("truetype"); }
    @font-face { font-family:'SpaceGrotesk'; font-style:normal; font-weight:bold;   src:url("{{ resource_path('fonts/SpaceGrotesk-Bold.ttf') }}") format("truetype"); }
    @endif

    /* @page margin : top = bandeau 48px + filet 1px + respiration 13px
                      bottom = pied 64px + respiration 20px
       Le pied est dimensionné pour 4 lignes de 8px : une ligne de mention
       courte + jusqu'à trois lignes de tatouage nominatif (les noms longs
       débordaient hors page — mesuré à y=-3.3pt sur un rendu réel). */
    @page { margin: 62px 0 84px 0; }
    * { box-sizing: border-box; }
    html { background: #FFFFFF; }
    body {
        font-family: "Lato", "DejaVu Sans", sans-serif;
        background: #FFFFFF;
        color: {{ $body }};
        font-size: 10.5px;
        line-height: 1.65;
        margin: 0;
    }
    .serif { font-family: "Lora", "DejaVu Serif", serif; }
    /* Gouttière éditoriale : 52px, tenue par toutes les sections. */
    .px    { padding-left: 52px; padding-right: 52px; }

    /* dompdf applique la feuille par défaut « td { padding: 1px } ». Ce 1px
       (0.75pt) décale la première colonne et casse l'alignement de la marge
       gauche — mesuré sur un rendu réel. On le remet à zéro ici ; chaque table
       déclare ensuite son propre rembourrage, en classe ou en inline. */
    td { padding: 0; }

    /* Table de mise en page pleine largeur. */
    table.t100 { width: 100%; border-collapse: collapse; }
    /* Listes à puces de la synthèse (générées par $mdToHtml). */
    table.md-list { width: 100%; border-collapse: collapse; margin: 2px 0 10px; }

    /* ══════════════════════════════════════════════════════
       EN-TÊTE RÉPÉTÉ (running header — dompdf fixed)
       Seul aplat sombre du document : bandeau 48px + filet or 1px
       ══════════════════════════════════════════════════════ */
    .run-header {
        position: fixed;
        top: -62px;
        left: 0; right: 0;
        height: 48px;
        background: {{ $accent }};
        padding: 0 52px;
    }
    .run-header table { width: 100%; height: 48px; border-collapse: collapse; }
    .run-header td { vertical-align: middle; padding: 0; }

    /* Wordmark : police de la marque, pas celle du document. */
    .rh-brand {
        font-family: "SpaceGrotesk", "Lato", "DejaVu Sans", sans-serif;
        font-size: 11.5px;
        font-weight: bold;
        color: #FFFFFF;
        letter-spacing: 0;
    }
    .rh-brand-q { color: {{ $primary }}; }

    .rh-info {
        font-size: 7.5px;
        color: #A2937A;
        letter-spacing: 1.4px;
        text-transform: uppercase;
        text-align: right;
    }

    /* Filet or sous le bandeau — 1px, signature discrète */
    .run-accent {
        position: fixed;
        top: -14px;
        left: 0; right: 0;
        height: 1px;
        background: {{ $primary }};
    }

    /* ══════════════════════════════════════════════════════
       PIED DE PAGE RÉPÉTÉ — filet fin, aucun aplat
       ══════════════════════════════════════════════════════ */
    .run-footer {
        position: fixed;
        bottom: -84px;
        left: 0; right: 0;
        height: 64px;
        border-top: 0.75px solid {{ $hair }};
        padding: 9px 52px 0;
    }
    .run-footer table { width: 100%; border-collapse: collapse; }
    .run-footer td { vertical-align: top; padding: 0; }
    /* 8px ≈ 6pt : plancher de lisibilité en impression pour une mention légale. */
    .rf-legal {
        font-size: 8px;
        color: {{ $muted }};
        line-height: 1.5;
    }
    .rf-brand {
        font-size: 7.5px;
        letter-spacing: 1.6px;
        text-transform: uppercase;
        color: {{ $soft }};
        text-align: right;
    }
    .rf-page {
        font-size: 8px;
        color: {{ $muted }};
        text-align: right;
        margin-top: 3px;
    }
    /* Numéro de page natif dompdf (le moteur PHP est désactivé).
       Vérifié au rendu : counter(page) est correct, counter(pages) renvoie 0 —
       on n'affiche donc que le folio, jamais un « 1 / 0 ». */
    .rf-page:after { content: counter(page); }

    /* ══════════════════════════════════════════════════════
       COUVERTURE — page blanche, hiérarchie purement typographique
       ══════════════════════════════════════════════════════ */
    .cov-kicker {
        font-size: 8px;
        font-weight: bold;
        letter-spacing: 2.6px;
        text-transform: uppercase;
        color: {{ $muted }};
    }
    .cov-rule { height: 2px; width: 28px; background: {{ $primary }}; margin: 10px 0 22px; font-size: 0; line-height: 0; }
    .cov-name {
        font-family: "Lora", "DejaVu Serif", serif;
        font-size: 31px;
        font-weight: bold;
        color: {{ $ink }};
        line-height: 1.15;
    }
    .cov-test {
        font-family: "Lora", "DejaVu Serif", serif;
        font-size: 14px;
        font-style: italic;
        color: {{ $soft }};
        margin-top: 6px;
    }
    .cov-brand {
        font-family: "SpaceGrotesk", "Lato", "DejaVu Sans", sans-serif;
        font-size: 12.5px;
        font-weight: bold;
        color: {{ $ink }};
        letter-spacing: 0;
    }
    .cov-brand span { color: {{ $goldDark }}; }
    .cov-emis {
        font-size: 7.5px;
        letter-spacing: 1.4px;
        text-transform: uppercase;
        color: {{ $muted }};
        margin-top: 5px;
    }
    /* Bandeau méta : deux filets fins, aucune couleur de fond */
    table.cov-meta { width: 100%; border-collapse: collapse; margin-top: 30px;
                     border-top: 0.75px solid {{ $hair }}; border-bottom: 0.75px solid {{ $hair }}; }
    table.cov-meta td { padding: 13px 24px 13px 0; vertical-align: top; }
    .meta-k { font-size: 7px; letter-spacing: 1.5px; text-transform: uppercase; color: {{ $muted }}; }
    .meta-v { font-family: "Lora", "DejaVu Serif", serif; font-size: 12px; color: {{ $ink }}; margin-top: 3px; }

    /* ══════════════════════════════════════════════════════
       TITRES DE SECTION — kicker + filet or court prolongé d'un hairline
       ══════════════════════════════════════════════════════ */
    .kicker {
        font-size: 8px;
        letter-spacing: 2.4px;
        text-transform: uppercase;
        color: {{ $ink }};
        font-weight: bold;
        margin: 0;
    }
    .s-rule { width: 100%; border-collapse: collapse; margin: 9px 0 16px; }
    .s-rule td.g { width: 24px; border-top: 1.5px solid {{ $primary }}; font-size: 0; line-height: 0; padding: 0; }
    .s-rule td.h { border-top: 0.75px solid {{ $hair }}; font-size: 0; line-height: 0; padding: 0; }

    .sec { margin-top: 30px; }
    .avoid-break { page-break-inside: avoid; }

    /* ══════════════════════════════════════════════════════
       CONTEXTE — table clé/valeur sans remplissage
       ══════════════════════════════════════════════════════ */
    /* Le filet de la dernière ligne est retiré via la classe .last posée par
       Blade : dompdf ne résout pas fiablement tr:last-child. */
    table.kv { width: 100%; border-collapse: collapse; }
    table.kv td { padding: 7px 0; vertical-align: top; border-bottom: 0.75px solid {{ $hair }}; }
    table.kv tr.last td { border-bottom: 0; }
    table.kv td.k { width: 32%; color: {{ $muted }}; font-size: 7.5px; text-transform: uppercase;
                    letter-spacing: 1.5px; padding-top: 9px; }
    table.kv td.v { font-family: "Lora", "DejaVu Serif", serif; font-size: 11.5px; color: {{ $ink }}; }

    /* ══════════════════════════════════════════════════════
       RÉSULTAT-PHARE — chiffre en majesté, séparé par un filet vertical
       ══════════════════════════════════════════════════════ */
    table.hero { width: 100%; border-collapse: collapse; }
    table.hero td { vertical-align: top; padding: 0; }
    .hero-figure {
        font-family: "Lora", "DejaVu Serif", serif;
        font-size: 42px;
        font-weight: bold;
        color: {{ $ink }};
        line-height: 1;
    }
    .hero-denom { font-family: "Lato", "DejaVu Sans", sans-serif; font-size: 13px; color: {{ $muted }}; }
    .hero-cap {
        font-size: 7px; letter-spacing: 1.6px; text-transform: uppercase;
        color: {{ $muted }}; margin-top: 8px;
    }
    .hero-raw { font-size: 10px; color: {{ $soft }}; margin-top: 2px; }
    .hero-code {
        font-size: 7.5px; font-weight: bold; letter-spacing: 2px;
        text-transform: uppercase; color: {{ $goldDark }}; margin-bottom: 6px;
    }
    .hero-label {
        font-family: "Lora", "DejaVu Serif", serif;
        font-size: 18px;
        font-weight: bold;
        color: {{ $ink }};
        line-height: 1.3;
    }
    .hero-phrase { font-size: 10.5px; color: {{ $soft }}; line-height: 1.7; margin-top: 7px; }

    /* ══════════════════════════════════════════════════════
       DIMENSIONS — jauges filiformes, une seule teinte
       ══════════════════════════════════════════════════════ */
    table.dims { width: 100%; border-collapse: collapse; }
    table.dims td { padding: 6px 0; vertical-align: middle; border-bottom: 0.75px solid {{ $hair }}; }
    table.dims tr.last td { border-bottom: 0; }
    .dim-name  { font-size: 10.5px; color: {{ $ink }}; }
    .dim-level { font-size: 7px; color: {{ $muted }}; text-transform: uppercase; letter-spacing: 1.2px; margin-top: 1px; }
    .track { background: {{ $rail }}; height: 4px; width: 100%; font-size: 0; line-height: 0; }
    .fill  { height: 4px; background: {{ $primary }}; font-size: 0; line-height: 0; }
    .dim-score { font-family: "Lora", "DejaVu Serif", serif; font-size: 12px; font-weight: bold;
                 text-align: right; color: {{ $ink }}; }

    /* ══════════════════════════════════════════════════════
       SYNTHÈSE — colonne de texte nue, pas d'encadré
       ══════════════════════════════════════════════════════ */
    .synth-p  { font-size: 10.5px; line-height: 1.8; color: {{ $body }};
                margin: 0 0 11px; text-align: justify; }
    .synth-h  { font-family: "Lora", "DejaVu Serif", serif; color: {{ $ink }};
                margin: 16px 0 7px; line-height: 1.35; }
    .synth-h1 { font-size: 14px; font-weight: bold; }
    .synth-h2 { font-size: 12.5px; font-weight: bold; }
    .synth-h3 { font-size: 11.5px; font-weight: bold; }
    .synth-hr { border-top: 0.75px solid {{ $hair }}; margin: 14px 0; height: 0; }
    .synth-p strong, .synth-h strong { color: {{ $ink }}; }
    .ai-note {
        font-size: 8px;
        font-style: italic;
        color: {{ $muted }};
        border-top: 0.75px solid {{ $hair }};
        padding-top: 7px;
        margin-top: 4px;
    }

    /* ══════════════════════════════════════════════════════
       LEVIERS — numérotation typographique, aucune pastille
       ══════════════════════════════════════════════════════ */
    .col-head {
        font-size: 7.5px; font-weight: bold; letter-spacing: 2px;
        text-transform: uppercase; color: {{ $muted }}; margin-bottom: 12px;
    }
    .points-list { width: 100%; border-collapse: collapse; }
    .points-list td { padding: 0 0 9px 0; vertical-align: top; }
    .point-num {
        font-family: "Lora", "DejaVu Serif", serif;
        font-size: 10px; font-weight: bold;
        color: {{ $goldDark }};
        width: 22px;
        line-height: 1.6;
    }
    .point-num.alt { color: {{ $muted }}; }
    .point-label { font-size: 10.5px; line-height: 1.6; color: {{ $body }}; }
    .point-label strong { color: {{ $ink }}; }

    /* ══════════════════════════════════════════════════════
       SOUS-ÉCHELLES
       ══════════════════════════════════════════════════════ */
    .sub-group { margin-top: 16px; }
    .sub-title { font-size: 7.5px; font-weight: bold; letter-spacing: 1.8px;
                 text-transform: uppercase; color: {{ $muted }}; margin-bottom: 6px; }
    .fig-cap   { font-size: 7.5px; letter-spacing: 0.6px; font-style: italic;
                 color: {{ $muted }}; text-align: center; margin-top: 6px; }
    .sub-val   { font-family: "Lora", "DejaVu Serif", serif; font-size: 11px; font-weight: bold;
                 color: {{ $ink }}; text-align: right; }
    .sub-level { font-size: 7px; letter-spacing: 1.2px; text-transform: uppercase;
                 color: {{ $muted }}; text-align: right; }

    /* ══════════════════════════════════════════════════════
       AVERTISSEMENT — encadré filaire
       ══════════════════════════════════════════════════════ */
    .disclaimer { border: 0.75px solid {{ $hair }}; background: {{ $velin }}; }
    .disclaimer td { padding: 13px 16px; }
    .disclaimer .dtitle { font-size: 7.5px; letter-spacing: 1.8px; text-transform: uppercase;
                          color: {{ $muted }}; font-weight: bold; }
    .disclaimer .dbody  { font-size: 9.5px; color: {{ $body }}; line-height: 1.65; margin-top: 5px; }

    /* ══════════════════════════════════════════════════════
       ORIENTATION — liste de métiers séparée par des filets
       Blocs <div> et non lignes de tableau : dompdf n'honore
       page-break-inside:avoid que sur des blocs, pas sur <tr>.
       ══════════════════════════════════════════════════════ */
    .job { padding: 14px 0; border-bottom: 0.75px solid {{ $hair }}; }
    .job.last { border-bottom: 0; }
    .job table { width: 100%; border-collapse: collapse; }
    .job td { vertical-align: top; padding: 0; }
    .job-rank {
        font-family: "Lora", "DejaVu Serif", serif;
        font-size: 15px; font-weight: bold;
        color: {{ $goldDark }};
        line-height: 1.2;
    }
    .job-sector { font-size: 7px; text-transform: uppercase; letter-spacing: 1.6px; color: {{ $muted }}; margin-bottom: 3px; }
    .job-title  { font-family: "Lora", "DejaVu Serif", serif; font-size: 13px; font-weight: bold; color: {{ $ink }}; }
    .job-why    { font-size: 10.5px; color: {{ $body }}; margin-top: 5px; line-height: 1.7; }
    .job-next   { font-size: 10px; color: {{ $soft }}; margin-top: 6px; font-style: italic; }
    .job-fit    { font-family: "Lora", "DejaVu Serif", serif; font-size: 14px; font-weight: bold;
                  color: {{ $ink }}; text-align: right; line-height: 1; }
    .job-fit-cap { font-size: 6.5px; letter-spacing: 1.3px; text-transform: uppercase;
                   color: {{ $muted }}; text-align: right; margin-top: 4px; }

    /* ══════════════════════════════════════════════════════
       COORDONNÉES — bloc filaire de clôture
       ══════════════════════════════════════════════════════ */
    .contact-block { border-top: 1.5px solid {{ $primary }}; border-bottom: 0.75px solid {{ $hair }}; }
    .contact-block td { padding: 16px 0; vertical-align: top; }
    .contact-head { font-size: 7.5px; font-weight: bold; letter-spacing: 2px;
                    text-transform: uppercase; color: {{ $muted }}; margin-bottom: 9px; }

    /* ══════════════════════════════════════════════════════
       MENTIONS — paragraphe légal complet, une seule fois,
       en clôture. Le pied répété n'en porte que le résumé.
       ══════════════════════════════════════════════════════ */
    .mentions { margin-top: 22px; }
    .mentions-head { font-size: 7px; font-weight: bold; letter-spacing: 2px;
                     text-transform: uppercase; color: {{ $muted }}; margin-bottom: 6px; }
    .mentions-body { font-size: 8.5px; line-height: 1.6; color: {{ $muted }}; }
</style>
</head>
<body>

{{-- ═══════════════════════════════════════════════════════
     EN-TÊTE RÉPÉTÉ — unique aplat encre du document
     ═══════════════════════════════════════════════════════ --}}
<div class="run-header">
    <table>
        <tr>
            <td style="vertical-align:middle;">
                <div class="rh-brand">Praxi<span class="rh-brand-q">Quest</span></div>
            </td>
            <td style="vertical-align:middle; text-align:right;">
                <div class="rh-info">{{ $test->name }} &middot; {{ $candidate }}</div>
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
            <td style="width:70%; padding-right:28px;">
                <div class="rf-legal">{{ $org['legal_short'] }}</div>
                {{-- Traçage anti-fuite : mention nominative + référence unique
                     par couple (compte, document). Cf. DocumentWatermark. --}}
                @if(($watermark ?? null) && $watermark['visible'])
                    <div class="rf-legal" style="margin-top:2px;">{{ $watermark['notice'] }}</div>
                @endif
            </td>
            <td style="width:30%; text-align:right;">
                <div class="rf-brand">{{ $brand['name'] }} &middot; {{ $dateDone->format('d/m/Y') }}</div>
                <div class="rf-page"></div>
            </td>
        </tr>
    </table>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════
     COUVERTURE — papier blanc, hiérarchie typographique
     Gauche : kicker, filet or, nom, test
     Droite : marque et date d'émission
     Bas : bandeau méta encadré de deux filets fins
     ═══════════════════════════════════════════════════════ --}}
@if($sections['cover'])
<div class="px" style="padding-top:34px;">
    <table class="t100">
        <tr>
            <td style="vertical-align:top; padding-right:24px;">
                <div class="cov-kicker">Rapport d'&eacute;valuation</div>
                <div class="cov-rule"></div>
                <div class="cov-name">{{ $candidate }}</div>
                <div class="cov-test">{{ $test->name }}</div>
            </td>
            <td style="vertical-align:top; text-align:right; width:170px;">
                @if(!empty($brand['logo']))
                    <img src="{{ $brand['logo'] }}" alt="{{ $brand['name'] }}" style="max-height:40px;">
                @else
                    <div class="cov-brand">Praxi<span>Quest</span></div>
                @endif
                <div class="cov-emis">&Eacute;mis le {{ $dateDone->format('d/m/Y') }}</div>
            </td>
        </tr>
    </table>

    <table class="cov-meta">
        <tr>
            @if($statusLabel)
            <td>
                <div class="meta-k">Statut</div>
                <div class="meta-v">{{ $statusLabel }}</div>
            </td>
            @endif
            @if($seniority)
            <td>
                <div class="meta-k">Anciennet&eacute;</div>
                <div class="meta-v">{{ $seniority }}</div>
            </td>
            @endif
            <td>
                <div class="meta-k">Date de passation</div>
                <div class="meta-v">{{ $dateDone->format('d/m/Y') }}</div>
            </td>
            @if(count($dimensions))
            <td>
                <div class="meta-k">Dimension{{ count($dimensions) > 1 ? 's' : '' }}</div>
                <div class="meta-v">{{ count($dimensions) }}</div>
            </td>
            @endif
            @if(count($jobs))
            <td>
                <div class="meta-k">Pistes m&eacute;tiers</div>
                <div class="meta-v">{{ count($jobs) }}</div>
            </td>
            @endif
        </tr>
    </table>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════
     CONTEXTE
     ═══════════════════════════════════════════════════════ --}}
@if($sections['profile'] && $profile)
<div class="px avoid-break sec">
    <div class="kicker">{{ $roman(++$chapN) }}. Contexte</div>
    <table class="s-rule"><tr><td class="g"></td><td class="h"></td></tr></table>
    @php
        /* Lignes construites en amont : permet de savoir laquelle est la
           dernière (filet supprimé) quel que soit le remplissage du profil. */
        $kvRows = array_filter([
            'Nom'          => $candidate,
            'Statut'       => $statusLabel,
            'Ancienneté'   => $seniority,
            'Poste actuel' => $profile->current_role,
            'Secteur'      => $profile->industry,
            'CV fourni'    => $profile->cv_original_name,
            'Évaluation'   => $test->name,
        ], fn ($v) => filled($v));
    @endphp
    <table class="kv">
        @foreach($kvRows as $k => $v)
            <tr @class(['last' => $loop->last])>
                <td class="k">{{ $k }}</td>
                <td class="v">{{ $v }}</td>
            </tr>
        @endforeach
    </table>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════
     RÉSULTAT-PHARE — chiffre en majesté, filet vertical de séparation
     ═══════════════════════════════════════════════════════ --}}
@if($headline)
<div class="px sec avoid-break">
    <div class="kicker">{{ $roman(++$chapN) }}. Votre r&eacute;sultat</div>
    <table class="s-rule"><tr><td class="g"></td><td class="h"></td></tr></table>
    <table class="hero">
        <tr>
            @if($headline['pct'] !== null)
            <td style="width:132px; padding-right:26px;">
                <div class="hero-figure">{{ $headline['pct'] }}<span class="hero-denom">/100</span></div>
                <div class="hero-cap">Score global</div>
                @if($headline['score'] !== null && $headline['score_max'])
                    <div class="hero-raw">{{ $headline['score'] }} points sur {{ $headline['score_max'] }}</div>
                @endif
            </td>
            <td style="padding-left:26px; border-left:0.75px solid {{ $hair }};">
            @else
            <td>
            @endif
                @if($headline['code'])
                    <div class="hero-code">Profil {{ $headline['code'] }}</div>
                @endif
                <div class="hero-label">{{ $headline['label'] ?? 'Profil établi' }}</div>
                @if($headline['phrase'])
                    <div class="hero-phrase">{{ $headline['phrase'] }}</div>
                @endif
            </td>
        </tr>
    </table>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════
     PROFIL DIMENSIONNEL — jauges filiformes
     ═══════════════════════════════════════════════════════ --}}
@if($sections['dimensions'] && count($dimensions))
<div class="px sec">
    <div class="kicker">{{ $roman(++$chapN) }}. Profil dimensionnel</div>
    <table class="s-rule"><tr><td class="g"></td><td class="h"></td></tr></table>

    @if($radarUri)
    <table class="t100">
        <tr>
            <td class="avoid-break" style="text-align:center; padding:0 0 20px;">
                {{-- Toile rendue en 480px : affichée à 440px, les libellés
                     restent à l'échelle des petites capitales du document. --}}
                <img src="{{ $radarUri }}" style="width:440px; height:auto;">
                <div class="fig-cap">Score par dimension, sur 100</div>
            </td>
        </tr>
    </table>
    <div class="sub-title">D&eacute;tail par dimension</div>
    @endif

    <table class="dims">
        @foreach($dimensions as $dim)
            @php $sc = max(0, min(100, (int) $dim['pct'])); @endphp
            <tr @class(['last' => $loop->last])>
                <td style="width:36%;">
                    <div class="dim-name">{{ $dim['name'] }}</div>
                    @if(!empty($dim['level']))<div class="dim-level">{{ $dim['level'] }}</div>@endif
                </td>
                <td style="width:52%; padding-left:16px; padding-right:16px;">
                    <div class="track"><div class="fill" style="width:{{ $sc }}%;"></div></div>
                </td>
                <td style="width:12%;">
                    <div class="dim-score">{{ $sc }}</div>
                </td>
            </tr>
        @endforeach
    </table>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════
     SYNTHÈSE — colonne de texte nue
     ═══════════════════════════════════════════════════════ --}}
@if($sections['synthesis'] && $synthesis)
<div class="px sec">
    <div class="kicker">{{ $roman(++$chapN) }}. Synth&egrave;se analytique</div>
    <table class="s-rule"><tr><td class="g"></td><td class="h"></td></tr></table>
    <div>{!! $mdToHtml($synthesis) !!}</div>
    <div class="ai-note">Analyse g&eacute;n&eacute;r&eacute;e par {{ $brand['name'] }} IA &mdash; &agrave; confronter &agrave; votre propre lecture.</div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════
     LEVIERS — deux colonnes, numérotation typographique
     ═══════════════════════════════════════════════════════ --}}
@if($sections['strengths'] && (count($strengths) || count($growth)))
<div class="px sec avoid-break">
    <div class="kicker">{{ $roman(++$chapN) }}. Leviers de d&eacute;veloppement</div>
    <table class="s-rule"><tr><td class="g"></td><td class="h"></td></tr></table>
    <table class="t100">
        <tr>
            @if(count($strengths))
            <td style="width:50%; vertical-align:top; padding-right:22px;">
                <div class="col-head">Points forts identifi&eacute;s</div>
                <table class="points-list">
                    @foreach($strengths as $i => $s)
                    <tr>
                        <td class="point-num">{{ sprintf('%02d', $i + 1) }}</td>
                        <td class="point-label">
                            {{ is_array($s) ? ($s['label'] ?? $s['name'] ?? reset($s)) : $s }}
                        </td>
                    </tr>
                    @endforeach
                </table>
            </td>
            @endif
            @if(count($growth))
            <td style="width:50%; vertical-align:top; padding-left:22px; border-left:0.75px solid {{ $hair }};">
                <div class="col-head">Axes de d&eacute;veloppement</div>
                <table class="points-list">
                    @foreach($growth as $i => $g)
                    <tr>
                        <td class="point-num alt">{{ sprintf('%02d', $i + 1) }}</td>
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
    <table class="t100">
        <tr>
            <td class="avoid-break" style="text-align:center; padding:0 0 18px;">
                <img src="{{ $quadrantUri }}" style="width:340px; height:auto;">
                <div class="fig-cap">Mod&egrave;le de Karasek &mdash; tension per&ccedil;ue &times; marge de manoeuvre</div>
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
                    <tr @class(['last' => $loop->last])>
                        <td style="width:36%;"><div class="dim-name">{{ $item['name'] }}</div></td>
                        <td style="width:40%; padding-left:16px; padding-right:16px;">
                            @if($sc !== null)
                                <div class="track"><div class="fill" style="width:{{ $sc }}%;"></div></div>
                            @endif
                        </td>
                        <td style="width:12%;">
                            <div class="sub-val">
                                @if($item['value'] !== null)
                                    {{ $item['value'] }}@if($item['max'])<span style="font-family:'Lato','DejaVu Sans',sans-serif; font-size:9px; color:{{ $muted }}; font-weight:normal;">/{{ $item['max'] }}</span>@endif
                                @endif
                            </div>
                        </td>
                        <td style="width:12%; padding-left:10px;">
                            @if(!empty($item['level']))
                                <div class="sub-level">{{ $item['level'] }}</div>
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
    <table class="disclaimer t100">
        <tr><td>
            <div class="dtitle">&Agrave; lire &mdash; port&eacute;e de ce bilan</div>
            <div class="dbody">{{ $disclaimer }}</div>
        </td></tr>
    </table>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════
     ORIENTATION — liste séparée par des filets, aucune carte
     ═══════════════════════════════════════════════════════ --}}
@if($sections['jobs'] && count($jobs))
<div style="page-break-before: always;"></div>
<div class="px sec">
    <div class="kicker">{{ $roman(++$chapN) }}. Orientation</div>
    <table class="s-rule"><tr><td class="g"></td><td class="h"></td></tr></table>
    <div style="font-family:'Lora','DejaVu Serif',serif; font-size:16px; color:{{ $ink }}; margin-bottom:4px;">
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
        <div class="job avoid-break{{ $loop->last ? ' last' : '' }}">
            <table>
                <tr>
                    <td style="width:34px;">
                        <div class="job-rank">{{ sprintf('%02d', $i + 1) }}</div>
                    </td>
                    <td style="padding-right:20px;">
                        @if($secteur)<div class="job-sector">{{ $secteur }}</div>@endif
                        <div class="job-title">{{ $titre }}</div>
                        @if($why)<div class="job-why">{{ $why }}</div>@endif
                        @if($next)<div class="job-next">&rarr; {{ $next }}</div>@endif
                    </td>
                    @if($fitPct !== null)
                    <td style="width:64px;">
                        <div class="job-fit">{{ $fitPct }}%</div>
                        <div class="job-fit-cap">Correspondance</div>
                    </td>
                    @endif
                </tr>
            </table>
        </div>
    @endforeach
</div>
@endif

{{-- ═══════════════════════════════════════════════════════
     COORDONNÉES — clôture filaire
     ═══════════════════════════════════════════════════════ --}}
@if($sections['footer'] && ($org['advisor'] || $org['email'] || $org['phone'] || $org['website'] || $org['address']))
<div class="px avoid-break sec">
    <table class="contact-block t100">
        <tr>
            <td style="vertical-align:top; width:55%; padding-right:20px;">
                <div class="contact-head">Pour aller plus loin</div>
                <div class="serif" style="font-size:13px; font-weight:bold; color:{{ $ink }};">{{ $org['name'] }}</div>
                @if($org['advisor'])<div style="font-size:10.5px; color:{{ $body }}; margin-top:3px;">{{ $org['advisor'] }}</div>@endif
                @if($org['address'])<div style="font-size:10px; color:{{ $muted }}; margin-top:3px;">{{ $org['address'] }}</div>@endif
            </td>
            <td style="vertical-align:bottom; text-align:right; font-size:10.5px; color:{{ $body }}; width:45%;">
                @if($org['email'])<div>{{ $org['email'] }}</div>@endif
                @if($org['phone'])<div>{{ $org['phone'] }}</div>@endif
                @if($org['website'])<div style="color:{{ $goldDark }};">{{ $org['website'] }}</div>@endif
            </td>
        </tr>
    </table>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════
     MENTIONS — clôture du document, rendue une seule fois
     ═══════════════════════════════════════════════════════ --}}
@if($sections['footer'] && $org['legal'])
<div class="px avoid-break mentions">
    <div class="mentions-head">Mentions</div>
    <div class="mentions-body">{{ $org['legal'] }}</div>
</div>
@endif

</body>
</html>
