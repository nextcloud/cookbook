import { removeDuplicatesInNestedStringArray } from 'cookbook/js/utils/removeDuplicates';

describe('removeDuplicatesInNestedStringArray', () => {
	test('should remove duplicate string arrays', () => {
		const input = [
			['apple', 'banana'],
			['banana', 'apple'],
			['cherry', 'date'],
			['apple', 'banana'],
		];
		const expectedOutput = [
			['apple', 'banana'],
			['cherry', 'date'],
		];

		const result = removeDuplicatesInNestedStringArray(input);

		// Check if the result has the expected length
		expect(result.length).toBe(expectedOutput.length);

		// Check if each element in the result matches the expected output
		expectedOutput.forEach((expectedArray, index) => {
			expect(result[index]).toEqual(
				expect.arrayContaining(expectedArray),
			);
		});
	});

	test('should not remove non-duplicate string arrays', () => {
		const input = [
			['apple', 'banana'],
			['cherry', 'date'],
		];
		const expectedOutput = [
			['apple', 'banana'],
			['cherry', 'date'],
		];

		const result = removeDuplicatesInNestedStringArray(input);

		// Check if the result has the expected length
		expect(result.length).toBe(expectedOutput.length);

		// Check if each element in the result matches the expected output
		expectedOutput.forEach((expectedArray, index) => {
			expect(result[index]).toEqual(
				expect.arrayContaining(expectedArray),
			);
		});
	});

	test('should handle empty array input', () => {
		const input: string[][] = [];
		const expectedOutput: string[][] = [];

		const result = removeDuplicatesInNestedStringArray(input);

		// Check if the result is an empty array
		expect(result).toEqual(expectedOutput);
	});

	test('should handle inner arrays with varying numbers of strings', () => {
		const input = [
			['apple', 'banana', 'cherry'],
			['apple', 'banana'],
			['banana', 'apple'],
			['apple', 'banana', 'cherry', 'date'],
			['apple', 'banana', 'cherry'],
		];
		const expectedOutput = [
			['apple', 'banana', 'cherry'],
			['apple', 'banana'],
			['apple', 'banana', 'cherry', 'date'],
		];

		const result = removeDuplicatesInNestedStringArray(input);

		// Check if the result has the expected length
		expect(result.length).toBe(expectedOutput.length);

		// Check if each element in the result matches the expected output
		expectedOutput.forEach((expectedArray, index) => {
			expect(result[index]).toEqual(
				expect.arrayContaining(expectedArray),
			);
		});
	});
});
