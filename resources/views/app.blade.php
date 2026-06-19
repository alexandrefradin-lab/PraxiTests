<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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
        /* Override Vite compiled vars — thème Assassin's Creed Parchment */
        :root {
            --pt-cream:           #F0E8D4;
            --pt-cream-dark:      #E5DAC2;
            --pt-white:           #FAF6ED;
            --pt-navy:            #2A1E08;
            --pt-navy-mid:        #3D2E0F;
            --pt-navy-light:      #5C4A1E;
            --pt-gold:            #A67520;
            --pt-gold-hover:      #7D5510;
            --pt-gold-border:     rgba(166,117,32,0.4);
            --pt-gold-pale:       rgba(166,117,32,0.12);
            --pt-text:            #2A1E08;
            --pt-text-light:      #5C4A1E;
            --pt-text-muted:      #8B7355;
            --pt-text-ghost:      #B8A88A;
            --pt-border:          rgba(166,117,32,0.15);
            --pt-border-mid:      rgba(166,117,32,0.3);
            --pt-border-strong:   rgba(166,117,32,0.55);
            --pt-shadow-xs:       0 1px 3px rgba(42,30,8,0.08);
            --pt-shadow-sm:       0 2px 8px rgba(42,30,8,0.12);
            --pt-shadow-md:       0 4px 16px rgba(42,30,8,0.15);
            --pt-shadow-lg:       0 8px 32px rgba(42,30,8,0.2);
        }
    </style>
    @inertiaHead
</head>
<body class="font-sans antialiased">
    @inertia
</body>
</html>
