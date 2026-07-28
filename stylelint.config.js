export default {
  extends: [ 'stylelint-config-standard' ],
  ignoreFiles: [
    'assets/dist/**',
    'coverage/**',
    'node_modules/**',
    'vendor/**',
  ],
  rules: {
    'at-rule-no-unknown': [
      true,
      {
        ignoreAtRules: [ 'apply', 'config', 'layer', 'screen', 'tailwind', 'variants' ],
      },
    ],
    'selector-class-pattern': null,
    'selector-id-pattern': null,
  },
};
