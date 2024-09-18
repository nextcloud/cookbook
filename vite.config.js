import { createAppConfig } from '@nextcloud/vite-config'
import { join, resolve } from 'path'

export default createAppConfig(
	{
		main: resolve(join('src', 'main.ts')),
		guest: resolve(join('src', 'guest.ts')),
	}, {
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
		},
	}
)
