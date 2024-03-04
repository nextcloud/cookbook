// Jest configuration
module.exports = {
    testEnvironment: 'node',
    moduleFileExtensions: ['js', 'ts', 'vue'],
    moduleNameMapper: {
        '^cookbook/(.*)$': '<rootDir>/src/$1',
        '^icons/(.*)$': '<rootDir>/node_modules/vue-material-design-icons/$1',
    },
    modulePaths: ['<rootDir>/src/'],
    modulePathIgnorePatterns: ['<rootDir>/.github/'],
    transform: {
        '.*\\.js$': '<rootDir>/node_modules/babel-jest',
        '.*\\.ts$': '<rootDir>/node_modules/babel-jest',
        '.*\\.(vue)$': '<rootDir>/node_modules/@vue/vue2-jest',
    },
    transformIgnorePatterns: ['node_modules/(?!variables/.*)'],
};
