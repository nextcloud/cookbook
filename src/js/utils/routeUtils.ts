import { Route } from 'vue-router';
import { Dictionary } from 'vue-router/types/router';
import parseSearchString from 'cookbook/js/utils/parseSearchString';

/**
 * Decodes the URI-encoded query parameters of a Vue Router Route object.
 * @param {Route} route - The Vue Router Route object.
 * @returns {Record<string, string | string[]>} An object containing the decoded query parameters.
 */
export function decodeQueryParams(
	route: Route,
): Dictionary<string | (string | null)[]> {
	const decodedQuery: Record<string, string | (string | null)[]> = {};

	// Iterate over the query parameters and decode each one
	Object.keys(route.query).forEach((key) => {
		const value = route.query[key];

		// Check if the value is an array (for multiple values with the same key)
		if (Array.isArray(value)) {
			decodedQuery[key] = value.map((v) =>
				v ? decodeURIComponent(v) : null,
			);
		} else if (typeof value === 'string') {
			decodedQuery[key] = decodeURIComponent(value);
		}
	});

	return decodedQuery;
}

/**
 * Decode the query paramater `q` of `route`, URI decode it and convert them to string.
 * @param route
 */
export function routeToQueryProp(route: Route): string | null {
	const decodedQuery = decodeQueryParams(route);
	if (!Object.prototype.hasOwnProperty.call(decodedQuery, 'q')) return null;
	const q = Array.isArray(decodedQuery.q)
		? decodedQuery.q.join(',')
		: decodedQuery.q;

	// Clean query to remove duplicates
	const parsedFilters = parseSearchString(q);
	return parsedFilters.map((f) => f.toSearchString()).join(' ');
}

export function isRouteToRecipe(route: Route): boolean {
	return route.name === '';
}
