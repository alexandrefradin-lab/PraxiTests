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

        <!-- Corps -->
        <tr><td style="padding:30px 32px 12px;">
          <p style="font-size:15px;margin:0 0 14px;">Bonjour {{ $firstName }},</p>

          <p style="font-size:14px;line-height:1.8;margin:0 0 18px;color:#3A2B0F;">
            Aujourd'hui c'était le <strong>jour {{ $day }}</strong> de ton parcours — et l'action
            est encore marquée comme non faite.
          </p>

          <!-- Bloc action du jour -->
          <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                 style="background:#FEF3C7;border:1px solid #D4A017;border-radius:10px;margin-bottom:22px;">
            <tr><td style="padding:16px 20px;">
              <div style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:#92400E;margin-bottom:4px;">
                Jour {{ $day }} — en attente
              </div>
              <div style="font-size:15px;font-weight:700;color:#1C1408;">{{ $actionTitle }}</div>
            </td></tr>
          </table>

          <!-- Séparateur doux -->
          <p style="font-size:13px;line-height:1.8;color:#6B5A3E;margin:0 0 6px;">
            Pas de jugement. Les jours chargés, les doutes, la fatigue — ça arrive à tout le monde.
            Mais parfois, ce qui nous retient est plus subtil qu'on ne le croit.
          </p>

          <p style="font-size:14px;font-weight:600;color:#2A1E08;margin:16px 0 8px;">
            Et si on prenait 2 minutes pour comprendre ce qui s'est passé ?
          </p>
          <p style="font-size:13px;line-height:1.8;color:#6B5A3E;margin:0 0 22px;">
            Ce n'est pas un formulaire. C'est une conversation avec toi-même —
            quelques questions pour identifier la croyance ou l'obstacle qui
            te bloque, et trouver un premier pas minuscule.
          </p>

          <!-- CTA principal -->
          <p style="text-align:center;margin:0 0 14px;">
            <a href="{{ $beliefUrl }}"
               style="background:#A67520;color:#1C1408;text-decoration:none;font-weight:bold;font-size:15px;padding:14px 28px;border-radius:10px;display:inline-block;">
              Explorer ce qui me bloque →
            </a>
          </p>

          <!-- CTA secondaire discret -->
          <p style="text-align:center;margin:0 0 6px;">
            <a href="{{ $actionUrl }}"
               style="color:#A67520;text-decoration:underline;font-size:13px;">
              Ou accéder directement à l'action du jour
            </a>
          </p>

          <p style="font-size:12px;color:#8A7A58;line-height:1.6;margin:20px 0 0;">
            Les jours passés restent accessibles — tu peux rattraper l'action de jour {{ $day }}
            jusqu'à demain soir. Après, le parcours avance mais la case reste cochable.
          </p>
        </td></tr>

        <!-- Footer -->
        <tr><td style="padding:16px 32px 26px;border-top:1px solid #E5DAC2;">
          <p style="font-size:11px;color:#8A7A58;line-height:1.6;margin:0;">
            Tu reçois ce message car tu participes au parcours <em>{{ $pluginLabel }}</em> sur PraxiQuest.
          </p>
          <p style="font-size:12px;color:#888;text-align:center;margin-top:20px;">
            <a href="{{ $unsubscribeUrl }}" style="color:#8A7A58;">Se désabonner des notifications</a>
          </p>
        </td></tr>

      </table>
    </td></tr>
  </table>
</body>
</html>
