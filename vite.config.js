// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-or-later

import { createAppConfig } from '@nextcloud/vite-config'
import { join, resolve } from 'path'
import { loadEnv } from 'vite'
import { visualizer } from 'rollup-plugin-visualizer'

const customPlugins = []

if (process.env.ENABLE_BUNDLE_ANALYZER === 'true') {
	customPlugins.push(visualizer({
		template: 'raw-data',
		filename: '.bundle-analysis.json',
		gzipSize: true,
		open: false,
	}))
}

const devEntryRewritePlugin = {
	name: 'cookbook-dev-entry-rewrite',
	apply: 'serve',

	configureServer(server) {
		server.middlewares.use((req, res, next) => {
			if (req.url?.startsWith('/apps-extra/cookbook/js/cookbook-main.mjs')) {
				req.url = req.url.replace(
					'/apps-extra/cookbook/js/cookbook-main.mjs',
					'/apps-extra/cookbook/src/main.ts',
				)
			}

			if (req.url?.startsWith('/apps-extra/cookbook/js/cookbook-guest.mjs')) {
				req.url = req.url.replace(
					'/apps-extra/cookbook/js/cookbook-guest.mjs',
					'/apps-extra/cookbook/src/guest.ts',
				)
			}

			next()
		})
	},
}

export default createAppConfig(
	{
		main: resolve(join('src', 'main.ts')),
		guest: resolve(join('src', 'guest.ts')),
	},
	{
		createEmptyCSSEntryPoints: true,
		extractLicenseInformation: true,
		thirdPartyLicense: false,
		config: ({ mode, command }) => {
			const env = loadEnv(mode, process.cwd(), '')
			const devBase = '/apps-extra/cookbook/'

			return {
				base: command === 'serve' ? devBase : '/',
				resolve: {
					alias: {
						cookbook: resolve(__dirname, 'src'),
						icons: resolve(
							__dirname,
							'node_modules/vue-material-design-icons'
						),
					},
				},
				plugins: [
					...customPlugins,
					devEntryRewritePlugin,
				],
				server: {
					port: Number(env.VITE_PORT ?? 5173),
					host: '0.0.0.0',
					strictPort: true,
					allowedHosts: [
						'localhost',
						'nextcloud.local',
						'host.docker.internal',
					],
				},
			}
		},
	}
)
