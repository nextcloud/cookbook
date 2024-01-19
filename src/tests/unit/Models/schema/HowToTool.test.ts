import HowToTool from '../../../../js/Models/schema/HowToTool';

describe('HowToTool', () => {
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
		const howToTool = new HowToTool('ToolB', 'TB123', 'High-quality tool', {
			value: 2,
			unitText: 'pcs',
			unitCode: undefined,
		});

		expect(howToTool).toBeInstanceOf(HowToTool);
		expect(howToTool.name).toBe('ToolB');
		expect(howToTool.identifier).toBe('TB123');
		expect(howToTool.description).toBe('High-quality tool');
		expect(howToTool.requiredQuantity).toEqual({
			value: 2,
			unitText: 'pcs',
			unitCode: undefined,
		});
	});
});
