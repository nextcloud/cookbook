// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-or-later

import { defineConfig } from 'vitest/config';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'node:path';

export default defineConfig({
    plugins: [vue()],
    resolve: {
        alias: {
            cookbook: resolve(import.meta.dirname, 'src'),
            icons: resolve(import.meta.dirname, 'node_modules/vue-material-design-icons'),
        },
    },
    test: {
        include: ['src/tests/**/*.test.{js,ts}'],
        environment: 'happy-dom',
        globals: true,
    },
});
