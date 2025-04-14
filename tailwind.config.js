// tailwind.config.js
import tailwindcss from 'tailwindcss'
import defaultTheme from 'tailwindcss/defaultTheme'
import forms from '@tailwindcss/forms'
import typography from '@tailwindcss/typography'

export default {
  content: [
    "./resources/**/*.{js,jsx,ts,tsx,vue,twig,php,html}",
    "./src/**/*.{php,html,twig}",
    "./templates/**/*.{html,twig}",
  ],
  darkMode: 'class',
  theme: {
    screens: {
      'xs': '375px',
      'sm': '540px',
      'md': '720px',
      'lg': '960px',
      'xl': '1140px',
      '2xl': '1550px',
    },
    container: {
      center: true,
      padding: '20px',
    },
    fontSize: {
      'xxs': ['14px', '1.6em'],
      'xs': ['16px', '1.6em'],
      'sm': ['18px', '1.6em'],
      'md': ['20px', '1.45em'],
      'lg': ['26px', '1.3em'],
      'xl': ['36px', '1.3em'],
      '2xl': ['64px', '1.1em'],
      '3xl': ['96px', '1.1em'],
    },
    extend: {
      fontFamily: {
        sans: ['Gilroy', ...defaultTheme.fontFamily.sans],
      },
      colors: {
        white: "#FFF",
        purple: "#7843E9",
        pink: "#EC4176",
        dark: "#222",
        gray: "#454545",
        darkblue: "#1E1F43",
        body: '#BDBECA',
        card: '#323359',
        primary: {
          DEFAULT: '#7843E9',
          dark: '#5c2dcc',
          light: '#9a6df0',
          ring: 'rgba(120, 67, 233, 0.5)'
        }
      },
      ringColor: ({ theme }) => ({
        primary: theme('colors.primary.ring')
      })
    }
  },
  plugins: [
    forms,
    typography
  ]
}
