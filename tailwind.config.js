/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './**/*.php',
    './assets/js/**/*.js',
    './resources/css/**/*.css',
  ],
  darkMode: ['selector', '[data-theme="dark"]'],
  theme: {
    extend: {
      colors: {
        org: {
          bg: 'var(--org-color-bg)',
          surface: 'var(--org-color-surface)',
          card: 'var(--org-color-card)',
          primary: 'var(--org-color-primary)',
          'primary-soft': 'var(--org-color-primary-soft)',
          text: 'var(--org-color-text)',
          muted: 'var(--org-color-muted)',
          border: 'var(--org-color-border)',
          navy: 'var(--org-color-navy)',
          'navy-deep': 'var(--org-color-navy-deep)',
          accent: 'var(--org-color-accent)',
        },
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', '-apple-system', 'Segoe UI', 'sans-serif'],
        display: ['Public Sans', 'Inter', 'system-ui', 'sans-serif'],
      },
      fontSize: {
        'org-xs': ['0.75rem', { lineHeight: '1.45' }],
        'org-sm': ['0.875rem', { lineHeight: '1.55' }],
        'org-base': ['1rem', { lineHeight: '1.6' }],
        'org-lg': ['1.125rem', { lineHeight: '1.55' }],
        'org-xl': ['1.25rem', { lineHeight: '1.45' }],
        'org-2xl': ['clamp(1.35rem, 1.1rem + 0.8vw, 1.85rem)', { lineHeight: '1.25', fontWeight: '800' }],
        'org-3xl': ['clamp(1.75rem, 1.4rem + 1.2vw, 2.5rem)', { lineHeight: '1.15', fontWeight: '800' }],
        'org-display': ['clamp(2rem, 4vw, 3.25rem)', { lineHeight: '1.08', fontWeight: '800', letterSpacing: '-0.03em' }],
      },
      spacing: {
        'org-1': '0.25rem',
        'org-2': '0.5rem',
        'org-3': '0.75rem',
        'org-4': '1rem',
        'org-5': '1.25rem',
        'org-6': '1.5rem',
        'org-8': '2rem',
        'org-10': '2.5rem',
        'org-12': '3rem',
        'org-section': 'clamp(3rem, 6vw, 4.5rem)',
      },
      maxWidth: {
        layout: '1280px',
      },
      borderRadius: {
        org: '0.75rem',
        'org-lg': '1rem',
        'org-xl': '1.25rem',
      },
      boxShadow: {
        org: 'var(--org-shadow)',
        'org-sm': 'var(--org-shadow-sm)',
        'org-lg': 'var(--org-shadow-lg)',
      },
      transitionDuration: {
        org: '280ms',
      },
    },
  },
  /* Bootstrap memakai class .collapse — jangan timpa dengan Tailwind visibility:collapse */
  blocklist: ['collapse', '!collapse'],
  plugins: [],
  safelist: [
    'lg:hidden',
    'lg:block',
    'hidden',
    'md:block',
  ],
};
