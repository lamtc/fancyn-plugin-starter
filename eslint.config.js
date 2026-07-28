import globals from 'globals';

export default [
  {
    ignores: [
      'assets/dist/**',
      'artifacts/**',
      'build/**',
      'coverage/**',
      'node_modules/**',
      'vendor/**',
    ],
  },
  {
    files: [ 'assets/src/**/*.js' ],
    languageOptions: {
      ecmaVersion: 'latest',
      globals: globals.browser,
      sourceType: 'module',
    },
    rules: {
      'no-undef': 'error',
      'no-unused-vars': [ 'error', { argsIgnorePattern: '^_' } ],
    },
  },
  {
    files: [ '*.config.js', 'scripts/**/*.{js,mjs}', 'tests/**/*.js' ],
    languageOptions: {
      ecmaVersion: 'latest',
      globals: {
        ...globals.browser,
        ...globals.node,
        ...globals.vitest,
      },
      sourceType: 'module',
    },
    rules: {
      'no-undef': 'error',
      'no-unused-vars': [ 'error', { argsIgnorePattern: '^_' } ],
    },
  },
];
