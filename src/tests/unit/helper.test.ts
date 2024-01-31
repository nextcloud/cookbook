import { asArray, asCleanedArray } from 'cookbook/js/helper';

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
