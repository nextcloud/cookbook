// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

/**
 * `vue-material-design-icons` does not declare a package.json "exports" map,
 * so TypeScript cannot resolve the `.d.vue.ts` declaration file that ships
 * alongside each icon's `.vue` component for subpath imports.
 */
declare module 'vue-material-design-icons/*.vue' {
	import type { DefineComponent } from 'vue';

	const component: DefineComponent<{
		title?: string;
		fillColor?: string;
		size?: number;
	}>;
	export default component;
}

declare module 'vue-showdown';

declare module 'icons/*.vue' {
	import type { DefineComponent } from 'vue';

	const component: DefineComponent<{
		title?: string;
		fillColor?: string;
		size?: number;
	}>;
	export default component;
}

declare module 'lozad' {
	interface Observer {
		observe(): void;
		disconnect(): void;
	}

	interface ObserverOptions {
		selector?: string;
		enableAutoReload?: boolean;
		load?: (element: Element) => void;
		loaded?: (element: Element) => void;
	}

	function lozad(
		element?: Element | null,
		options?: ObserverOptions,
	): Observer;

	export default lozad;
}
