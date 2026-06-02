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
                sans:    ['DM Sans', 'ui-sans-serif', 'system-ui', '-apple-system'],
                display: ['Playfair Display', 'ui-serif', 'Georgia', 'serif'],
            },
            colors: {
                praxi: {
                    navy:  '#1B2B3A',
                    gold:  '#B8913A',
                    cream: '#F7F5F0',
                    50:    '#FBF5E8',
                    500:   '#B8913A',
                    700:   '#8A6A20',
                },
            },
        },
    },
    plugins: [forms, typography],
}
