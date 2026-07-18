PraxiQuest — {{ $pluginLabel }}

Bonjour {{ $firstName }},
@if ($streak >= 1)

🔥 Streak actif : {{ $streak }} jour{{ $streak > 1 ? 's' : '' }} consécutif{{ $streak > 1 ? 's' : '' }} — se ferme à minuit.
@endif

Ton parcours : jour {{ $day - 1 > 0 ? $day - 1 : 0 }} accompli, {{ $totalDays - ($day - 1) }} jours restants.

Jour {{ $day }} — en attente ce soir : {{ $actionTitle }}
@if ($streak >= 2)

Tu construis quelque chose de réel, {{ $firstName }} — {{ $streak }} jours sans briser la chaîne. Aujourd'hui, une seule action maintient tout ça vivant.
@else

Pas de jugement. Mais cette case reste ouverte jusqu'à minuit — et la refermer prend moins de temps que lire cet email.
@endif

Faire l'action du jour ({{ $day }}/{{ $totalDays }}) :
{{ $actionUrl }}

Cette action se referme automatiquement à minuit.

Si quelque chose te retient — une pensée, une résistance, un manque d'énergie — deux minutes de questionnement peuvent l'éclairer :
{{ $beliefUrl }}

—
Tu reçois ce message car tu participes au parcours {{ $pluginLabel }} sur PraxiQuest.
Se désabonner des notifications : {{ $unsubscribeUrl }}
