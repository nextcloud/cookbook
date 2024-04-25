import VueRouter, { Route } from 'vue-router';

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
 * Navigates to the parent of a recipe route.
 * @param {Route} route - The current route to the recipe.
 * @returns The new route or null, if a parent for `route` can't be determined.
 */
export function goToRecipeParent(route: Route): Promise<Route> | null {
	const parts = route.path.split('/');
	if (!['category', 'name', 'search', 'tags'].includes(parts[1])) {
		// wrong route
		return null;
	}
	// Remove recipe id from path
	parts.splice(-1, 1);

	// Build new path
	const path = parts.join('/');

	// Navigate, keeping the query params of the recipe view
	return router.push({ path, query: route.query });
}

export default {
	useRouter,
	goToRecipeParent,
};
