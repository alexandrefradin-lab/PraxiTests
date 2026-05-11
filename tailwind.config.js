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
                sans: ['Inter', 'ui-sans-serif', 'system-ui'],
            },
            colors: {
                praxi: {
                    50:  '#EEF2FF',
                    500: '#4F46E5',
                    600: '#4338CA',
                    700: '#3730A3',
                },
            },
        },
    },
    plugins: [forms, typography],
}
