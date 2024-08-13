module.exports = {
  env: {
    browser: true,
    es2021: true,
  },
  extends: [
    'eslint:recommended',
    'plugin:import/recommended',
    'plugin:vue/vue3-recommended',
  ],
  overrides: [
  ],
  parserOptions: {
    ecmaVersion: 'latest',
    sourceType: 'module',
  },
  plugins: [
    'vue',
  ],
  rules: {
    'arrow-parens': ['error', 'always'],
    'comma-dangle': ['error', 'always-multiline'],
    'import/order': ['error', {
      groups: ['builtin', 'external', 'internal', 'sibling', 'parent', 'index', 'object', 'type'],
      pathGroups: [
        {
          'pattern': '@/**',
          'group': 'internal',
        },
      ],
    }],
    'indent': ['error', 2],
    'no-empty-function': 'off',
    'no-multiple-empty-lines': ['error', { 'max': 1, 'maxEOF': 0 }],
    'no-undef': 'off',
    'no-unused-vars': ['error', { 'vars': 'all', 'args': 'after-used' }],
    'object-curly-spacing': ['error', 'always'],
    'padded-blocks': ['error', 'never'],
    'quotes': ['error', 'single'],
    'semi': ['error', 'always'],
    'space-before-function-paren': ['error', { anonymous: 'always', named: 'never', asyncArrow: 'always' }],
    'vue/max-attributes-per-line': ['error', { 'singleline': { 'max': 4 },'multiline': { 'max': 1 } }],
  },
  settings: {
    'import/resolver': {
      alias: {
        map: [
          ['@', './resources/js'],
        ],
        extensions: ['.ts', '.js', '.jsx', '.json'],
      },
    },
  },
};
