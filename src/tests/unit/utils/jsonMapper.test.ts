import {
	mapInteger,
	mapString,
	mapStringOrStringArray,
} from 'cookbook/js/utils/jsonMapper';

// mapInteger tests
describe('mapInteger', () => {
	it('should throw for null value', () => {
		expect(() => mapInteger(null, 'property')).toThrow(
			'Error mapping property. Expected integer number but received "null".',
		);
	});

	it('should return null for null value if null and undefined are allowed', () => {
		const result = mapInteger(null, '', true);
		expect(result).toBeNull();
	});

	it('should throw for undefined value', () => {
		expect(() => mapInteger(undefined, 'property', false)).toThrow(
			'Error mapping property. Expected integer number but received "undefined".',
		);
	});

	it('should return undefined for undefined value if null and undefined are allowed', () => {
		const result = mapInteger(undefined, '', true);
		expect(result).toBeUndefined();
	});

	it('should map integer number to itself', () => {
		const result = mapInteger(42);
		expect(result).toBe(42);
	});

	it('should map numeric string to an integer', () => {
		const result = mapInteger('123');
		expect(result).toBe(123);
	});

	it('should throw an error for non-integer number', () => {
		expect(() => mapInteger(42.5, 'property')).toThrow(
			'Error mapping property. Expected integer number but received non-integer "42.5".',
		);
	});

	it('should throw an error for non-numeric string', () => {
		expect(() => mapInteger('abc', 'property')).toThrow(
			'Error mapping property. Expected integer number but received non-parsable string "abc".',
		);
	});

	it('should throw an error for non-numeric and non-string value', () => {
		const invalidValue = { prop: 'invalid' };
		expect(() => mapInteger(invalidValue, 'property')).toThrow(
			'Error mapping property. Expected integer number but received "object".',
		);
	});
});

// mapStringOrStringArray() tests
describe('mapStringOrStringArray', () => {
	it('should throw for null value', () => {
		expect(() => mapStringOrStringArray(null, 'property')).toThrow(
			'Error mapping property. Expected string or string array but received "null".',
		);
	});

	it('should return null for null value if null and undefined are allowed', () => {
		const result = mapStringOrStringArray(null, '', true);
		expect(result).toBeNull();
	});

	it('should throw for undefined value', () => {
		expect(() =>
			mapStringOrStringArray(undefined, 'property', false),
		).toThrow(
			'Error mapping property. Expected string or string array but received "undefined".',
		);
	});

	it('should return undefined for undefined value if null and undefined are allowed', () => {
		const result = mapStringOrStringArray(undefined, '', true);
		expect(result).toBeUndefined();
	});

	it('should map string to itself', () => {
		const result = mapStringOrStringArray('test');
		expect(result).toBe('test');
	});

	it('should map array of strings to itself', () => {
		const result = mapStringOrStringArray(['test1', 'test2']);
		expect(result).toEqual(['test1', 'test2']);
	});

	it('should throw an error for non-string and non-array value', () => {
		const invalidValue = 42;
		const invalidValue2 = { prop: '42' };

		expect(() => mapStringOrStringArray(invalidValue, 'property')).toThrow(
			'Error mapping property. Expected string or array but received "number".',
		);

		expect(() => mapStringOrStringArray(invalidValue2, 'property')).toThrow(
			'Error mapping property. Expected string or array but received "object".',
		);
	});

	it('should throw an error for array with non-string elements', () => {
		const invalidValue = ['test', 42];
		expect(() => mapStringOrStringArray(invalidValue, 'property')).toThrow(
			'Error mapping property. Expected string or string array received array with non-string elements.',
		);
	});
});

// mapString tests
describe('mapString', () => {
	it('should throw for null value', () => {
		expect(() => mapString(null, 'property')).toThrow(
			'Error mapping property. Expected string but received "null".',
		);
	});

	it('should return null for null value if null and undefined are allowed', () => {
		const result = mapString(null, '', true);
		expect(result).toBeNull();
	});

	it('should throw for undefined value', () => {
		expect(() => mapString(undefined, 'property')).toThrow(
			'Error mapping property. Expected string but received "undefined".',
		);
	});

	it('should return undefined for undefined value if null and undefined are allowed', () => {
		const result = mapString(undefined, '', true);
		expect(result).toBeUndefined();
	});

	it('should map string to itself', () => {
		const result = mapString('test');
		expect(result).toBe('test');
	});

	it('should throw an error for non-string value', () => {
		const invalidValueNumber = 42;
		const invalidValueObject = { value: '42' };

		expect(() => mapString(invalidValueNumber, 'property')).toThrow(
			'Error mapping property. Expected string but received "number".',
		);

		expect(() => mapString(invalidValueObject, 'property')).toThrow(
			'Error mapping property. Expected string but received "object".',
		);
	});
});
