import HowToDirection from '../../../../js/Models/schema/HowToDirection';
import HowToSupply from '../../../../js/Models/schema/HowToSupply';
import HowToTool from '../../../../js/Models/schema/HowToTool';

describe('HowToDirection', () => {
	describe('constructor', () => {
		test('should set "@type" property to "HowToDirection"', () => {
			const direction = new HowToDirection('Step 5');

			expect(direction).toHaveProperty('@type', 'HowToDirection');
		});

		test('should create an instance with only text', () => {
			const direction = new HowToDirection('Step 1');

			expect(direction).toBeInstanceOf(HowToDirection);
			expect(direction.text).toBe('Step 1');
			expect(direction.position).toBeUndefined();
			expect(direction.image).toStrictEqual([]);
			expect(direction.thumbnailUrl).toStrictEqual([]);
			expect(direction.timeRequired).toBeUndefined();
			expect(direction.supply).toStrictEqual([]);
			expect(direction.tool).toStrictEqual([]);
		});

		test('should create an instance with text and position', () => {
			const direction = new HowToDirection('Step 2', { position: 2 });

			expect(direction).toBeInstanceOf(HowToDirection);
			expect(direction.text).toBe('Step 2');
			expect(direction.position).toBe(2);
			expect(direction.image).toStrictEqual([]);
			expect(direction.thumbnailUrl).toStrictEqual([]);
			expect(direction.timeRequired).toBeUndefined();
			expect(direction.supply).toStrictEqual([]);
			expect(direction.tool).toStrictEqual([]);
		});

		test('should create an instance with all properties', () => {
			const image = ['image1.jpg', 'image2.jpg'];
			const thumbnailUrl = ['thumb1.jpg', 'thumb2.jpg'];
			const supply: HowToSupply[] = [{ name: 'Ingredient 1' }];
			const tool: HowToTool[] = [{ name: 'Tool 1' }];

			const direction = new HowToDirection('Step 3', {
				position: 3,
				image,
				thumbnailUrl,
				timeRequired: '5 minutes',
				supply,
				tool,
			});

			expect(direction).toBeInstanceOf(HowToDirection);
			expect(direction.text).toBe('Step 3');
			expect(direction.position).toBe(3);
			expect(direction.image).toEqual(image);
			expect(direction.thumbnailUrl).toEqual(thumbnailUrl);
			expect(direction.timeRequired).toBe('5 minutes');
			expect(direction.supply).toEqual(supply);
			expect(direction.tool).toEqual(tool);
		});

		test('should create an instance with only text and image string', () => {
			const image = 'image1.jpg';
			const thumbnailUrl = 'image1_thumb.jpg';
			const direction = new HowToDirection('Step 4', {
				image,
				thumbnailUrl,
			});

			expect(direction).toBeInstanceOf(HowToDirection);
			expect(direction.text).toBe('Step 4');
			expect(direction.position).toBeUndefined();
			expect(direction.image).toEqual([image]);
			expect(direction.thumbnailUrl).toEqual([thumbnailUrl]);
			expect(direction.timeRequired).toBeUndefined();
			expect(direction.supply).toStrictEqual([]);
			expect(direction.tool).toStrictEqual([]);
		});
	});

	// fromJSON tests
	describe('fromJSON', () => {
		test('should create a HowToDirection instance from valid JSON', () => {
			const json = {
				text: 'Mix the ingredients',
				position: 1,
				image: ['image1.jpg', 'image2.jpg'],
				thumbnailUrl: ['thumbnail1.jpg', 'thumbnail2.jpg'],
				timeRequired: '10 minutes',
				supply: [
					{
						name: 'Flour',
						requiredQuantity: {
							value: 200,
							unitText: 'grams',
						},
					},
					{
						name: 'Water',
						requiredQuantity: {
							value: 150,
							unitText: 'milliliters',
						},
					},
				],
				tool: [
					{
						name: 'Mixing Bowl',
					},
					{
						name: 'Spoon',
					},
				],
			};

			const result = HowToDirection.fromJSON(json);

			expect(result).toBeInstanceOf(HowToDirection);
			expect(result.text).toEqual('Mix the ingredients');
			expect(result.position).toEqual(1);
			expect(result.image).toEqual(['image1.jpg', 'image2.jpg']);
			expect(result.thumbnailUrl).toEqual([
				'thumbnail1.jpg',
				'thumbnail2.jpg',
			]);
			expect(result.timeRequired).toEqual('10 minutes');

			// Validate supply property
			expect(result.supply).toBeInstanceOf(Array);
			expect(result.supply[0]).toBeInstanceOf(HowToSupply);
			expect(result.supply[0].name).toEqual('Flour');
			expect(result.supply[0].requiredQuantity?.value).toEqual(200);
			expect(result.supply[0].requiredQuantity?.unitText).toEqual(
				'grams',
			);

			// Validate tool property
			expect(result.tool).toBeInstanceOf(Array);
			expect(result.tool[0]).toBeInstanceOf(HowToTool);
			expect(result.tool[0].name).toEqual('Mixing Bowl');
		});

		test('should create a HowToDirection instance from valid JSON with single tool and supply', () => {
			const json = {
				text: 'Mix the ingredients',
				position: 1,
				image: ['image1.jpg', 'image2.jpg'],
				thumbnailUrl: ['thumbnail1.jpg', 'thumbnail2.jpg'],
				timeRequired: '10 minutes',
				supply: {
					name: 'Flour',
					requiredQuantity: {
						value: 200,
						unitText: 'grams',
					},
				},
				tool: {
					name: 'Mixing Bowl',
				},
			};

			const result = HowToDirection.fromJSON(json);

			expect(result).toBeInstanceOf(HowToDirection);
			expect(result.text).toEqual('Mix the ingredients');
			expect(result.position).toEqual(1);
			expect(result.image).toEqual(['image1.jpg', 'image2.jpg']);
			expect(result.thumbnailUrl).toEqual([
				'thumbnail1.jpg',
				'thumbnail2.jpg',
			]);
			expect(result.timeRequired).toEqual('10 minutes');

			// Validate supply property
			expect(result.supply).toBeInstanceOf(Array);
			expect(result.supply[0]).toBeInstanceOf(HowToSupply);
			expect(result.supply[0].name).toEqual('Flour');
			expect(result.supply[0].requiredQuantity?.value).toEqual(200);
			expect(result.supply[0].requiredQuantity?.unitText).toEqual(
				'grams',
			);

			// Validate tool property
			expect(result.tool).toBeInstanceOf(Array);
			expect(result.tool[0]).toBeInstanceOf(HowToTool);
			expect(result.tool[0].name).toEqual('Mixing Bowl');
		});

		test('should handle missing optional properties', () => {
			const json = {
				text: 'Bake the cake',
			};

			const result = HowToDirection.fromJSON(json);

			expect(result).toBeInstanceOf(HowToDirection);
			expect(result.text).toEqual('Bake the cake');
			expect(result.position).toBeUndefined();
			expect(result.image).toEqual([]);
			expect(result.thumbnailUrl).toEqual([]);
			expect(result.timeRequired).toBeUndefined();
			expect(result.supply).toEqual([]);
			expect(result.tool).toEqual([]);
		});

		test('should throw an error for invalid JSON', () => {
			const invalidJson = 'Invalid JSON string';

			expect(() => HowToDirection.fromJSON(invalidJson)).toThrow(
				'Error mapping to "HowToDirection". Received invalid JSON: "Invalid JSON string"',
			);
		});

		test('should throw an error for missing required properties', () => {
			const json = {
				position: 1,
				// Missing required 'text' property
			};

			expect(() => HowToDirection.fromJSON(json)).toThrowError(
				'Error mapping HowToDirection \'text\'. Expected string but received "undefined".',
			);
		});

		test('should handle null or undefined values for optional properties', () => {
			const json = {
				text: 'Chop the vegetables',
				position: null,
				image: null,
				thumbnailUrl: undefined,
				timeRequired: undefined,
				supply: null,
				tool: undefined,
			};

			const result = HowToDirection.fromJSON(json);

			expect(result).toBeInstanceOf(HowToDirection);
			expect(result.text).toEqual('Chop the vegetables');
			expect(result.position).toBeUndefined();
			expect(result.image).toEqual([]);
			expect(result.thumbnailUrl).toEqual([]);
			expect(result.timeRequired).toBeUndefined();
			expect(result.supply).toEqual([]);
			expect(result.tool).toEqual([]);
		});
	});
});
