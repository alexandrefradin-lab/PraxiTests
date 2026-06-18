<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $subjectLine ?? config('app.name') }}</title>
</head>
<body style="margin:0;padding:0;background:#f9fafb;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:#111827">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f9fafb;padding:32px 0">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background:#ffffff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.06);overflow:hidden">
                    <tr>
                        <td style="padding:32px 40px 0 40px">
                            <a href="{{ config('app.url') }}" style="text-decoration:none;color:inherit">
                                <strong style="font-size:18px;letter-spacing:-0.02em">{{ config('app.name', 'PraxiQuest') }}</strong>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 40px 40px 40px;line-height:1.6;font-size:15px">
                            {!! strip_tags($html, '<p><br><a><b><i><strong><em><ul><ol><li><h2><h3><blockquote><span>') !!}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 40px 32px 40px;font-size:11px;color:#9ca3af;border-top:1px solid #e5e7eb;padding-top:24px">
                            Tu reçois cet email parce que tu as un compte sur {{ config('app.name') }}.
                            <a href="{{ config('app.url') }}/email/unsubscribe" style="color:#9ca3af">Se désabonner</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
