import HowToSupply from 'cookbook/js/Models/schema/HowToSupply';

describe('HowToSupply', () => {
	describe('constructor', () => {
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

	describe('parseJSON', () => {
		const createValidJSON = () => ({
			name: 'Flour',
			identifier: 'FLR123',
			description: 'High-quality flour',
			requiredQuantity: {
				value: 1,
				unitText: 'cup',
			},
		});

		it('should create an instance from valid JSON', () => {
			const validJSON = createValidJSON();
			const tool = HowToSupply.fromJSON(validJSON);
			expect(tool).toBeInstanceOf(HowToSupply);
			expect(tool.name).toBe(validJSON.name);
			expect(tool.identifier).toBe(validJSON.identifier);
			expect(tool.description).toBe(validJSON.description);
			expect(tool.requiredQuantity).toBeDefined();
			// Add more specific checks for QuantitativeValue if needed
		});

		it('should create an instance from valid JSON string', () => {
			const validJSON = createValidJSON();
			const tool = HowToSupply.fromJSON(JSON.stringify(validJSON));
			expect(tool).toBeInstanceOf(HowToSupply);
			expect(tool.name).toBe(validJSON.name);
			expect(tool.identifier).toBe(validJSON.identifier);
			expect(tool.description).toBe(validJSON.description);
			expect(tool.requiredQuantity).toBeDefined();
			// Add more specific checks for QuantitativeValue if needed
		});

		it('should handle missing optional properties', () => {
			const validJSON = { name: 'Knife' };
			const tool = HowToSupply.fromJSON(validJSON);
			expect(tool).toBeInstanceOf(HowToSupply);
			expect(tool.name).toBe(validJSON.name);
			expect(tool.identifier).toBeUndefined();
			expect(tool.description).toBeUndefined();
			expect(tool.requiredQuantity).toBeUndefined();
		});

		it('should throw an error for invalid JSON', () => {
			const invalidJSON = { name: 123 }; // 'name' should be a string
			expect(() => HowToSupply.fromJSON(invalidJSON)).toThrow();
		});

		it('should throw an error for invalid JSON string', () => {
			const invalidJSONString = '{"name": 123}'; // 'name' should be a string
			expect(() => HowToSupply.fromJSON(invalidJSONString)).toThrow();
		});

		it('should throw an error for invalid JSON with missing name property', () => {
			const invalidJSON = { prop: 123 }; // 'name' is missing
			expect(() => HowToSupply.fromJSON(invalidJSON)).toThrow(
				'Error mapping HowToSupply \'name\'. Expected string but received "undefined".',
			);
		});

		it('should throw an error for invalid JSON string with missing name property', () => {
			const invalidJSONString = '{"prop": 123}'; // 'name' is missing
			expect(() => HowToSupply.fromJSON(invalidJSONString)).toThrow(
				'Error mapping HowToSupply \'name\'. Expected string but received "undefined".',
			);
		});

		test('should throw an error for invalid JSON', () => {
			const invalidJson = 'Invalid JSON string';

			expect(() => HowToSupply.fromJSON(invalidJson)).toThrow(
				'Error mapping to "HowToSupply". Received invalid JSON: "Invalid JSON string"',
			);
		});
	});
});
