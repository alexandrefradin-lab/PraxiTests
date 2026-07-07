<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ auth()->user()?->ui_theme ?? 'medieval' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title inertia>{{ config('app.name', 'PraxiQuest') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|dm-sans:300,400,500,600&display=swap" rel="stylesheet">
    @routes
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* ════════════════════════════════════════════════════════════════
           HACK LEGACY — landing uniquement.
           Les tokens du design system sont définis dans resources/css/app.css
           (source unique). Ce bloc ne sert QU'À neutraliser les classes
           Tailwind (indigo/slate/emerald) de l'ancien bundle compilé encore
           utilisé par la landing marketing. Ne pas y remettre de tokens.
           ════════════════════════════════════════════════════════════════ */

        /* === Override Tailwind classes de l'ancien bundle compilé === */
        .bg-white               { background-color: #F0E8D4 !important; }
        .bg-slate-50            { background-color: #E5DAC2 !important; }
        .bg-slate-100           { background-color: #E5DAC2 !important; }
        .text-slate-900         { color: #2A1E08 !important; }
        .text-slate-700         { color: #3D2E0F !important; }
        .text-slate-600         { color: #5C4A1E !important; }
        .text-slate-500         { color: #8B7355 !important; }
        .text-slate-400         { color: #B8A88A !important; }
        .border-slate-100       { border-color: rgba(166,117,32,0.2) !important; }
        .border-slate-200       { border-color: rgba(166,117,32,0.3) !important; }

        /* Gradient accents → or parchment */
        .from-indigo-500        { --tw-gradient-from: #A67520 !important; }
        .to-violet-500          { --tw-gradient-to: #C99030 !important; }
        .from-emerald-500       { --tw-gradient-from: #7D5510 !important; }
        .to-teal-500            { --tw-gradient-to: #A67520 !important; }
        .from-amber-500         { --tw-gradient-from: #C99030 !important; }
        .to-rose-500            { --tw-gradient-to: #A67520 !important; }

        /* Gradient text → or parchment (bg-clip-text) */
        .bg-clip-text.text-transparent {
            -webkit-text-fill-color: #A67520 !important;
            background-image: none !important;
        }

        /* Hero section gradient background */
        .bg-gradient-to-br, .bg-gradient-to-r {
            background-image: none !important;
        }

        /* Boutons Tailwind */
        .bg-indigo-600,
        .bg-indigo-500          { background-color: #A67520 !important; }
        .hover\:bg-indigo-700:hover { background-color: #7D5510 !important; }
        .text-indigo-600        { color: #A67520 !important; }
        .border-indigo-600      { border-color: #A67520 !important; }
        .ring-indigo-500        { --tw-ring-color: #A67520 !important; }

        /* Pillar card accents */
        .rounded-full.bg-gradient-to-br { background: #A67520 !important; }

        /* Logo icon */
        .from-indigo-500.to-emerald-500 { background: linear-gradient(135deg, #A67520, #C99030) !important; }
    </style>
    @inertiaHead
</head>
<body class="font-sans antialiased">
    @inertia
</body>
</html>
