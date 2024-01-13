import VueRouter, { Route } from 'vue-router';

/**
 * Clamps val between the minimum min and maximum max value.
 * @param val The value to be clamped between min and max
 * @param min The upper limit
 * @param max The lower limit
 * @returns {number} min if val is <= min, max if val is >= max and val if min <= val <= max.
 */
function clamp(val: number, min: number, max: number): number {
	return Math.min(max, Math.max(min, val));
}

// Check if two routes point to the same component but have different content
function shouldReloadContent(url1: string, url2: string): boolean {
	if (url1 === url2) {
		return false; // Obviously should not if both routes are the same
	}

	const comps1 = url1.split('/');
	const comps2 = url2.split('/');

	if (comps1.length < 2 || comps2.length < 2) {
		return false; // Just a failsafe, this should never happen
	}

	// The route structure is as follows:
	// - /{item}/:id        View
	// - /{item}/:id/edit   Edit
	// - /{item}/create     Create
	// If the items are different, then the router automatically handles
	// component loading: do not manually reload
	if (comps1[1] !== comps2[1]) {
		return false;
	}

	// If one of the routes is "edit" and the other is not
	if (comps1.length !== comps2.length) {
		// Only reload if changing from "edit" to "create"
		return comps1.pop() === 'create' || comps2.pop() === 'create';
	}
	if (comps1.pop() === 'create') {
		// But, if we are moving from create to view, do not reload
		// the "create" component
		return false;
	}

	// Only options left are that both of the routes are edit or view,
	// but not identical, or that we're moving from "view" to "create"
	// -> reload view
	return true;
}

/**
 * Check if the two urls point to the same item instance
 */
function isSameItemInstance(url1: string, url2: string): boolean {
	if (url1 === url2) {
		return true; // Obviously true if the routes are the same
	}
	const comps1 = url1.split('/');
	const comps2 = url2.split('/');
	if (comps1.length < 2 || comps2.length < 2) {
		return false; // Just a failsafe, this should never happen
	}
	// If the items are different, then the item instance cannot be
	// the same either
	if (comps1[1] !== comps2[1]) {
		return false;
	}
	if (comps1.length < 3 || comps2.length < 3) {
		// ID is the third url component, so can't be the same instance if
		// either of the urls have less than three components
		return false;
	}
	return comps1[2] === comps2[2];
}

/**
 * A simple function to sanitize HTML tags.
 * @param {string} text Input string
 * @returns {string}
 */
function escapeHTML(text: string): string {
	const replacementChars = {
		'&': '&amp;',
		'"': '&quot;',
		"'": '&apos;',
		'<': '&lt;',
		'>': '&gt;',
	};
	return text.replace(/["&'<>]/g, (c) => replacementChars[c]);
}

/**
 * `VueRouter` instance to be used for navigation functions like `goTo(url)`.
 */
let router: VueRouter;

/**
 * Set the router to be used for navigation functions like `goTo(url)`.
 * @param _router `VueRouter` to be used.
 */
function useRouter(_router: VueRouter): void {
	router = _router;
}

/**
 * Push a new URL to the router, effectively navigating to that page.
 * @param url URL to navigate to.
 */
function goTo(url: string): Promise<Route> {
	return router.push(url);
}

/**
 * Ensures that item is an array. If not, wraps it in an array.
 * @template T
 * @param {T|T[]} item Item to be wrapped in an array if it isn't an array itself.
 * @returns {T[]}
 */
export function asArray<T>(item: T | T[]): T[] {
	return Array.isArray(item) ? item : [item];
}

export default {
	asArray,
	clamp,
	shouldReloadContent,
	isSameItemInstance,
	escapeHTML,
	useRouter,
	goTo,
};
