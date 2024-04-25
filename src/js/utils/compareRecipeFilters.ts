import { RecipeFilter } from 'cookbook/js/RecipeFilters';

/**
 * Compares two lists of recipe filters.
 * @param filters1
 * @param filters2
 * @return True if both lists contain the same filters
 */
export default function compareRecipeFilters(
	filters1: RecipeFilter[],
	filters2: RecipeFilter[],
) {
	if (filters1.length !== filters2.length) {
		return false;
	}

	for (let i1 = 0; i1 < filters1.length; i1++) {
		const filter1 = filters1[i1];
		let filterIsNew = true;
		for (let i2 = 0; filterIsNew && i2 < filters2.length; i2++) {
			const filter2 = filters2[i2];
			if (filter1.equals(filter2)) {
				filterIsNew = false;
				break;
			}
		}
		if (filterIsNew) return false;
	}
	return true;
}
