import js from '@eslint/js'
import react from 'eslint-plugin-react'
import reactHooks from 'eslint-plugin-react-hooks'
import globals from 'globals'

export default [
  // 基本のJS推奨ルール
  js.configs.recommended,

  {
    files: ['resources/js/**/*.{js,jsx}'],

    languageOptions: {
      ecmaVersion: 'latest',
      sourceType: 'module',
      globals: {
        ...globals.browser,
        deleteUrl: 'readonly',
        uploadUrl: 'readonly',
        uploadsDeleteUrl: 'readonly',
        CSRF_TOKEN: 'readonly',
        Sortable: 'readonly',
      },
      parserOptions: {
        ecmaFeatures: { jsx: true },
      },
    },

    plugins: {
      react,
      'react-hooks': reactHooks,
    },

    rules: {
      // React推奨ルール
      ...react.configs.recommended.rules,

      // Hooks推奨ルール
      ...reactHooks.configs.recommended.rules,

      // React 17+ では不要
      'react/react-in-jsx-scope': 'off',

      // 使用しない変数の警告は消すルール（ _変数名 ）
      'no-unused-vars': ['warn', {
        argsIgnorePattern: '^_',
      }],
      
    },

    settings: {
      react: {
        version: 'detect',
      },
    },
  },
]
