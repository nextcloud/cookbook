// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-or-later

// Jest configuration
module.exports = {
    testEnvironment: 'node',
    moduleFileExtensions: ['js', 'vue'],
    modulePaths: [
        '<rootDir>/src/'
    ],
    modulePathIgnorePatterns: [
        '<rootDir>/.github/'
    ],
    transform: {
        '.*\\.js$': '<rootDir>/node_modules/babel-jest',
        '.*\\.(vue)$': '<rootDir>/node_modules/@vue/vue3-jest',
    },
    transformIgnorePatterns: ['node_modules/(?!variables/.*)'],
};
