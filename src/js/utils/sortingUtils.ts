/* eslint-disable import/prefer-default-export */

/**
 * A function that compares two string items. Used to order strings alphabetically case-insensitively.
 * @param item1
 * @param item2
 * @returns {-1|1|0} `1` if `item1` should be sorted behind `item2`, `-1` if `item1` should be sorted before `item2`,
 * 	`0` otherwise.
 */
const caseInsensitiveStringSort = (
	item1: string,
	item2: string,
): 1 | -1 | 0 => {
	const i1 = item1.toLowerCase();
	const i2 = item2.toLowerCase();
	if (i1 < i2) return -1;
	if (i2 < i1) return 1;
	return 0;
};

export { caseInsensitiveStringSort };
