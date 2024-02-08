import HowToDirection from 'cookbook/js/Models/schema/HowToDirection';
import HowToSection from 'cookbook/js/Models/schema/HowToSection';
import HowToStep from 'cookbook/js/Models/schema/HowToStep';
import HowToTip from 'cookbook/js/Models/schema/HowToTip';

describe('HowToSection', () => {
	// constructor tests
	describe('constructor', () => {
		test('should create a HowToSection instance with required properties', () => {
			const section = new HowToSection('Section 1');

			expect(section).toHaveProperty('@type', 'HowToSection');
			expect(section.name).toBe('Section 1');
		});

		test('should set optional properties when provided in options', () => {
			const options = {
				description: 'Section description',
				position: 2,
				image: 'section-image.jpg',
				timeRequired: '5 minutes',
				thumbnailUrl: 'thumbnail.jpg',
				itemListElement: new HowToDirection('Step 1'),
			};

			const section = new HowToSection('Section 2', options);

			expect(section.description).toBe(options.description);
			expect(section.position).toBe(options.position);
			expect(section.image).toEqual([options.image]);
			expect(section.timeRequired).toEqual(options.timeRequired);
			expect(section.thumbnailUrl).toEqual([options.thumbnailUrl]);
			expect(section.itemListElement).toEqual([options.itemListElement]);
		});

		test('should handle undefined options', () => {
			const section = new HowToSection('Section 3', undefined);

			expect(section.description).toBeUndefined();
			expect(section.position).toBeUndefined();
			expect(section.image).toEqual([]);
			expect(section.timeRequired).toBeUndefined();
			expect(section.thumbnailUrl).toEqual([]);
			expect(section.itemListElement).toEqual([]);
		});

		test('should handle options with undefined properties', () => {
			const options = {
				description: undefined,
				position: undefined,
				image: undefined,
				timeRequired: undefined,
				thumbnailUrl: undefined,
				itemListElement: undefined,
			};

			const section = new HowToSection('Section 4', options);

			expect(section.description).toBeUndefined();
			expect(section.position).toBeUndefined();
			expect(section.image).toEqual([]);
			expect(section.timeRequired).toBeUndefined();
			expect(section.thumbnailUrl).toEqual([]);
			expect(section.itemListElement).toEqual([]);
		});
	});

	// fromJSON tests
	describe('fromJSON', () => {
		test('should create a HowToSection instance with HowToDirection, HowToStep, and HowToTip elements', () => {
			const json = {
				name: 'Mixing',
				description: 'Mixing ingredients',
				position: 2,
				image: 'section_image.jpg',
				timeRequired: '5 minutes',
				thumbnailUrl: 'section_thumbnail.jpg',
				itemListElement: [
					{
						'@type': 'HowToDirection',
						text: 'Mix the flour and water',
						position: 1,
						image: ['direction_image.jpg'],
						thumbnailUrl: ['direction_thumbnail.jpg'],
					},
					{
						'@type': 'HowToStep',
						text: 'Stir the mixture',
						position: 2,
						image: 'stir_image.jpg',
						thumbnailUrl: 'stir_thumbnail.jpg',
					},
					{
						'@type': 'HowToTip',
						text: 'Preheat the oven before mixing',
						position: 3,
						image: 'tip_image.jpg',
						thumbnailUrl: 'tip_thumbnail.jpg',
					},
				],
			};

			const result = HowToSection.fromJSON(json);

			expect(result).toBeInstanceOf(HowToSection);
			expect(result.name).toEqual('Mixing');
			expect(result.description).toEqual('Mixing ingredients');
			expect(result.position).toEqual(2);
			expect(result.image).toEqual(['section_image.jpg']);
			expect(result.timeRequired).toEqual('5 minutes');
			expect(result.thumbnailUrl).toEqual(['section_thumbnail.jpg']);

			// Validate itemListElement property
			expect(result.itemListElement).toBeInstanceOf(Array);
			expect(result.itemListElement.length).toEqual(3);

			// Validate HowToDirection
			expect(result.itemListElement[0]).toBeInstanceOf(HowToDirection);
			expect(result.itemListElement[0].text).toEqual(
				'Mix the flour and water',
			);
			expect(result.itemListElement[0].position).toEqual(1);
			expect(result.itemListElement[0].image).toEqual([
				'direction_image.jpg',
			]);
			expect(result.itemListElement[0].thumbnailUrl).toEqual([
				'direction_thumbnail.jpg',
			]);

			// Validate HowToStep
			expect(result.itemListElement[1]).toBeInstanceOf(HowToStep);
			expect(result.itemListElement[1].text).toEqual('Stir the mixture');
			expect(result.itemListElement[1].position).toEqual(2);
			expect(result.itemListElement[1].image).toEqual(['stir_image.jpg']);
			expect(result.itemListElement[1].thumbnailUrl).toEqual([
				'stir_thumbnail.jpg',
			]);

			// Validate HowToTip
			expect(result.itemListElement[2]).toBeInstanceOf(HowToTip);
			expect(result.itemListElement[2].text).toEqual(
				'Preheat the oven before mixing',
			);
			expect(result.itemListElement[2].position).toEqual(3);
			expect(result.itemListElement[2].image).toEqual(['tip_image.jpg']);
			expect(result.itemListElement[2].thumbnailUrl).toEqual([
				'tip_thumbnail.jpg',
			]);
		});

		test('should handle missing optional properties', () => {
			const json = {
				name: 'Baking',
				itemListElement: [
					{
						text: 'Preheat the oven',
						position: 1,
					},
				],
			};

			const result = HowToSection.fromJSON(json);

			expect(result).toBeInstanceOf(HowToSection);
			expect(result.name).toEqual('Baking');
			expect(result.description).toBeUndefined();
			expect(result.position).toBeUndefined();
			expect(result.image).toEqual([]);
			expect(result.timeRequired).toBeUndefined();
			expect(result.thumbnailUrl).toEqual([]);

			// Validate itemListElement property
			expect(result.itemListElement).toBeInstanceOf(Array);
			expect(result.itemListElement[0]).toBeInstanceOf(HowToDirection);
			expect(result.itemListElement[0].text).toEqual('Preheat the oven');
			expect(result.itemListElement[0].position).toEqual(1);
			expect(result.itemListElement[0].image).toEqual([]);
			expect(result.itemListElement[0].thumbnailUrl).toEqual([]);
		});

		test('should handle string subitems', () => {
			const json = {
				name: 'Baking',
				itemListElement: ['Preheat the oven', 'Chop the olives'],
			};

			const result = HowToSection.fromJSON(json);

			expect(result).toBeInstanceOf(HowToSection);
			expect(result.name).toEqual('Baking');

			// Validate itemListElement property
			expect(result.itemListElement).toBeInstanceOf(Array);
			expect(result.itemListElement[0]).toBeInstanceOf(HowToDirection);
			expect(result.itemListElement[0].text).toEqual('Preheat the oven');
			expect(result.itemListElement[1]).toBeInstanceOf(HowToDirection);
			expect(result.itemListElement[1].text).toEqual('Chop the olives');
		});

		test('should throw an error for invalid JSON', () => {
			const invalidJson = 'Invalid JSON string';

			expect(() => HowToSection.fromJSON(invalidJson)).toThrow(
				'Error mapping to "HowToSection". Received invalid JSON: "Invalid JSON string"',
			);
		});

		test('should throw an error for missing required properties', () => {
			const json = {
				// Missing required 'name' property
			};

			expect(() => HowToSection.fromJSON(json)).toThrow(
				'Error mapping HowToSection \'name\'. Expected string but received "undefined".',
			);
		});

		test('should handle null or undefined values for optional properties', () => {
			const json = {
				name: 'Chopping',
				description: null,
				position: undefined,
				image: null,
				thumbnailUrl: undefined,
				timeRequired: undefined,
				itemListElement: null,
			};

			const result = HowToSection.fromJSON(json);

			expect(result).toBeInstanceOf(HowToSection);
			expect(result.name).toEqual('Chopping');
			expect(result.description).toBeUndefined();
			expect(result.position).toBeUndefined();
			expect(result.image).toEqual([]);
			expect(result.timeRequired).toBeUndefined();
			expect(result.thumbnailUrl).toEqual([]);
			expect(result.itemListElement).toEqual([]);
		});
	});
});
