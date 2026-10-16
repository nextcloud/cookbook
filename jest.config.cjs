// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-or-later

// Jest configuration
module.exports = {
    testEnvironment: 'node',
    moduleFileExtensions: ['js', 'vue'],
    extensionsToTreatAsEsm: ['.vue'],
    modulePaths: [
        '<rootDir>/src/'
    ],
    modulePathIgnorePatterns: [
        '<rootDir>/.github/'
    ],
    transform: {
        '.*\\.js$': '<rootDir>/node_modules/babel-jest',
        '.*\\.(vue)$': '<rootDir>/src/tests/vueTransformer.js',
    },
    moduleNameMapper: {
        '^icons/(.*)$': '<rootDir>/node_modules/vue-material-design-icons/$1',
    },
    transformIgnorePatterns: [
        'node_modules/(?!(variables|vue-material-design-icons)/.*)',
    ],
};
