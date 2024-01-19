import HowToDirection from '../../../../js/Models/schema/HowToDirection';
import HowToSupply from '../../../../js/Models/schema/HowToSupply';
import HowToTool from '../../../../js/Models/schema/HowToTool';

describe('HowToDirection', () => {
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
		const direction = new HowToDirection('Step 4', { image, thumbnailUrl });

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
