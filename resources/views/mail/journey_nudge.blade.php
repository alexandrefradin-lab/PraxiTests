<!DOCTYPE html>
<html lang="fr">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;background:#F0E8D4;font-family:Segoe UI,Helvetica,Arial,sans-serif;color:#2A1E08;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F0E8D4;padding:28px 0;">
    <tr><td align="center">
      <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="background:#FFFDF7;border:1px solid #CBBE9E;border-radius:14px;overflow:hidden;">

        <!-- Header -->
        <tr><td style="background:#1C1408;padding:22px 32px;">
          <div style="font-size:18px;font-weight:bold;color:#F0E8D4;">Praxi<span style="color:#A67520;">Quest</span></div>
          <div style="font-size:11px;color:#B9A87E;margin-top:2px;letter-spacing:1px;text-transform:uppercase;">{{ $pluginLabel }}</div>
        </td></tr>

        <!-- Bloc streak (aversion à la perte) — affiché uniquement si streak ≥ 1 -->
        @if ($streak >= 1)
        <tr><td style="background:#FFF8E7;border-bottom:1px solid #F0D88A;padding:14px 32px;">
          <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
            <tr>
              <td style="font-size:28px;line-height:1;width:36px;">🔥</td>
              <td style="padding-left:10px;">
                <div style="font-size:13px;font-weight:700;color:#92400E;text-transform:uppercase;letter-spacing:.05em;">
                  Streak actif
                </div>
                <div style="font-size:22px;font-weight:800;color:#1C1408;line-height:1.2;">
                  {{ $streak }} jour{{ $streak > 1 ? 's' : '' }} consécutif{{ $streak > 1 ? 's' : '' }}
                </div>
              </td>
              <td align="right" style="font-size:11px;color:#92400E;font-weight:600;vertical-align:middle;">
                ⚠️ Se ferme<br>à minuit
              </td>
            </tr>
          </table>
        </td></tr>
        @endif

        <!-- Corps -->
        <tr><td style="padding:28px 32px 16px;">

          <p style="font-size:15px;margin:0 0 18px;">Bonjour {{ $firstName }},</p>

          <!-- Progression (gradient d'objectif) -->
          <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                 style="background:#F5F0E8;border:1px solid #CBBE9E;border-radius:10px;margin-bottom:22px;">
            <tr><td style="padding:14px 18px;">
              <div style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:#6B5A3E;margin-bottom:8px;">
                Ton parcours
              </div>
              <div style="background:#E8E0CC;border-radius:4px;height:8px;overflow:hidden;">
                <div style="background:#A67520;height:8px;border-radius:4px;width:{{ round((($day - 1) / $totalDays) * 100) }}%;"></div>
              </div>
              <div style="display:flex;justify-content:space-between;margin-top:6px;">
                <span style="font-size:12px;font-weight:700;color:#1C1408;">Jour {{ $day - 1 > 0 ? $day - 1 : 0 }} accompli</span>
                <span style="font-size:12px;color:#8A7A58;">{{ $totalDays - ($day - 1) }} jours restants</span>
              </div>
            </td></tr>
          </table>

          <!-- Action du jour -->
          <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                 style="background:#FEF3C7;border:1px solid #D4A017;border-radius:10px;margin-bottom:24px;">
            <tr><td style="padding:16px 20px;">
              <div style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:#92400E;margin-bottom:4px;">
                Jour {{ $day }} — en attente ce soir
              </div>
              <div style="font-size:15px;font-weight:700;color:#1C1408;">{{ $actionTitle }}</div>
            </td></tr>
          </table>

          @if ($streak >= 2)
          <!-- Message identité (Coach) — si streak significatif -->
          <p style="font-size:14px;line-height:1.8;color:#3A2B0F;margin:0 0 22px;">
            Tu construis quelque chose de réel, {{ $firstName }} — {{ $streak }} jours sans briser la chaîne.
            Aujourd'hui, une seule action maintient tout ça vivant.
          </p>
          @else
          <!-- Message empathique standard -->
          <p style="font-size:14px;line-height:1.8;color:#3A2B0F;margin:0 0 22px;">
            Pas de jugement. Mais cette case reste ouverte jusqu'à minuit —
            et la refermer prend moins de temps que lire cet email.
          </p>
          @endif

          <!-- CTA PRIMAIRE : accès direct (friction minimale) -->
          <p style="text-align:center;margin:0 0 12px;">
            <a href="{{ $actionUrl }}"
               style="background:#A67520;color:#FFFDF7;text-decoration:none;font-weight:bold;font-size:15px;padding:14px 32px;border-radius:10px;display:inline-block;letter-spacing:.02em;">
              Faire l'action du jour → {{ $day }}/{{ $totalDays }}
            </a>
          </p>

          <!-- Zeigarnik : mention de la fermeture -->
          <p style="text-align:center;font-size:11px;color:#8A7A58;margin:0 0 20px;">
            Cette action se referme automatiquement à minuit.
          </p>

          <!-- Séparateur -->
          <hr style="border:none;border-top:1px solid #E5DAC2;margin:20px 0;">

          <!-- CTA secondaire : croyances (exploration optionnelle) -->
          <p style="font-size:13px;color:#6B5A3E;line-height:1.7;margin:0 0 10px;">
            Si quelque chose te retient — une pensée, une résistance, un manque d'énergie —
            deux minutes de questionnement peuvent l'éclairer :
          </p>
          <p style="text-align:center;margin:0 0 4px;">
            <a href="{{ $beliefUrl }}"
               style="color:#A67520;text-decoration:underline;font-size:13px;font-weight:600;">
              Explorer ce qui me retient
            </a>
          </p>

        </td></tr>

        <!-- Footer -->
        <tr><td style="padding:16px 32px 26px;border-top:1px solid #E5DAC2;">
          <p style="font-size:11px;color:#8A7A58;line-height:1.6;margin:0;">
            Tu reçois ce message car tu participes au parcours <em>{{ $pluginLabel }}</em> sur PraxiQuest.
          </p>
          <p style="font-size:12px;color:#888;text-align:center;margin-top:16px;">
            <a href="{{ $unsubscribeUrl }}" style="color:#8A7A58;">Se désabonner des notifications</a>
          </p>
        </td></tr>

      </table>
    </td></tr>
  </table>
</body>
</html>
