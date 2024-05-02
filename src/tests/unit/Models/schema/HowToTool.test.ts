import HowToTool from '../../../../js/Models/schema/HowToTool';

describe('HowToTool', () => {
	describe('constructor', () => {
		test('should set the @type property to "HowToTool"', () => {
			const howToTool = new HowToTool('ToolA');
			expect(howToTool['@type']).toBe('HowToTool');
		});

		test('should create an instance of HowToTool with optional properties undefined', () => {
			const howToTool = new HowToTool('ToolA');
			expect(howToTool).toBeInstanceOf(HowToTool);
			expect(howToTool.name).toBe('ToolA');
			expect(howToTool.identifier).toBeUndefined();
			expect(howToTool.description).toBeUndefined();
			expect(howToTool.requiredQuantity).toBeUndefined();
		});

		test('should create an instance of HowToTool with some optional properties defined and some undefined', () => {
			const howToTool = new HowToTool('ToolA', 'TA123');
			expect(howToTool).toBeInstanceOf(HowToTool);
			expect(howToTool.name).toBe('ToolA');
			expect(howToTool.identifier).toBe('TA123');
			expect(howToTool.description).toBeUndefined();
			expect(howToTool.requiredQuantity).toBeUndefined();
		});

		test('should create an instance of HowToTool with all properties set', () => {
			const howToTool = new HowToTool(
				'ToolB',
				'TB123',
				'High-quality tool',
				{
					'@type': 'QuantitativeValue',
					value: 2,
					unitText: 'pcs',
					unitCode: undefined,
				},
			);

			expect(howToTool).toBeInstanceOf(HowToTool);
			expect(howToTool.name).toBe('ToolB');
			expect(howToTool.identifier).toBe('TB123');
			expect(howToTool.description).toBe('High-quality tool');
			expect(howToTool.requiredQuantity).toEqual({
				'@type': 'QuantitativeValue',
				value: 2,
				unitText: 'pcs',
				unitCode: undefined,
			});
		});
	});

	describe('fromJSON', () => {
		const createValidJSON = () => ({
			name: 'Knife',
			identifier: 'tool123',
			description: 'A sharp cutting tool',
			requiredQuantity: {
				'@type': 'QuantitativeValue',
				value: 1,
				unitText: 'unit',
			},
		});

		it('should create an instance from valid JSON', () => {
			const validJSON = createValidJSON();
			const tool = HowToTool.fromJSON(validJSON);
			expect(tool).toBeInstanceOf(HowToTool);
			expect(tool.name).toBe(validJSON.name);
			expect(tool.identifier).toBe(validJSON.identifier);
			expect(tool.description).toBe(validJSON.description);
			expect(tool.requiredQuantity).toBeDefined();
			// Add more specific checks for QuantitativeValue if needed
		});

		it('should create an instance from valid JSON string', () => {
			const validJSON = createValidJSON();
			const tool = HowToTool.fromJSON(JSON.stringify(validJSON));
			expect(tool).toBeInstanceOf(HowToTool);
			expect(tool.name).toBe(validJSON.name);
			expect(tool.identifier).toBe(validJSON.identifier);
			expect(tool.description).toBe(validJSON.description);
			expect(tool.requiredQuantity).toBeDefined();
			// Add more specific checks for QuantitativeValue if needed
		});

		it('should handle missing optional properties', () => {
			const validJSON = { name: 'Knife' };
			const tool = HowToTool.fromJSON(validJSON);
			expect(tool).toBeInstanceOf(HowToTool);
			expect(tool.name).toBe(validJSON.name);
			expect(tool.identifier).toBeUndefined();
			expect(tool.description).toBeUndefined();
			expect(tool.requiredQuantity).toBeUndefined();
		});

		it('should map integer property to string', () => {
			const validJSON = { name: 123 }; // integer 'name' should be automatically converted to string
			const tool = HowToTool.fromJSON(validJSON);
			expect(tool).toBeInstanceOf(HowToTool);
			expect(tool.name).toBe('123');
		});

		it('shouldmap integer property to string for valid JSON string', () => {
			const validJSONString = '{"name": 123}'; // integer 'name' should be automatically converted to string
			const tool = HowToTool.fromJSON(validJSONString);
			expect(tool).toBeInstanceOf(HowToTool);
			expect(tool.name).toBe('123');
		});

		it('should throw an error for invalid JSON with missing name property', () => {
			const invalidJSON = { prop: 123 }; // 'name' is missing
			expect(() => HowToTool.fromJSON(invalidJSON)).toThrow(
				'Error mapping HowToTool \'name\'. Expected string but received "undefined".',
			);
		});

		it('should throw an error for invalid JSON string with missing name property', () => {
			const invalidJSONString = '{"prop": 123}'; // 'name' is missing
			expect(() => HowToTool.fromJSON(invalidJSONString)).toThrow(
				'Error mapping HowToTool \'name\'. Expected string but received "undefined".',
			);
		});

		test('should throw an error for invalid JSON', () => {
			const invalidJson = 'Invalid JSON string';

			expect(() => HowToTool.fromJSON(invalidJson)).toThrow(
				'Error mapping to "HowToTool". Received invalid JSON: "Invalid JSON string"',
			);
		});
	});

	describe('fromJSONOrString', () => {
		const createValidJSON = () => ({
			name: 'Knife',
			identifier: 'tool123',
			description: 'A sharp cutting tool',
			requiredQuantity: {
				'@type': 'QuantitativeValue',
				value: 1,
				unitText: 'unit',
			},
		});

		const createValidSimpleJSON = () => ({
			name: 'Knife',
			requiredQuantity: 3,
		});

		it('should create an instance from valid JSON', () => {
			const validJSON = createValidJSON();
			const tool = HowToTool.fromJSONOrString(validJSON);
			expect(tool).toBeInstanceOf(HowToTool);
			expect(tool.name).toBe(validJSON.name);
			expect(tool.identifier).toBe(validJSON.identifier);
			expect(tool.description).toBe(validJSON.description);
			expect(tool.requiredQuantity).toBeDefined();
		});

		it('should create an instance from valid JSON string', () => {
			const validJSON = createValidJSON();
			const tool = HowToTool.fromJSONOrString(JSON.stringify(validJSON));
			expect(tool).toBeInstanceOf(HowToTool);
			expect(tool.name).toBe(validJSON.name);
			expect(tool.identifier).toBe(validJSON.identifier);
			expect(tool.description).toBe(validJSON.description);
			expect(tool.requiredQuantity).toBeDefined();
		});

		it('should throw an error for invalid JSON', () => {
			const validJSON = { name: 123 }; // integer 'name' should be automatically converted to string
			const tool = HowToTool.fromJSONOrString(JSON.stringify(validJSON));
			expect(tool).toBeInstanceOf(HowToTool);
			expect(tool.name).toBe('123');
		});

		it('should create new supply for invalid JSON string', () => {
			const invalidJSONString = 'Red spatula'; // 'name' should be a string
			expect(() => HowToTool.fromJSON(invalidJSONString)).toThrow();
			const tool = HowToTool.fromJSONOrString(invalidJSONString);
			expect(tool).toBeInstanceOf(HowToTool);
			expect(tool.name).toBe(invalidJSONString);
		});

		it('should create an instance from a simple valid JSON', () => {
			const validJSON = createValidSimpleJSON();
			const tool = HowToTool.fromJSONOrString(validJSON);
			expect(tool).toBeInstanceOf(HowToTool);
			expect(tool.name).toBe(validJSON.name);
			expect(tool.identifier).toBeUndefined();
			expect(tool.description).toBeUndefined();
			expect(tool.requiredQuantity).toBeDefined();
			expect(tool.requiredQuantity?.value).toBe(
				validJSON.requiredQuantity,
			);
		});

		it('should create an instance from a simple valid JSON string', () => {
			const validJSON = createValidSimpleJSON();
			const tool = HowToTool.fromJSONOrString(JSON.stringify(validJSON));
			expect(tool).toBeInstanceOf(HowToTool);
			expect(tool.name).toBe(validJSON.name);
			expect(tool.identifier).toBeUndefined();
			expect(tool.description).toBeUndefined();
			expect(tool.requiredQuantity).toBeDefined();
			expect(tool.requiredQuantity?.value).toBe(
				validJSON.requiredQuantity,
			);
		});
	});
});
