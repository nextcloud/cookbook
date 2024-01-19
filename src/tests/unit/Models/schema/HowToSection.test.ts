import HowToDirection from '../../../../js/Models/schema/HowToDirection';
import HowToSection from '../../../../js/Models/schema/HowToSection';

describe('HowToSection', () => {
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
