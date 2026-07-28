import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
  prefix: 'fps-',
  content: [
    './fancyn-plugin-starter.php',
    './includes/**/*.php',
    './templates/**/*.php',
    './assets/src/**/*.{js,ts,jsx,tsx}',
  ],
  corePlugins: {
    preflight: false,
  },
  theme: {
    extend: {
      colors: {
        surface: {
          page: '#F4F4F3',
          DEFAULT: '#FFFFFF',
          muted: '#F8F8F7',
          selected: '#FFF3EE',
        },
        ink: {
          DEFAULT: '#171717',
          secondary: '#5F5F5B',
          muted: '#8A8A84',
          inverse: '#FFFFFF',
        },
        brand: {
          DEFAULT: '#F4511E',
          hover: '#DC4317',
          soft: '#FFF0EA',
          border: '#FF6A3D',
        },
        success: { DEFAULT: '#16A36A', soft: '#EAF8F1' },
        warning: { DEFAULT: '#D98E04', soft: '#FFF7E3' },
        danger: { DEFAULT: '#D9363E', soft: '#FDECEE' },
        info: { DEFAULT: '#2563EB', soft: '#EDF4FF' },
        line: { DEFAULT: '#E2E2DF', strong: '#CFCFCA' },
      },
      borderRadius: {
        control: '10px',
        card: '18px',
      },
      boxShadow: {
        card: '0 1px 2px rgba(0,0,0,.03), 0 8px 24px rgba(0,0,0,.04)',
        popover: '0 12px 30px rgba(0,0,0,.10)',
      },
      fontFamily: {
        sans: [
          'Inter',
          'ui-sans-serif',
          'system-ui',
          '-apple-system',
          'BlinkMacSystemFont',
          'Segoe UI',
          'sans-serif',
        ],
      },
    },
  },
  plugins: [forms({ strategy: 'class' })],
};
