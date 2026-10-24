// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-or-later

import { defineConfig } from 'vitest/config';
import { resolve } from 'path';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [vue()],
    resolve: {
        alias: {
            cookbook: resolve(__dirname, 'src'),
            icons: resolve(__dirname, 'node_modules/vue-material-design-icons'),
        },
    },
    test: {
        include: ['src/tests/**/*.test.{js,ts}'],
        environment: 'happy-dom',
        globals: true,
    },
});
