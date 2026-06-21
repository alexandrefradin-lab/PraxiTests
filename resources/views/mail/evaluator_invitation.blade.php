<!DOCTYPE html>
<html lang="fr">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;background:#F0E8D4;font-family:Segoe UI,Helvetica,Arial,sans-serif;color:#2A1E08;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F0E8D4;padding:28px 0;">
    <tr><td align="center">
      <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="background:#FFFDF7;border:1px solid #CBBE9E;border-radius:14px;overflow:hidden;">
        <tr><td style="background:#1C1408;padding:22px 32px;">
          <div style="font-size:18px;font-weight:bold;color:#F0E8D4;">Praxi<span style="color:#A67520;">Quest</span></div>
          <div style="font-size:11px;color:#B9A87E;margin-top:2px;letter-spacing:1px;text-transform:uppercase;">Feedback 360°</div>
        </td></tr>
        <tr><td style="padding:30px 32px 12px;">
          <p style="font-size:15px;margin:0 0 14px;">Bonjour,</p>
          <p style="font-size:14px;line-height:1.7;margin:0 0 14px;">
            <strong>{{ $candidateName }}</strong> réalise actuellement un bilan de ses compétences
            comportementales et souhaite recueillir votre regard en tant que
            <strong>{{ $relationLabel }}</strong>.
          </p>
          <p style="font-size:14px;line-height:1.7;margin:0 0 14px;">
            Votre contribution prend <strong>moins de 10 minutes</strong> et reste
            <strong>strictement anonyme</strong> : {{ $candidateName }} ne verra que des résultats agrégés,
            jamais vos réponses individuelles.
          </p>
          <p style="text-align:center;margin:26px 0;">
            <a href="{{ $link }}" style="background:#A67520;color:#1C1408;text-decoration:none;font-weight:bold;font-size:15px;padding:14px 30px;border-radius:10px;display:inline-block;">
              Donner mon avis
            </a>
          </p>
          <p style="font-size:12px;color:#6B5A3E;line-height:1.6;margin:0 0 6px;">
            Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :
          </p>
          <p style="font-size:12px;color:#7D5510;word-break:break-all;margin:0 0 18px;">{{ $link }}</p>
        </td></tr>
        <tr><td style="padding:16px 32px 26px;border-top:1px solid #E5DAC2;">
          <p style="font-size:11px;color:#8A7A58;line-height:1.6;margin:0;">
            Ce lien vous est personnel. Vous recevez ce message car {{ $candidateName }} vous a
            désigné(e) comme évaluateur. Vos réponses sont traitées conformément au RGPD.
          </p>
        </td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
