import QuantitativeValue from '../../../../js/Models/schema/QuantitativeValue';

describe('QuantitativeValue', () => {
	describe('constructor', () => {
		test('should set the @type property to "QuantitativeValue"', () => {
			const quantitativeValue = new QuantitativeValue(15, {
				unitText: 'meter',
			});
			expect(quantitativeValue['@type']).toBe('QuantitativeValue');
		});

		test('should create an instance of QuantitativeValue with unitCode undefined', () => {
			const quantitativeValue = new QuantitativeValue(10, {
				unitText: 'cup',
			});
			expect(quantitativeValue).toBeInstanceOf(QuantitativeValue);
			expect(quantitativeValue.value).toBe(10);
			expect(quantitativeValue.unitText).toBe('cup');
			expect(quantitativeValue.unitCode).toBeUndefined();
		});

		test('should create an instance of QuantitativeValue with specified unitCode', () => {
			const quantitativeValue = new QuantitativeValue(5, {
				unitText: 'kilogram',
				unitCode: 'KGM',
			});
			expect(quantitativeValue).toBeInstanceOf(QuantitativeValue);
			expect(quantitativeValue.value).toBe(5);
			expect(quantitativeValue.unitText).toBe('kilogram');
			expect(quantitativeValue.unitCode).toBe('KGM');
		});
	});

	describe('fromJSON', () => {
		it('should create a QuantitativeValue instance from a valid JSON string', () => {
			const jsonString =
				'{"value": 250, "unitText": "grams", "unitCode": "G"}';
			const quantitativeValue = QuantitativeValue.fromJSON(jsonString);
			expect(quantitativeValue).toBeInstanceOf(QuantitativeValue);
			expect(quantitativeValue.value).toBe(250);
			expect(quantitativeValue.unitText).toBe('grams');
			expect(quantitativeValue.unitCode).toBe('G');
		});

		it('should create a QuantitativeValue instance from a valid JSON object', () => {
			const jsonObject = { value: 2, unitText: 'cups', unitCode: 'CUP' };
			const quantitativeValue = QuantitativeValue.fromJSON(jsonObject);
			expect(quantitativeValue).toBeInstanceOf(QuantitativeValue);
			expect(quantitativeValue.value).toBe(2);
			expect(quantitativeValue.unitText).toBe('cups');
			expect(quantitativeValue.unitCode).toBe('CUP');
		});

		it('should throw an error for invalid JSON string', () => {
			const invalidJsonString =
				'{"value": "invalid", "unitText": "grams", "unitCode": "G"}';
			expect(() => QuantitativeValue.fromJSON(invalidJsonString)).toThrow(
				'Error mapping QuantitativeValue \'value\'. Expected integer number but received non-parsable string "invalid".',
			);
		});

		it('should throw an error for missing "value" property', () => {
			const jsonString = '{"unitText": "grams", "unitCode": "G"}';
			expect(() => QuantitativeValue.fromJSON(jsonString)).toThrow(
				'Error mapping QuantitativeValue \'value\'. Expected integer number but received "undefined".',
			);
		});

		it('should throw an error for missing "unitText" property', () => {
			const jsonString = '{"value": 250, "unitCode": "G"}';
			expect(() => QuantitativeValue.fromJSON(jsonString)).toThrow(
				'Error mapping QuantitativeValue \'value\'. Expected string but received "undefined".',
			);
		});

		it('should throw an error for invalid "unitCode" property type', () => {
			const jsonString =
				'{"value": 250, "unitText": "grams", "unitCode": 123}';
			expect(() => QuantitativeValue.fromJSON(jsonString)).toThrow(
				'Error mapping QuantitativeValue \'value\'. Expected string but received "number".',
			);
		});

		test('should throw an error for invalid JSON', () => {
			const invalidJson = 'Invalid JSON string';

			expect(() => QuantitativeValue.fromJSON(invalidJson)).toThrow(
				'Error mapping to "QuantitativeValue". Received invalid JSON: "Invalid JSON string"',
			);
		});
	});

	describe('fromJSONOrString', () => {
		it('should create a QuantitativeValue instance from valid JSON', () => {
			const json = '{"value": 10, "unitText": "cup"}';
			const expected = new QuantitativeValue(10, { unitText: 'cup' });
			expect(QuantitativeValue.fromJSONOrString(json)).toEqual(expected);
		});

		it('should create a QuantitativeValue instance from valid JSON string', () => {
			const jsonString = '{"value": 10, "unitText": "cup"}';
			const expected = new QuantitativeValue(10, { unitText: 'cup' });
			expect(QuantitativeValue.fromJSONOrString(jsonString)).toEqual(
				expected,
			);
		});

		it('should create a QuantitativeValue instance from a valid string', () => {
			const stringValue = '10';
			const expected = new QuantitativeValue(10);
			expect(QuantitativeValue.fromJSONOrString(stringValue)).toEqual(
				expected,
			);
		});

		it('should throw an error for invalid JSON', () => {
			const invalidJson = '{value: 10}';
			expect(() =>
				QuantitativeValue.fromJSONOrString(invalidJson),
			).toThrow();
		});

		it('should throw an error for invalid JSON string', () => {
			const invalidJsonString = '{"value": 10, unitText: "cup"}'; // missing quotes
			expect(() =>
				QuantitativeValue.fromJSONOrString(invalidJsonString),
			).toThrow();
		});

		it('should throw an error for invalid string', () => {
			const invalidString = 'invalid'; // no number
			expect(() =>
				QuantitativeValue.fromJSONOrString(invalidString),
			).toThrow();
		});
	});
});
