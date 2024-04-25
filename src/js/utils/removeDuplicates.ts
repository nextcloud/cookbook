import { compareArrays } from 'cookbook/js/utils/comparison';

/**
 * Removes duplicates from a double string array, ignoring the order of strings in the inner array.
 * @param {string[][]} arr - Array to handle
 * @returns Array without duplicates
 */
export function removeDuplicatesInNestedStringArray(
	arr: string[][],
): string[][] {
	const array = JSON.parse(JSON.stringify(arr));

	for (let outerIdx = 0; outerIdx < array.length; outerIdx++) {
		const item = array[outerIdx];
		for (let innerIdx = array.length - 1; innerIdx > outerIdx; innerIdx--) {
			if (compareArrays(item, array[innerIdx])) {
				// Remove duplicate
				array.splice(innerIdx, 1);
			}
		}
	}
	return array;
}

/**
 * Removes duplicates from an array by using the equals operator for elements.
 * Not expected to work for objects.
 * Not very efficient for large arrays.
 * @template T
 * @param {T[]} arr - Array to handle
 * @returns Array without duplicates
 */
// eslint-disable-next-line import/prefer-default-export
export function simpleRemoveDuplicates<T>(arr: T[]): T[] {
	return arr.filter((item, pos) => arr.indexOf(item) === pos);
}
