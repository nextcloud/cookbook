import HowToDirection from '../../../../js/Models/schema/HowToDirection';
import HowToSection from '../../../../js/Models/schema/HowToSection';

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
				thumbnailUrl: 'thumbnail.jpg',
				itemListElement: new HowToDirection('Step 1'),
			};

			const section = new HowToSection('Section 2', options);

			expect(section.description).toBe(options.description);
			expect(section.position).toBe(options.position);
			expect(section.image).toEqual([options.image]);
			expect(section.thumbnailUrl).toEqual([options.thumbnailUrl]);
			expect(section.itemListElement).toEqual([options.itemListElement]);
		});

		test('should handle undefined options', () => {
			const section = new HowToSection('Section 3', undefined);

			expect(section.description).toBeUndefined();
			expect(section.position).toBeUndefined();
			expect(section.image).toEqual([]);
			expect(section.thumbnailUrl).toEqual([]);
			expect(section.itemListElement).toEqual([]);
		});

		test('should handle options with undefined properties', () => {
			const options = {
				description: undefined,
				position: undefined,
				image: undefined,
				thumbnailUrl: undefined,
				itemListElement: undefined,
			};

			const section = new HowToSection('Section 4', options);

			expect(section.description).toBeUndefined();
			expect(section.position).toBeUndefined();
			expect(section.image).toEqual([]);
			expect(section.thumbnailUrl).toEqual([]);
			expect(section.itemListElement).toEqual([]);
		});
	});

	// fromJSON tests
	describe('fromJSON', () => {
		test('should create a HowToSection instance from valid JSON', () => {
			const json = {
				name: 'Mixing',
				description: 'Mixing ingredients',
				position: 2,
				image: 'section_image.jpg',
				thumbnailUrl: 'section_thumbnail.jpg',
				itemListElement: [
					{
						text: 'Mix the flour and water',
						position: 1,
						image: ['direction_image.jpg'],
						thumbnailUrl: ['direction_thumbnail.jpg'],
					},
					{
						text: 'Stir the mixture',
						position: 2,
						image: 'stir_image.jpg',
						thumbnailUrl: 'stir_thumbnail.jpg',
					},
				],
			};

			const result = HowToSection.fromJSON(json);

			expect(result).toBeInstanceOf(HowToSection);
			expect(result.name).toEqual('Mixing');
			expect(result.description).toEqual('Mixing ingredients');
			expect(result.position).toEqual(2);
			expect(result.image).toEqual(['section_image.jpg']);
			expect(result.thumbnailUrl).toEqual(['section_thumbnail.jpg']);

			// Validate itemListElement property
			expect(result.itemListElement).toBeInstanceOf(Array);
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

			expect(result.itemListElement[1]).toBeInstanceOf(HowToDirection);
			expect(result.itemListElement[1].text).toEqual('Stir the mixture');
			expect(result.itemListElement[1].position).toEqual(2);
			expect(result.itemListElement[1].image).toEqual(['stir_image.jpg']);
			expect(result.itemListElement[1].thumbnailUrl).toEqual([
				'stir_thumbnail.jpg',
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
			expect(result.thumbnailUrl).toEqual([]);

			// Validate itemListElement property
			expect(result.itemListElement).toBeInstanceOf(Array);
			expect(result.itemListElement[0]).toBeInstanceOf(HowToDirection);
			expect(result.itemListElement[0].text).toEqual('Preheat the oven');
			expect(result.itemListElement[0].position).toEqual(1);
			expect(result.itemListElement[0].image).toEqual([]);
			expect(result.itemListElement[0].thumbnailUrl).toEqual([]);
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
				itemListElement: null,
			};

			const result = HowToSection.fromJSON(json);

			expect(result).toBeInstanceOf(HowToSection);
			expect(result.name).toEqual('Chopping');
			expect(result.description).toBeUndefined();
			expect(result.position).toBeUndefined();
			expect(result.image).toEqual([]);
			expect(result.thumbnailUrl).toEqual([]);
			expect(result.itemListElement).toEqual([]);
		});
	});
});
