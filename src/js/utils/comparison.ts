/**
 * Compares two arrays to determine if they contain the same elements, regardless of order.
 * @template T
 * @param {T[]} array1 - The first array to compare.
 * @param {T[]} array2 - The second array to compare.
 * @returns {boolean} - Returns true if both arrays contain the same elements, otherwise false.
 */
// eslint-disable-next-line import/prefer-default-export
export function compareArrays<T>(array1: T[], array2: T[]): boolean {
	// Check if both arrays are of the same length
	if (array1.length !== array2.length) {
		return false;
	}

	// Create copies of the arrays to avoid modifying the original arrays
	const sortedArray1 = [...array1].sort();
	const sortedArray2 = [...array2].sort();

	// Converting to JSON string allows deep comparison
	return JSON.stringify(sortedArray1) === JSON.stringify(sortedArray2);
}
