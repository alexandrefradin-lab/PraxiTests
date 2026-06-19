<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title inertia>{{ config('app.name', 'PraxiQuest') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|dm-sans:300,400,500,600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-base:              #F0E8D4;
            --bg-surface:           #E5DAC2;
            --bg-elevated:          #D8CEB5;
            --color-primary:        #A67520;
            --color-primary-dark:   #7D5510;
            --color-primary-light:  #C99030;
            --text-primary:         #2A1E08;
            --text-secondary:       #5C4A1E;
            --text-muted:           #8B7355;
            --glass-bg:             rgba(240,232,212,0.85);
            --glass-border:         rgba(166,117,32,0.3);
            --shadow-gold:          0 4px 24px rgba(166,117,32,0.25);
            --border-radius:        12px;
            --font-heading:         'Playfair Display', serif;
            --font-body:            'DM Sans', sans-serif;
        }
        html, body {
            background: var(--bg-base);
            color: var(--text-primary);
            font-family: var(--font-body);
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-heading);
        }
    </style>
    @routes
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body class="font-sans antialiased">
    @inertia
</body>
</html>
