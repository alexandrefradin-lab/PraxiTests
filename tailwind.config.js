import forms from '@tailwindcss/forms'
import typography from '@tailwindcss/typography'

export default {
    content: [
        './resources/**/*.{js,vue,blade.php}',
        './app/**/*.php',
        './plugins/**/resources/**/*.{js,vue,blade.php}',
    ],
    theme: {
        extend: {
            fontFamily: {
                display: ['Space Grotesk', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                sans:    ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'sans-serif'],
                mono:    ['Space Mono', 'ui-monospace', 'SFMono-Regular', 'monospace'],
            },
            colors: {
                ac: {
                    base:         '#F0E8D4',
                    surface:      '#E5DAC2',
                    elevated:     '#D8CEB5',
                    gold:         '#A67520',
                    'gold-dark':  '#7D5510',
                    crimson:      '#7B1515',
                    ink:          '#1C1408',
                    success:      '#3A6B48',
                    danger:       '#B03020',
                    signal:       '#0A7FA0',
                    text:         '#2A1E08',
                    muted:        '#6B5A3E',
                },
                praxi: {
                    navy:   '#1C1408',
                    gold:   '#A67520',
                    cream:  '#F0E8D4',
                    50:     '#F0E8D4',
                    500:    '#A67520',
                    700:    '#7D5510',
                },
            },
            boxShadow: {
                'ac-primary':  '0 4px 20px rgba(166,117,32,0.25)',
                'ac-card':     '0 2px 12px rgba(42,30,8,0.10)',
                'ac-elevated': '0 8px 32px rgba(42,30,8,0.15)',
            },
            backgroundImage: {
                'ac-parchment': 'linear-gradient(135deg, #F0E8D4 0%, #E8DFCA 50%, #F0E8D4 100%)',
                'ac-gold-grad': 'linear-gradient(90deg, #7D5510, #A67520, #C99030)',
            },
        },
    },
    plugins: [forms, typography],
}
