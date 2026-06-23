<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Désabonnement confirmé — {{ $appName }}</title>
</head>
<body style="margin:0;padding:0;background:#f9fafb;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:#111827">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f9fafb;padding:48px 0">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellspacing="0" cellpadding="0" style="background:#ffffff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.06);overflow:hidden">
                    <tr>
                        <td style="padding:40px 40px 24px 40px;text-align:center">
                            <strong style="font-size:20px;letter-spacing:-0.02em">{{ $appName }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 40px 40px 40px;line-height:1.6;font-size:15px;text-align:center">
                            <h1 style="font-size:18px;margin:0 0 16px">Désabonnement confirmé</h1>
                            <p style="margin:0 0 8px">
                                @if($email)<strong>{{ $email }}</strong> ne recevra plus@else Tu ne recevras plus @endif
                                d'emails marketing de notre part.
                            </p>
                            <p style="margin:16px 0 0;font-size:13px;color:#6b7280">
                                Tu continueras à recevoir les emails nécessaires au fonctionnement de ton compte
                                (sécurité, facturation, résultats de tests).
                            </p>
                            <p style="margin:24px 0 0">
                                <a href="{{ $appUrl }}" style="color:#1f4e79;text-decoration:none">Retour sur {{ $appName }}</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
