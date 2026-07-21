{{--
  PraxiQuest — Le Grimoire (relecture globale transversale) — PDF DomPDF.
  Direction artistique « Éditorial clair », alignée sur resources/views/pdf/results.blade.php :
  papier blanc, filets fins, or employé en trait et jamais en aplat, encre
  ancienne réservée au bandeau d'en-tête répété.
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
        /* Mentions complètes : rendues UNE fois, en fin de rapport. */
        'legal' => 'Données traitées conformément au RGPD. '
            . "Outil d'auto-évaluation et de développement personnel : contenus générés par IA à titre informatif, "
            . "ne constituant pas un avis professionnel et ne remplaçant pas un psychologue, un médecin ou un coach.",
        /* Ligne courte du pied répété : le pied doit rester libre pour le
           tatouage de traçage nominatif, qui doit figurer sur chaque page. */
        'legal_short' => 'Document confidentiel — usage personnel.',
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
    $iaImpact  = trim((string) ($grimoire->ia_impact ?? ''));

    /* Tokens « éditorial clair » — mêmes valeurs que la synthèse de test. */
    $ink       = '#1C1408';   // titres
    $body      = '#2B2318';   // texte courant
    $soft      = '#6B5A3E';   // texte secondaire
    $muted     = '#8A7C64';   // labels, kickers, mentions
    $hair      = '#E4DED2';   // filet de séparation
    $primary   = $brand['primary'];
    $accent    = $brand['accent'];
    $goldDark  = '#7D5510';

    // Rendu Markdown minimal (titres ##, listes -/*, gras **…**) → HTML pour DomPDF.
    // Léger et sûr : on échappe le texte puis on applique un petit sous-ensemble.
    $renderIaMd = function (string $md) use ($body, $muted): string {
        $lines  = preg_split('/\r?\n/', $md);
        $html   = '';
        $items  = [];
        $inline = function (string $s): string {
            $s = e($s);
            $s = preg_replace('/\*\*(.+?)\*\*/u', '<strong>$1</strong>', $s);
            $s = preg_replace('/(?<!\*)\*(?!\s)(.+?)(?<!\s)\*(?!\*)/u', '<em>$1</em>', $s);
            return $s;
        };
        /* Puces : tiret cadratin discret plutôt qu'un chevron coloré. */
        $flushList = function () use (&$items, &$html, $inline, $body, $muted) {
            if ($items) {
                $html .= '<table class="md-list">';
                foreach ($items as $it) {
                    $html .= '<tr>'
                        . '<td style="width:14px;vertical-align:top;padding-right:8px;padding-bottom:5px;font-size:10px;color:' . $muted . ';line-height:1.75;">&mdash;</td>'
                        . '<td style="font-size:10.5px;line-height:1.75;color:' . $body . ';padding-bottom:5px;">' . $inline($it) . '</td>'
                        . '</tr>';
                }
                $html  .= '</table>';
                $items  = [];
            }
        };
        foreach ($lines as $line) {
            $t = trim($line);
            if ($t === '') { $flushList(); continue; }
            if (preg_match('/^#{2,3}\s+(.*)$/u', $t, $m)) {
                $flushList();
                $html .= '<p class="ia-h">' . $inline($m[1]) . '</p>';
            } elseif (preg_match('/^[-*]\s+(.*)$/u', $t, $m)) {
                $items[] = $m[1];
            } else {
                $flushList();
                $html .= '<p class="ia-p">' . $inline($t) . '</p>';
            }
        }
        $flushList();
        return $html;
    };

    /* Numérotation éditoriale des chapitres, séquentielle même si une section manque. */
    $chapN = 0;
    $roman = function (int $n): string {
        $map = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'];
        return $map[$n] ?? (string) $n;
    };
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
    {{-- Space Grotesk Bold : police du wordmark PraxiQuest, identique à celle de
         l'application (--font-display). Réservée au logo, jamais au texte.
         Instance STATIQUE : php-font-lib n'interpole pas les axes d'une police
         variable et rendrait le logo en Regular. Licence OFL, cf. resources/fonts. --}}
    @font-face { font-family:'SpaceGrotesk'; font-style:normal; font-weight:normal; src:url("{{ resource_path('fonts/SpaceGrotesk-Bold.ttf') }}") format("truetype"); }
    @font-face { font-family:'SpaceGrotesk'; font-style:normal; font-weight:bold;   src:url("{{ resource_path('fonts/SpaceGrotesk-Bold.ttf') }}") format("truetype"); }
    @endif

    /* top = bandeau 48px + filet 1px + respiration ; bottom = pied 64px + respiration.
       Le pied loge 4 lignes de 8px : mention courte + tatouage nominatif. */
    @page { margin: 62px 0 84px 0; }
    * { box-sizing: border-box; }

    body {
        font-family: 'Lato', 'DejaVu Sans', sans-serif;
        color: {{ $body }};
        font-size: 10.5px;
        line-height: 1.65;
        background: #FFFFFF;
        margin: 0;
        padding: 0;
    }
    .serif { font-family: 'Lora', 'DejaVu Serif', serif; }
    /* Gouttière éditoriale : 52px, tenue par toutes les sections. */
    .px { padding-left: 52px; padding-right: 52px; }
    .avoid-break { page-break-inside: avoid; }
    .sec { margin-top: 30px; }

    /* dompdf applique la feuille par défaut « td { padding: 1px } ». Ce 1px
       (0.75pt) décale la première colonne et casse l'alignement de la marge
       gauche — mesuré sur un rendu réel. On le remet à zéro ici ; chaque table
       déclare ensuite son propre rembourrage, en classe ou en inline. */
    td { padding: 0; }

    /* Table de mise en page pleine largeur. */
    table.t100 { width: 100%; border-collapse: collapse; }
    /* Listes à puces du bloc IA (générées par $renderIaMd). */
    table.md-list { width: 100%; border-collapse: collapse; margin: 2px 0 10px; }

    /* ── En-tête répété — unique aplat encre du document ── */
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
    /* Largeur réservée au logo : sans elle, un nom long comprime la cellule
       jusqu'à couper le wordmark en deux lignes (constaté sur un rendu de charge). */
    .run-header td.brand { width: 120px; }
    /* Wordmark : police de la marque, pas celle du document. */
    .rh-brand {
        font-family: 'SpaceGrotesk', 'Lato', 'DejaVu Sans', sans-serif;
        font-size: 11.5px;
        font-weight: bold;
        color: #FFFFFF;
        letter-spacing: 0;
        white-space: nowrap;
    }
    .rh-brand span { color: {{ $primary }}; }
    .rh-info {
        font-size: 7.5px;
        color: #A2937A;
        letter-spacing: 1.4px;
        text-transform: uppercase;
        text-align: right;
    }
    .run-accent {
        position: fixed;
        top: -14px;
        left: 0; right: 0;
        height: 1px;
        background: {{ $primary }};
    }

    /* ── Pied de page répété — filet fin, aucun aplat ── */
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
    .rf-legal { font-size: 8px; color: {{ $muted }}; line-height: 1.5; }
    .rf-brand {
        font-size: 7.5px;
        letter-spacing: 1.6px;
        text-transform: uppercase;
        color: {{ $soft }};
        text-align: right;
    }
    .rf-page { font-size: 8px; color: {{ $muted }}; text-align: right; margin-top: 3px; }
    /* Numéro de page natif dompdf (le moteur PHP est désactivé).
       Vérifié au rendu : counter(page) est correct, counter(pages) renvoie 0 —
       on n'affiche donc que le folio, jamais un « 1 / 0 ». */
    .rf-page:after { content: counter(page); }

    /* ── Couverture — papier blanc, hiérarchie typographique ── */
    .cov-kicker {
        font-size: 8px;
        font-weight: bold;
        letter-spacing: 2.6px;
        text-transform: uppercase;
        color: {{ $muted }};
    }
    .cov-rule { height: 2px; width: 28px; background: {{ $primary }}; margin: 10px 0 22px; font-size: 0; line-height: 0; }
    .cov-name {
        font-family: 'Lora', 'DejaVu Serif', serif;
        font-size: 31px;
        font-weight: bold;
        color: {{ $ink }};
        line-height: 1.15;
    }
    .cov-tagline {
        font-family: 'Lora', 'DejaVu Serif', serif;
        font-size: 14px;
        font-style: italic;
        color: {{ $soft }};
        margin-top: 6px;
    }
    .cov-brand {
        font-family: 'SpaceGrotesk', 'Lato', 'DejaVu Sans', sans-serif;
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
    table.cov-meta { width: 100%; border-collapse: collapse; margin-top: 30px;
                     border-top: 0.75px solid {{ $hair }}; border-bottom: 0.75px solid {{ $hair }}; }
    table.cov-meta td { padding: 13px 24px 13px 0; vertical-align: top; }
    .meta-k { font-size: 7px; letter-spacing: 1.5px; text-transform: uppercase; color: {{ $muted }}; }
    .meta-v { font-family: 'Lora', 'DejaVu Serif', serif; font-size: 12px; color: {{ $ink }}; margin-top: 3px; }
    /* Épreuves croisées : une ligne de texte, plus de chips */
    .cov-tests { font-size: 9px; color: {{ $muted }}; margin-top: 10px; line-height: 1.6; }

    /* ── Titres de section — kicker + filet or court prolongé d'un hairline ── */
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

    /* ── Corps de texte — colonne nue, pas d'encadré ── */
    .para {
        font-size: 10.5px;
        line-height: 1.8;
        color: {{ $body }};
        text-align: justify;
        margin: 0 0 11px;
    }
    .ia-block .ia-h {
        font-family: 'Lora', 'DejaVu Serif', serif;
        font-size: 12.5px;
        font-weight: bold;
        color: {{ $ink }};
        margin: 16px 0 7px;
        line-height: 1.35;
    }
    .ia-block .ia-h:first-child { margin-top: 0; }
    .ia-block .ia-p {
        font-size: 10.5px;
        line-height: 1.8;
        color: {{ $body }};
        text-align: justify;
        margin: 0 0 11px;
    }
    .ai-note {
        font-size: 8px;
        font-style: italic;
        color: {{ $muted }};
        border-top: 0.75px solid {{ $hair }};
        padding-top: 7px;
        margin-top: 4px;
    }

    /* ── Voies — liste séparée par des filets, aucune carte ──
       Blocs <div> et non lignes de tableau : dompdf n'honore
       page-break-inside:avoid que sur des blocs, pas sur <tr>. */
    .voie { padding: 14px 0; border-bottom: 0.75px solid {{ $hair }}; }
    .voie.last { border-bottom: 0; }
    .voie table { width: 100%; border-collapse: collapse; }
    .voie td { vertical-align: top; padding: 0; }
    .voie-rank {
        font-family: 'Lora', 'DejaVu Serif', serif;
        font-size: 15px; font-weight: bold;
        color: {{ $goldDark }};
        line-height: 1.2;
    }
    .voie-sector { font-size: 7px; text-transform: uppercase; letter-spacing: 1.6px; color: {{ $muted }}; margin-bottom: 3px; }
    .voie-title  { font-family: 'Lora', 'DejaVu Serif', serif; font-size: 13px; font-weight: bold; color: {{ $ink }}; }
    .voie-why    { font-size: 10.5px; color: {{ $body }}; margin-top: 5px; line-height: 1.7; text-align: justify; }
    .voie-appui  { font-size: 9px; color: {{ $muted }}; margin-top: 6px; }
    .voie-next   { font-size: 10px; color: {{ $soft }}; margin-top: 5px; font-style: italic; }
    .voie-fit    { font-family: 'Lora', 'DejaVu Serif', serif; font-size: 14px; font-weight: bold;
                   color: {{ $ink }}; text-align: right; line-height: 1; }
    .voie-fit-cap { font-size: 6.5px; letter-spacing: 1.3px; text-transform: uppercase;
                    color: {{ $muted }}; text-align: right; margin-top: 4px; }

    /* ── Mentions : paragraphe légal complet, une seule fois, en clôture ── */
    .mentions { margin-top: 22px; }
    .mentions-head { font-size: 7px; font-weight: bold; letter-spacing: 2px;
                     text-transform: uppercase; color: {{ $muted }}; margin-bottom: 6px; }
    .mentions-body { font-size: 8.5px; line-height: 1.6; color: {{ $muted }}; }
</style>
</head>
<body>

{{-- ── En-tête répété (dompdf fixed positioning) ── --}}
<div class="run-header">
    <table>
        <tr>
            <td class="brand" style="vertical-align:middle;">
                <div class="rh-brand">Praxi<span>Quest</span></div>
            </td>
            <td style="vertical-align:middle; text-align:right;">
                <div class="rh-info">Le Grimoire &middot; {{ $candidate }}</div>
            </td>
        </tr>
    </table>
</div>
<div class="run-accent"></div>

{{-- ── Pied de page répété ── --}}
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
                <div class="rf-brand">{{ $brand['name'] }} &middot; {{ $date->format('d/m/Y') }}</div>
                <div class="rf-page"></div>
            </td>
        </tr>
    </table>
</div>

{{-- ── COUVERTURE ── --}}
<div class="px" style="padding-top:34px;">
    <table class="t100">
        <tr>
            <td style="vertical-align:top; padding-right:24px;">
                <div class="cov-kicker">Le Grimoire &middot; Relecture globale</div>
                <div class="cov-rule"></div>
                <div class="cov-name">{{ $candidate }}</div>
                @if($brand['tagline'])
                <div class="cov-tagline">{{ $brand['tagline'] }}</div>
                @endif
            </td>
            <td style="vertical-align:top; text-align:right; width:170px;">
                @if(!empty($brand['logo']))
                    <img src="{{ $brand['logo'] }}" alt="{{ $brand['name'] }}" style="max-height:40px;">
                @else
                    <div class="cov-brand">Praxi<span>Quest</span></div>
                @endif
                <div class="cov-emis">&Eacute;mis le {{ $date->format('d/m/Y') }}</div>
            </td>
        </tr>
    </table>

    <table class="cov-meta">
        <tr>
            @if($statut)
            <td>
                <div class="meta-k">Statut</div>
                <div class="meta-v">{{ $statut }}</div>
            </td>
            @endif
            <td>
                <div class="meta-k">Date</div>
                <div class="meta-v">{{ $date->format('d/m/Y') }}</div>
            </td>
            <td>
                <div class="meta-k">&Eacute;preuves crois&eacute;es</div>
                <div class="meta-v">{{ count($tests) }}</div>
            </td>
        </tr>
    </table>

    @if(count($tests))
    <div class="cov-tests">
        {{ collect($tests)->map(fn ($t) => $t['test'] ?? 'Test')->implode(' · ') }}
    </div>
    @endif
</div>

{{-- ── SECTION : Le fil conducteur ── --}}
@if($synthesis)
<div class="px sec">
    <div class="kicker">{{ $roman(++$chapN) }}. Le fil conducteur</div>
    <table class="s-rule"><tr><td class="g"></td><td class="h"></td></tr></table>
    @foreach(array_filter(explode("\n\n", $synthesis), fn($p) => trim($p) !== '') as $para)
        <div class="para">{{ trim($para) }}</div>
    @endforeach
</div>
@endif

{{-- ── SECTION : Ton métier face à l'IA ── --}}
@if($iaImpact)
<div class="px sec">
    <div class="kicker">{{ $roman(++$chapN) }}. Ton m&eacute;tier face &agrave; l'IA</div>
    <table class="s-rule"><tr><td class="g"></td><td class="h"></td></tr></table>
    <div class="ia-block">{!! $renderIaMd($iaImpact) !!}</div>
</div>
@endif

{{-- ── SECTION : Tes Voies Possibles ── --}}
@if(count($voies))
<div class="px sec">
    <div class="kicker">{{ $roman(++$chapN) }}. Tes voies possibles</div>
    <table class="s-rule"><tr><td class="g"></td><td class="h"></td></tr></table>

    @foreach($voies as $i => $v)
    <div class="voie avoid-break{{ $loop->last ? ' last' : '' }}">
        <table>
            <tr>
                <td style="width:34px;">
                    <div class="voie-rank">{{ sprintf('%02d', $i + 1) }}</div>
                </td>
                <td style="padding-right:20px;">
                    @if(!empty($v['secteur']))<div class="voie-sector">{{ $v['secteur'] }}</div>@endif
                    <div class="voie-title">{{ $v['titre'] ?? '' }}</div>
                    @if(!empty($v['pourquoi']))<div class="voie-why">{{ $v['pourquoi'] }}</div>@endif
                    @if(!empty($v['appui']))<div class="voie-appui">Appuy&eacute; par&nbsp;: {{ $v['appui'] }}</div>@endif
                    @if(!empty($v['prochaine_etape']))<div class="voie-next">&rarr; {{ $v['prochaine_etape'] }}</div>@endif
                </td>
                @if(isset($v['fit_score']))
                <td style="width:64px;">
                    <div class="voie-fit">{{ min(100, (int) $v['fit_score']) }}%</div>
                    <div class="voie-fit-cap">Correspondance</div>
                </td>
                @endif
            </tr>
        </table>
    </div>
    @endforeach

    <div class="ai-note">Analyse g&eacute;n&eacute;r&eacute;e par {{ $brand['name'] }} IA &mdash; &agrave; confronter &agrave; votre propre lecture.</div>
</div>
@endif

{{-- ── MENTIONS : clôture du document, rendue une seule fois ── --}}
@if($org['legal'])
<div class="px avoid-break mentions">
    <div class="mentions-head">Mentions</div>
    <div class="mentions-body">{{ $org['legal'] }}</div>
</div>
@endif

</body>
</html>
