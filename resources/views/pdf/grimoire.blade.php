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
    $velin     = '#E5DAC2';
    $hair      = '#CBBE9E';
    $primary   = $brand['primary'];
    $secondary = $brand['secondary'];
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<style>
    @page { margin: 28px 34px; }
    * { font-family: DejaVu Sans, sans-serif; }
    body { color: {{ $ink }}; font-size: 11px; line-height: 1.55; }
    h1, h2, h3 { font-family: DejaVu Serif, serif; color: {{ $primary }}; margin: 0; }
    .cover { text-align: center; padding: 40px 0 26px; border-bottom: 2px solid {{ $primary }}; }
    .kicker { font-size: 10px; letter-spacing: 3px; text-transform: uppercase; color: {{ $secondary }}; }
    .cover h1 { font-size: 30px; margin: 8px 0 4px; }
    .cover .tagline { font-style: italic; color: {{ $inkSoft }}; font-size: 11px; }
    .meta { margin-top: 14px; font-size: 10px; color: {{ $inkSoft }}; }
    .chips { margin-top: 10px; }
    .chip { display: inline-block; border: 1px solid {{ $hair }}; border-radius: 10px; padding: 2px 8px; margin: 2px; font-size: 9px; color: {{ $inkSoft }}; }
    .section { margin-top: 22px; }
    .section h2 { font-size: 15px; border-bottom: 1px solid {{ $hair }}; padding-bottom: 4px; margin-bottom: 8px; }
    .para { margin-bottom: 8px; text-align: justify; }
    .voie { border: 1px solid {{ $hair }}; border-left: 3px solid {{ $primary }}; background: {{ $velin }}; padding: 8px 10px; margin-bottom: 8px; }
    .voie-head { font-family: DejaVu Serif, serif; font-size: 12px; color: {{ $ink }}; font-weight: bold; }
    .voie-fit { float: right; font-size: 10px; color: {{ $secondary }}; font-weight: bold; }
    .voie-sector { font-size: 9px; color: {{ $inkSoft }}; text-transform: uppercase; letter-spacing: 1px; }
    .voie-why { margin: 4px 0; }
    .voie-appui { font-size: 9px; color: {{ $inkSoft }}; }
    .voie-next { font-size: 10px; margin-top: 4px; }
    .voie-next b { color: {{ $primary }}; }
    .footer { margin-top: 26px; border-top: 1px solid {{ $hair }}; padding-top: 8px; font-size: 8px; color: {{ $inkSoft }}; text-align: center; }
</style>
</head>
<body>
    <div class="cover">
        <div class="kicker">Le Grimoire · Relecture globale</div>
        <h1>{{ $candidate }}</h1>
        @if($brand['tagline'])<div class="tagline">{{ $brand['tagline'] }}</div>@endif
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
