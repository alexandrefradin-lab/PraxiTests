@php
    $testIds      = $invitation->metadata['test_ids'] ?? [];
    $invitedTests = count($testIds) > 1
        ? \App\Models\Test::whereIn('id', $testIds)->orderBy('name')->get(['id', 'name'])
        : collect();
    $link = url('/i/' . $invitation->token);
@endphp
{{ config('app.name') }} — Vous avez reçu une invitation

Bonjour{{ $invitation->first_name ? ' ' . $invitation->first_name : '' }},

@if($invitedTests->count() > 1)
Vous avez été invité(e) à passer les {{ $invitedTests->count() }} épreuves suivantes :
@foreach($invitedTests as $t)
- {{ $t->name }}
@endforeach
@else
Vous avez été invité(e) à passer {{ $invitation->test->name ?? 'un test' }}.
@endif

Ces épreuves s'inscrivent dans le cadre de votre accompagnement. Répondez spontanément — il n'y a pas de bonnes ou de mauvaises réponses. Vos résultats vous seront restitués directement dans votre espace personnel.

@if($invitation->expires_at)
Attention : ce lien expire le {{ $invitation->expires_at->format('d/m/Y à H:i') }}.
@endif

Pour commencer, ouvrez ce lien dans votre navigateur :
{{ $link }}

—
Cet email vous a été envoyé car vous avez été invité(e) à passer un test sur {{ config('app.name') }}.
Si vous pensez avoir reçu cet email par erreur, ignorez-le simplement.
