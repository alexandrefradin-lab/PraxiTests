<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation à passer un test</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        .header { background: #1E3A5F; color: white; padding: 32px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .body { padding: 32px; }
        .cta { display: block; margin: 32px auto; text-align: center; }
        .btn { display: inline-block; background: #C9A84C; color: white; padding: 16px 32px; border-radius: 6px; text-decoration: none; font-size: 16px; font-weight: bold; }
        .footer { background: #f4f4f4; padding: 16px 32px; font-size: 12px; color: #888; text-align: center; }
        .expires { background: #FFF8E1; border-left: 4px solid #C9A84C; padding: 12px 16px; margin: 16px 0; border-radius: 0 4px 4px 0; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>{{ config('app.name') }}</h1>
        <p style="margin:8px 0 0; opacity:.85">Vous avez reçu une invitation</p>
    </div>
    <div class="body">
        <p>Bonjour,</p>
        <p>Vous avez été invité(e) à passer <strong>{{ $invitation->test->name ?? 'un test' }}</strong>.</p>
        @php $customMessage = $invitation->metadata['message'] ?? null; @endphp
        @if(!empty($customMessage))
        <p style="background:#f9f9f9;padding:12px;border-radius:4px;font-style:italic">
            "{{ $customMessage }}"
        </p>
        @endif
        @if($invitation->expires_at)
        <div class="expires">
            <strong>Ce lien expire le {{ $invitation->expires_at->format('d/m/Y à H:i') }}</strong>
        </div>
        @endif
        <div class="cta">
            <a href="{{ url('/i/' . $invitation->token) }}" class="btn">Commencer le test &rarr;</a>
        </div>
        <p style="color:#888;font-size:13px">Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :<br>
        <a href="{{ url('/i/' . $invitation->token) }}">{{ url('/i/' . $invitation->token) }}</a></p>
    </div>
    <div class="footer">
        <p>Cet email vous a été envoyé car vous avez été invité(e) à passer un test sur {{ config('app.name') }}.</p>
        <p>Si vous pensez avoir reçu cet email par erreur, ignorez-le simplement.</p>
    </div>
</div>
</body>
</html>
