import path from 'node:path';
import { fileURLToPath } from 'node:url';

import js from '@eslint/js';
import { FlatCompat } from '@eslint/eslintrc';
import { fixupConfigRules } from '@eslint/compat';
import pluginVue from 'eslint-plugin-vue';

import vueParser from 'vue-eslint-parser';
import tsParser from '@typescript-eslint/parser';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const compat = new FlatCompat({
  baseDirectory: __dirname,
  recommendedConfig: js.configs.recommended,
});

export default [
  {
    ignores: ['node_modules/**', 'dist/**', 'build/**', 'js/**'],
  },

  // Keep legacy compat only for the non-Vue configs
  ...fixupConfigRules(
    compat.extends(
      'eslint:recommended',
      'airbnb-base',
      'airbnb-typescript/base',
      // 'plugin:vue/base',
      // 'plugin:vue/vue3-essential',
      // 'plugin:vue/vue3-strongly-recommended',
      // 'plugin:vue/vue3-recommended',
      'plugin:import/typescript',
      // '@vue/typescript/recommended',
      'prettier',
    ),
  ),

  // Native flat Vue 2 preset
  ...pluginVue.configs['flat/vue2-recommended'],

  // Force Vue SFC parsing for .vue files LAST so it wins
  {
    files: ['src/**/*.vue'],
    languageOptions: {
      parser: vueParser,
      parserOptions: {
        parser: tsParser,
        project: './tsconfig.json',
        tsconfigRootDir: __dirname,
        extraFileExtensions: ['.vue'],
        ecmaVersion: 'latest',
        sourceType: 'module',
      },
      globals: {
        OC: 'readonly',
        n: 'readonly',
        t: 'readonly',
      },
    },
  },

  // Normal TS/JS files
  {
    files: ['src/**/*.{js,ts}'],
    languageOptions: {
      parser: tsParser,
      parserOptions: {
        project: './tsconfig.json',
        tsconfigRootDir: __dirname,
        ecmaVersion: 'latest',
        sourceType: 'module',
      },
      globals: {
        OC: 'readonly',
        n: 'readonly',
        t: 'readonly',
      },
    },
  },

  // This is just a quick fix for an issue where the TS parser is trying to parse .vue files and causing false positives for unused variables. It happens only for Vue SFC files with multiple script tags. By turning off the rule for .vue files, we can avoid these false positives while still enforcing it for regular TS files.
  {
    files: ['src/**/*.ts'],
    rules: {
      'no-unused-vars': 'off',
      '@typescript-eslint/no-unused-vars': 'error',
    },
  },
  {
    files: ['src/**/*.vue'],
    rules: {
      '@typescript-eslint/no-unused-vars': 'off',
      'no-unused-vars': 'off',
      'vue/valid-v-for': 'off',
    },
  },
  
  {
    files: ['src/**/*.vue'],
    rules: {
      'vue/html-indent': ['warn', 4, {
        attribute: 1,
        baseIndent: 1,
        closeBracket: 0,
        alignAttributesVertically: true,
      }],

      // The following should prevent issues with prettier
      'vue/singleline-html-element-content-newline': 'off',
      'vue/multiline-html-element-content-newline': 'off',
      'vue/html-self-closing': 'off',
      'vue/max-attributes-per-line': 'off',
    },
  },

  {
    files: ['src/**/*.{js,ts,vue}'],
    settings: {
      'import/resolver': {
        node: {
          paths: ['src'],
          extensions: ['.js', '.ts', '.d.ts'],
        },
        typescript: {
          project: './tsconfig.json',
        },
        alias: {
          map: [
            ['cookbook', './src'],
            ['icons', './node_modules/vue-material-design-icons'],
          ],
          extensions: ['.js', '.ts', '.d.ts'],
        },
      },
    },

    rules: {
      'no-unused-vars': 'off',
      //'@typescript-eslint/no-unused-vars': 'error',
      'no-param-reassign': ['error', {
        props: true,
        ignorePropertyModificationsFor: ['state'],
      }],
      'import/extensions': [
        'error',
        'ignorePackages',
        {
          js: 'never',
          jsx: 'never',
          ts: 'never',
          tsx: 'never',
        },
      ],
      'no-plusplus': [
        'error',
        {
          allowForLoopAfterthoughts: true,
        },
      ],
      quotes: [
        'error',
        'single',
        {
          avoidEscape: true,
        },
      ],
      semi: ['error', 'always'],
      'no-restricted-syntax': [
        'error',
        'ForInStatement',
        'LabeledStatement',
        'WithStatement',
      ],
      'vue/no-deprecated-dollar-listeners-api': 'off',
      'vue/no-deprecated-v-bind-sync': 'off',
    },
  },

  {
    files: ['src/composables/**/*.js'],
    rules: {
      'import/prefer-default-export': 'off',
    },
  },
];