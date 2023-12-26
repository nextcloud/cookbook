// Jest configuration
export default {
    testEnvironment: 'jest-environment-node',

    moduleFileExtensions: ['js', 'vue'],
    transform: {
        '.*\\.js$': '<rootDir>/node_modules/babel-jest',
        '.*\\.(vue)$': '<rootDir>/node_modules/@vue/vue2-jest',
    },
};
