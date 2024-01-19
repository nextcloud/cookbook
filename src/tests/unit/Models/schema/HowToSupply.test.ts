import HowToSupply from '../../../../js/Models/schema/HowToSupply';

describe('HowToSupply', () => {
	test('should set the @type property to "HowToSupply"', () => {
		const howToSupply = new HowToSupply('Ingredient');
		expect(howToSupply['@type']).toBe('HowToSupply');
	});

	test('should create an instance of HowToSupply with optional properties undefined', () => {
		const howToSupply = new HowToSupply('Ingredient');
		expect(howToSupply).toBeInstanceOf(HowToSupply);
		expect(howToSupply.name).toBe('Ingredient');
		expect(howToSupply.identifier).toBeUndefined();
		expect(howToSupply.description).toBeUndefined();
		expect(howToSupply.requiredQuantity).toBeUndefined();
	});

	test('should create an instance of HowToSupply with some optional properties defined and some undefined', () => {
		const howToSupply = new HowToSupply('Ingredient', 'IGD123');
		expect(howToSupply).toBeInstanceOf(HowToSupply);
		expect(howToSupply.name).toBe('Ingredient');
		expect(howToSupply.identifier).toBe('IGD123');
		expect(howToSupply.description).toBeUndefined();
		expect(howToSupply.requiredQuantity).toBeUndefined();
	});

	test('should create an instance of HowToSupply with all properties set', () => {
		const howToSupply = new HowToSupply(
			'Flour',
			'FLR123',
			'High-quality flour',
			{
				value: 2,
				unitText: 'cup',
				unitCode: undefined,
			},
		);

		expect(howToSupply).toBeInstanceOf(HowToSupply);
		expect(howToSupply.name).toBe('Flour');
		expect(howToSupply.identifier).toBe('FLR123');
		expect(howToSupply.description).toBe('High-quality flour');
		expect(howToSupply.requiredQuantity).toEqual({
			value: 2,
			unitText: 'cup',
			unitCode: undefined,
		});
	});
});
