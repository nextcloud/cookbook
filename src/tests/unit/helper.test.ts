import { adjustToInteger, asArray, asCleanedArray } from 'cookbook/js/helper';

// adjustToIntegerToInteger() tests
describe('adjustToInteger function', () => {
	it('should increase the value by 1 when step is 1', () => {
		expect(adjustToInteger(1, 1)).toBe(2);
		expect(adjustToInteger(1.5, 1)).toBe(2);
	});

	it('should increase the value by 2 when step is 2', () => {
		expect(adjustToInteger(1, 2)).toBe(3);
		expect(adjustToInteger(1.5, 2)).toBe(3);
	});

	it('should decrease the value by 1 when step is -1', () => {
		expect(adjustToInteger(2, -1)).toBe(1);
		expect(adjustToInteger(2.5, -1)).toBe(2);
		expect(adjustToInteger(1, -1)).toBe(1);
		expect(adjustToInteger(0.5, -1)).toBe(0.5);
	});

	it('should decrease the value by 2 when step is -2', () => {
		expect(adjustToInteger(3, -2)).toBe(1);
		expect(adjustToInteger(3.5, -2)).toBe(2);
		expect(adjustToInteger(1.5, -2)).toBe(1);
		expect(adjustToInteger(0.5, -2)).toBe(0.5);
	});

	it('should handle values between 0 and 1 correctly with negative step', () => {
		expect(adjustToInteger(0.5, -1)).toBe(0.5);
		expect(adjustToInteger(0.5, -2)).toBe(0.5);
		expect(adjustToInteger(0.3, -1)).toBe(0.3);
		expect(adjustToInteger(0.3, -2)).toBe(0.3);
	});
});

// asArray() tests
describe('asArray', () => {
	it('should return array unchanged', () => {
		const arr = ['val', null, 1, undefined];
		expect(asArray(arr)).toStrictEqual(arr);
	});
	it('should return single value as array', () => {
		const val = 'val';
		expect(asArray(val)).toStrictEqual([val]);
	});
	it('should return single undefined value as array', () => {
		const val = undefined;
		expect(asArray(val)).toStrictEqual([val]);
	});
	it('should return single null value as array', () => {
		const val = undefined;
		expect(asArray(val)).toStrictEqual([val]);
	});
});

// asCleanedArray() tests
describe('asCleanedArray', () => {
	it('should return array with values unchanged', () => {
		const arr = ['val', { test: 'name', id: 4 }, 1, true];
		expect(asCleanedArray(arr)).toStrictEqual(arr);
	});
	it('should remove null and undefined from array', () => {
		const arr = ['val', null, 1, undefined];
		expect(asCleanedArray(arr)).toStrictEqual(['val', 1]);
	});
	it('should return single value as array', () => {
		const val = 'val';
		expect(asCleanedArray(val)).toStrictEqual([val]);
	});
	it('should return single undefined value as empty array', () => {
		const val = undefined;
		expect(asCleanedArray(val)).toStrictEqual([]);
	});
	it('should return single null value as empty array', () => {
		const val = undefined;
		expect(asCleanedArray(val)).toStrictEqual([]);
	});
});
