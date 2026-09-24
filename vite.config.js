```js
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

export default ({ mode }) => {
	const env = loadEnv(mode, process.cwd(), '')

	return createAppConfig(
		{
			main: resolve(join('src', 'main.ts')),
			guest: resolve(join('src', 'guest.ts')),
		},
		{
			createEmptyCSSEntryPoints: true,
			extractLicenseInformation: true,
			thirdPartyLicense: false,
			config: {
				resolve: {
					alias: {
						cookbook: resolve(__dirname, 'src'),
						icons: resolve(
							__dirname,
							'node_modules/vue-material-design-icons'
						),
					},
				},
				plugins: customPlugins,
				server: {
					port: Number(env.VITE_PORT ?? 5173),
					strictPort: true,
				},
			},
		}
	)
}
```
