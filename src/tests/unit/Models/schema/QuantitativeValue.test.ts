import QuantitativeValue from '../../../../js/Models/schema/QuantitativeValue';

describe('QuantitativeValue', () => {
	test('should set the @type property to "QuantitativeValue"', () => {
		const quantitativeValue = new QuantitativeValue(15, 'meter');
		expect(quantitativeValue['@type']).toBe('QuantitativeValue');
	});

	test('should create an instance of QuantitativeValue with unitCode undefined', () => {
		const quantitativeValue = new QuantitativeValue(10, 'cup');
		expect(quantitativeValue).toBeInstanceOf(QuantitativeValue);
		expect(quantitativeValue.value).toBe(10);
		expect(quantitativeValue.unitText).toBe('cup');
		expect(quantitativeValue.unitCode).toBeUndefined();
	});

	test('should create an instance of QuantitativeValue with specified unitCode', () => {
		const quantitativeValue = new QuantitativeValue(5, 'kilogram', 'KGM');
		expect(quantitativeValue).toBeInstanceOf(QuantitativeValue);
		expect(quantitativeValue.value).toBe(5);
		expect(quantitativeValue.unitText).toBe('kilogram');
		expect(quantitativeValue.unitCode).toBe('KGM');
	});
});
