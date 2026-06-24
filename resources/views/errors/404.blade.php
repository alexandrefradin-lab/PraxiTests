<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contrée introuvable — PraxiQuest</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&family=Space+Mono:wght@400;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg-base:            #F0E8D4;
            --bg-elevated:        #D8CEB5;
            --color-primary:      #A67520;
            --color-primary-dark: #7D5510;
            --text-primary:       #2A1E08;
            --text-secondary:     #5C4A1E;
            --text-muted:         #8B7355;
            --glass-border:       rgba(166,117,32,0.28);
        }

        body {
            background: var(--bg-base);
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        /* Vignette subtile aux coins */
        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background: radial-gradient(ellipse at center, transparent 55%, rgba(42,30,8,0.08) 100%);
            pointer-events: none;
        }

        /* Cercles décoratifs en fond */
        .bg-ring {
            position: fixed;
            border-radius: 50%;
            border: 1px solid rgba(166,117,32,0.12);
            pointer-events: none;
        }
        .bg-ring-1 { width: 500px; height: 500px; top: 50%; left: 50%; transform: translate(-50%,-50%); }
        .bg-ring-2 { width: 750px; height: 750px; top: 50%; left: 50%; transform: translate(-50%,-50%); }
        .bg-ring-3 { width: 1050px; height: 1050px; top: 50%; left: 50%; transform: translate(-50%,-50%); }

        .wrapper {
            position: relative;
            z-index: 1;
            text-align: center;
            max-width: 540px;
            width: 100%;
            animation: fadeUp 0.6s ease both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Logo */
        .logo-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            margin-bottom: 3rem;
        }
        .logo-name {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 15px;
            font-weight: 600;
            color: var(--text-primary);
            letter-spacing: -0.01em;
        }
        .logo-tagline {
            font-family: 'Space Mono', monospace;
            font-size: 8px;
            color: var(--color-primary);
            letter-spacing: 0.14em;
            text-transform: uppercase;
            margin-top: 2px;
        }

        /* Code 404 */
        .error-code {
            font-family: 'Space Mono', monospace;
            font-size: clamp(6rem, 20vw, 9.5rem);
            font-weight: 700;
            color: var(--color-primary);
            letter-spacing: -0.05em;
            line-height: 0.9;
            opacity: 0.2;
            margin-bottom: 0.5rem;
            user-select: none;
        }

        /* Diviseur or */
        .divider {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 1.75rem 0;
        }
        .divider-line {
            flex: 1;
            height: 1px;
        }
        .divider-l { background: linear-gradient(to right, transparent, var(--color-primary)); opacity: 0.5; }
        .divider-r { background: linear-gradient(to left,  transparent, var(--color-primary)); opacity: 0.5; }

        /* Texte */
        .title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: clamp(1.4rem, 4vw, 1.9rem);
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.02em;
            line-height: 1.2;
            margin-bottom: 0.875rem;
        }
        .subtitle {
            font-size: 0.9375rem;
            color: var(--text-muted);
            line-height: 1.65;
            margin-bottom: 2.25rem;
        }

        /* Bouton retour */
        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--color-primary);
            color: var(--bg-base);
            font-family: 'Space Grotesk', sans-serif;
            font-size: 13.5px;
            font-weight: 600;
            padding: 11px 24px;
            border-radius: 10px;
            text-decoration: none;
            transition: background 0.15s ease, transform 0.15s ease, box-shadow 0.15s ease;
            box-shadow: 0 4px 16px rgba(166,117,32,0.22);
        }
        .btn-home:hover {
            background: var(--color-primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(166,117,32,0.3);
        }
        .btn-home svg {
            flex-shrink: 0;
        }

        /* Note de bas */
        .footer-note {
            margin-top: 3rem;
            font-family: 'Space Mono', monospace;
            font-size: 10px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--text-muted);
            opacity: 0.55;
        }
    </style>
</head>
<body>

    <!-- Anneaux décoratifs -->
    <div class="bg-ring bg-ring-1"></div>
    <div class="bg-ring bg-ring-2"></div>
    <div class="bg-ring bg-ring-3"></div>

    <div class="wrapper">

        <!-- Logo -->
        <a href="/" class="logo-link">
            <svg width="32" height="32" viewBox="0 0 38 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="19" cy="19" r="17.5" stroke="#A67520" stroke-width="1"/>
                <circle cx="19" cy="19" r="13" stroke="#A67520" stroke-width="0.5" opacity="0.5"/>
                <polygon points="19,6 20.4,18 19,21 17.6,18" fill="#A67520"/>
                <polygon points="19,32 20.4,20 19,17 17.6,20" fill="#A67520" opacity="0.35"/>
                <circle cx="19" cy="19" r="2" fill="#A67520"/>
                <circle cx="19" cy="19" r="1" fill="#F0E8D4"/>
            </svg>
            <div>
                <div class="logo-name">PraxiQuest</div>
                <div class="logo-tagline">Voyage intérieur</div>
            </div>
        </a>

        <!-- Code erreur -->
        <div class="error-code">404</div>

        <!-- Diviseur -->
        <div class="divider">
            <div class="divider-line divider-l"></div>
            <svg width="11" height="11" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M8 0L9.6 6.4L16 8L9.6 9.6L8 16L6.4 9.6L0 8L6.4 6.4L8 0Z" fill="#A67520" opacity="0.45"/>
            </svg>
            <div class="divider-line divider-r"></div>
        </div>

        <!-- Message -->
        <h1 class="title">Cette contrée n'existe pas</h1>
        <p class="subtitle">
            La page que tu cherches s'est perdue dans les brumes.<br>
            Elle a peut-être été déplacée, supprimée,<br>ou n'a jamais existé dans ce royaume.
        </p>

        <!-- CTA -->
        <a href="/" class="btn-home">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 18l-6-6 6-6"/>
            </svg>
            Retourner à la Quête
        </a>

        <p class="footer-note">PraxiQuest — Évaluer. Orienter. Transformer.</p>
    </div>

</body>
</html>
