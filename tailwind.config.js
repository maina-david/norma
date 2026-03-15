const defaultTheme = require('tailwindcss/defaultTheme');
const colors = require('tailwindcss/colors');

const extendedColours = {
  'norma-gray': {
    '50': 'var(--norma-gray-50)',
    '100': 'var(--norma-gray-100)',
    '200': 'var(--norma-gray-200)',
    '300': 'var(--norma-gray-300)',
    '400': 'var(--norma-gray-400)',
    '500': 'var(--norma-gray-500)',
    '600': 'var(--norma-gray-600)',
    '700': 'var(--norma-gray-700)',
    '800': 'var(--norma-gray-800)',
    '900': 'var(--norma-gray-900)',
    '950': 'var(--norma-gray-950)',
  },
};
const safeList = [];

[
  'primary', 'negative', 'secondary', 'tertiary', 'warning', 'positive', 'dark', 'accent', 'info',
  'primary-lighter', 'negative-lighter', 'secondary-lighter', 'tertiary-lighter', 'warning-lighter', 'positive-lighter', 'dark-lighter', 'info-lighter',
  'primary-darker', 'negative-darker', 'secondary-darker', 'tertiary-darker', 'warning-darker', 'positive-darker', 'dark-darker', 'info-darker',
  'navbar', 'navbar-text', 'navbar-active',
  'sidebar-background', 'sidebar-background-sub', 'sidebar-text', 'sidebar-active',

].forEach((col) => {
  extendedColours[col] = `var(--${col})`;
  safeList.push({ pattern: new RegExp(`${col}$`) });
});

module.exports = {
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
    './resources/js/app/**/*.js',
    './resources/js/vue/**/*.js',
    './resources/js/Pages/**/*.js',
    './resources/js/Pages/**/*.vue',
    './resources/js/vue/**/*.vue',
    './resources/js/collaborate/**/*.js',
    './resources/js/collaborate/**/*.vue',
    './app/View/**/*.php',
  ],
  safelist: safeList,

  theme: {
    extend: {
      fontFamily: {
        sans: ['Figtree', ...defaultTheme.fontFamily.sans],
        serif: ['Cambria', ...defaultTheme.fontFamily.serif],
      },
      colors: extendedColours,
      listStyleType: {
        'lower-alpha': 'lower-alpha',
      },
      width: {
        'screen-50': '50vw',
        'screen-75': '75vw',
        'screen-90': '90vw',
      },
      height: {
        'screen-50': '50vh',
        'screen-75': '75vh',
        'screen-90': '90vh',
      },
      minHeight: {
        'screen-25': '25vh',
        'screen-33': '33vh',
        'screen-50': '50vh',
        'screen-66': '66vh',
        'screen-75': '75vh',
        'screen-90': '90vh',
      },
      minWidth: {
        'screen-25': '25vw',
        'screen-33': '33vw',
        'screen-50': '50vw',
        'screen-66': '66vw',
        'screen-75': '75vw',
        'screen-90': '90vw',
      },
      maxWidth: {
        'screen-25': '25vw',
        'screen-33': '33vw',
        'screen-50': '50vw',
        'screen-66': '66vw',
        'screen-75': '75vw',
        'screen-90': '90vw',
      },
      maxHeight: {
        'screen-50': '50vh',
        'screen-75': '75vh',
        'screen-90': '90vh',
      },
    },

    fontSize: {
      xs: '0.875rem',
      sm: '1rem',
      base: '1.125rem',
      lg: '1.25rem',
      xl: '1.5rem',
      '2xl': '1.875rem',
      '3xl': '2.25rem',
      '4xl': '3rem',
      '5xl': '3.75rem',
    },
  },

  // variants: {
  //    extend: {
  //        opacity: ['disabled'],
  //        width: ['hover'],
  //        transitionProperty: ['hover'],
  //    },
  // },

  plugins: [require('@tailwindcss/forms')],
};
