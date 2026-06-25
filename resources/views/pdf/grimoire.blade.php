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
    $parchment = '#F0E8D4';   // conservé pour textes sur fond sombre (cover)
    $velin     = '#F8F7F4';   // surface cards — neutre professionnel
    $stone     = '#EEECE7';   // fond tracks / élevé
    $hair      = '#E0DBD0';   // filets discrets neutres
    $primary   = $brand['primary'];
    $secondary = $brand['secondary'];
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<style>
    /* Polices embarquées (OFL) — Lora (titres) + Lato (corps). Repli DejaVu. */
    {{-- embedFonts=false (repli contrôleur si cache polices inaccessible) → DejaVu. --}}
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

    @page { margin: 28px 34px; }
    * { font-family: 'Lato', DejaVu Sans, sans-serif; }
    body { color: {{ $ink }}; font-size: 11px; line-height: 1.55; background: #FFFFFF; }
    h1, h2, h3 { font-family: 'Lora', DejaVu Serif, serif; color: {{ $primary }}; margin: 0; }
    /* Cover — carte sombre style consulting (cohérent avec results.blade.php) */
    .cover { text-align: center; padding: 42px 36px 36px;
        background: {{ $brand['accent'] }}; border: 1.5px solid {{ $primary }}; border-radius: 14px;
        margin-bottom: 24px; }
    .cover h1 { font-size: 30px; margin: 8px 0 4px; color: #FFFFFF; }
    .cover .kicker { font-size: 9px; letter-spacing: 3px; text-transform: uppercase; color: {{ $primary }}; }
    .cover .tagline { font-style: italic; color: #C8B48A; font-size: 11px; }
    .cover .meta { margin-top: 14px; font-size: 10px; color: #B9A87E; }
    .cover .chips { margin-top: 10px; }
    .cover .chip { display: inline-block; border: 1px solid rgba(166,117,32,0.4); border-radius: 10px;
        padding: 2px 8px; margin: 2px; font-size: 9px; color: #C8B48A; background: rgba(255,255,255,0.06); }
    .kicker { font-size: 10px; letter-spacing: 3px; text-transform: uppercase; color: {{ $secondary }}; }
    .meta { margin-top: 14px; font-size: 10px; color: {{ $inkSoft }}; }
    .chips { margin-top: 10px; }
    .chip { display: inline-block; border: 1px solid {{ $hair }}; border-radius: 10px; padding: 2px 8px; margin: 2px; font-size: 9px; color: {{ $inkSoft }}; }
    .section { margin-top: 22px; }
    .section h2 { font-size: 15px; border-bottom: 1px solid {{ $hair }}; padding-bottom: 4px; margin-bottom: 8px; }
    .para { margin-bottom: 8px; text-align: justify; }
    .voie { border: 1px solid {{ $hair }}; border-left: 3px solid {{ $primary }}; background: {{ $velin }}; padding: 10px 12px; margin-bottom: 8px; border-radius: 0 6px 6px 0; }
    .voie-head { font-family: 'Lora', DejaVu Serif, serif; font-size: 12px; color: {{ $ink }}; font-weight: bold; }
    .voie-fit { float: right; font-size: 10px; color: {{ $secondary }}; font-weight: bold; }
    .voie-sector { font-size: 9px; color: {{ $inkSoft }}; text-transform: uppercase; letter-spacing: 1px; }
    .voie-why { margin: 4px 0; }
    .voie-appui { font-size: 9px; color: {{ $inkSoft }}; }
    .voie-next { font-size: 10px; margin-top: 4px; }
    .voie-next b { color: {{ $primary }}; }
    .footer { margin-top: 26px; border-top: 1px solid {{ $hair }}; padding-top: 8px; font-size: 8px; color: {{ $inkSoft }}; text-align: center; }

    /* ── Rendu d'excellence — sceau, ornement, filets or ── */
    .cover h1 { font-size: 32px; letter-spacing: .3px; }
    .section h2 { color: {{ $brand['accent'] }}; border-bottom: 1.5px solid {{ $primary }}; }
    .seal { display: inline-block; width: 50px; height: 50px; border-radius: 25px;
        border: 1.5px solid {{ $primary }}; background: {{ $brand['accent'] }}; }
    .seal table { width: 50px; height: 50px; border-collapse: collapse; }
    .seal td { padding: 0; text-align: center; vertical-align: middle; line-height: 1;
        font-family: 'Lora', DejaVu Serif, serif; font-weight: bold; font-size: 21px; color: {{ $primary }}; }
    .ornament { color: {{ $primary }}; font-family: DejaVu Sans Mono, monospace;
        font-size: 9px; letter-spacing: 8px; margin-top: 12px; }
</style>
</head>
<body>
    <div class="cover">
        <div class="seal"><table><tr><td>Q</td></tr></table></div>
        <div class="kicker" style="margin-top:14px;">Le Grimoire · Relecture globale</div>
        <h1>{{ $candidate }}</h1>
        @if($brand['tagline'])<div class="tagline">{{ $brand['tagline'] }}</div>@endif
        <div class="ornament">✦&nbsp;&nbsp;◆&nbsp;&nbsp;✦</div>
        <div class="meta">
            @if($statut){{ $statut }} · @endif
            Relecture générée le {{ $date->format('d/m/Y') }} · {{ count($tests) }} épreuve{{ count($tests) > 1 ? 's' : '' }} croisée{{ count($tests) > 1 ? 's' : '' }}
        </div>
        @if(count($tests))
        <div class="chips">
            @foreach($tests as $t)<span class="chip">{{ $t['test'] ?? 'Test' }}</span>@endforeach
        </div>
        @endif
    </div>

    @if($synthesis)
    <div class="section">
        <h2>Le fil conducteur</h2>
        @foreach(array_filter(explode("\n", $synthesis), fn($p) => trim($p) !== '') as $para)
            <div class="para">{{ trim($para) }}</div>
        @endforeach
    </div>
    @endif

    @if(count($voies))
    <div class="section">
        <h2>Tes Voies Possibles</h2>
        @foreach($voies as $i => $v)
            <div class="voie">
                @if(isset($v['fit_score']))<span class="voie-fit">{{ $v['fit_score'] }}%</span>@endif
                <span class="voie-head">{{ $i + 1 }}. {{ $v['titre'] ?? '' }}</span>
                @if(!empty($v['secteur']))<div class="voie-sector">{{ $v['secteur'] }}</div>@endif
                @if(!empty($v['pourquoi']))<div class="voie-why">{{ $v['pourquoi'] }}</div>@endif
                @if(!empty($v['appui_tests']) && is_array($v['appui_tests']))
                    <div class="voie-appui">Appuyé par : {{ implode(', ', $v['appui_tests']) }}</div>
                @endif
                @if(!empty($v['prochaine_etape']))
                    <div class="voie-next"><b>Prochaine étape —</b> {{ $v['prochaine_etape'] }}</div>
                @endif
            </div>
        @endforeach
    </div>
    @endif

    <div class="footer">
        {{ $org['legal'] }}
        @if($org['advisor'] || $org['email'])
            <br>{{ $org['advisor'] }}@if($org['advisor'] && $org['email']) · @endif{{ $org['email'] }}
        @endif
    </div>
</body>
</html>
