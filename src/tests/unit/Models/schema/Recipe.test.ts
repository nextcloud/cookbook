import HowToDirection from 'cookbook/js/Models/schema/HowToDirection';
import HowToSection from 'cookbook/js/Models/schema/HowToSection';
import HowToSupply from 'cookbook/js/Models/schema/HowToSupply';
import HowToTool from 'cookbook/js/Models/schema/HowToTool';
import NutritionInformation from 'cookbook/js/Models/schema/NutritionInformation';
import Recipe from 'cookbook/js/Models/schema/Recipe';

describe('Recipe', () => {
	// constructor tests
	describe('constructor', () => {
		const recipeId = '123';
		const recipeName = 'Test Recipe';

		test('should create a Recipe instance with required properties', () => {
			const recipe = new Recipe(recipeId, recipeName);

			expect(recipe).toHaveProperty('@type', 'Recipe');
			expect(recipe.identifier).toBe(recipeId);
			expect(recipe.name).toBe(recipeName);
			expect(recipe.image).toStrictEqual([]);
			expect(recipe.imageUrl).toStrictEqual([]);
			expect(recipe.keywords).toStrictEqual([]);
			expect(recipe.recipeIngredient).toStrictEqual([]);
			expect(recipe.supply).toStrictEqual([]);
			expect(recipe.recipeInstructions).toStrictEqual([]);
			expect(recipe.tool).toStrictEqual([]);
			expect(recipe.url).toStrictEqual([]);
		});

		test('should set optional properties when provided in options', () => {
			const options = {
				recipeCategory: 'Dinner',
				dateCreated: '2022-01-01',
				dateModified: '2022-01-02',
				description: 'A delicious recipe',
				image: 'recipe-image.jpg',
				imageUrl: 'recipe-thumbnail.jpg',
				keywords: 'delicious, easy',
				totalTime: 'PT1H',
				cookTime: 'PT30M',
				prepTime: 'PT30M',
				nutrition: new NutritionInformation({
					calories: '100',
					carbohydrateContent: '20',
					proteinContent: '15',
					servingSize: '1 cup',
					sodiumContent: '200',
				}),
				recipeIngredient: '1 cup flour',
				recipeYield: 4,
				supply: new HowToSupply('Flour', '1 cup'),
				recipeInstructions: new HowToDirection('Mix the ingredients'),
				tool: new HowToTool('Mixing Bowl'),
				url: 'https://example.com/recipe',
			};

			const recipe = new Recipe(recipeId, recipeName, options);

			expect(recipe.recipeCategory).toBe(options.recipeCategory);
			expect(recipe.dateCreated).toBe(options.dateCreated);
			expect(recipe.dateModified).toBe(options.dateModified);
			expect(recipe.description).toBe(options.description);
			expect(recipe.image).toEqual([options.image]);
			expect(recipe.imageUrl).toEqual([options.imageUrl]);
			expect(recipe.keywords).toEqual([options.keywords]);
			expect(recipe.totalTime).toBe(options.totalTime);
			expect(recipe.cookTime).toBe(options.cookTime);
			expect(recipe.prepTime).toBe(options.prepTime);
			expect(recipe.nutrition).toEqual(options.nutrition);
			expect(recipe.recipeIngredient).toEqual([options.recipeIngredient]);
			expect(recipe.recipeYield).toBe(options.recipeYield);
			expect(recipe.supply).toEqual([options.supply]);
			expect(recipe.recipeInstructions).toEqual([
				options.recipeInstructions,
			]);
			expect(recipe.tool).toEqual([options.tool]);
			expect(recipe.url).toEqual([options.url]);
		});

		test('should handle undefined options', () => {
			const recipe = new Recipe(recipeId, recipeName, undefined);

			expect(recipe.recipeCategory).toBeUndefined();
			expect(recipe.dateCreated).toBeUndefined();
			expect(recipe.dateModified).toBeUndefined();
			expect(recipe.description).toBeUndefined();
			expect(recipe.image).toStrictEqual([]);
			expect(recipe.imageUrl).toStrictEqual([]);
			expect(recipe.keywords).toStrictEqual([]);
			expect(recipe.totalTime).toBeUndefined();
			expect(recipe.cookTime).toBeUndefined();
			expect(recipe.prepTime).toBeUndefined();
			expect(recipe.nutrition).toBeUndefined();
			expect(recipe.recipeIngredient).toStrictEqual([]);
			expect(recipe.recipeYield).toBeUndefined();
			expect(recipe.supply).toStrictEqual([]);
			expect(recipe.recipeInstructions).toStrictEqual([]);
			expect(recipe.tool).toStrictEqual([]);
			expect(recipe.url).toStrictEqual([]);
		});

		test('should handle options with undefined properties', () => {
			const options = {
				recipeCategory: undefined,
				dateCreated: undefined,
				dateModified: undefined,
				description: undefined,
				image: undefined,
				imageUrl: undefined,
				keywords: undefined,
				totalTime: undefined,
				cookTime: undefined,
				prepTime: undefined,
				nutrition: undefined,
				recipeIngredient: undefined,
				recipeYield: undefined,
				supply: undefined,
				recipeInstructions: undefined,
				tool: undefined,
				url: undefined,
			};

			const recipe = new Recipe(recipeId, recipeName, options);

			expect(recipe.recipeCategory).toBeUndefined();
			expect(recipe.dateCreated).toBeUndefined();
			expect(recipe.dateModified).toBeUndefined();
			expect(recipe.description).toBeUndefined();
			expect(recipe.image).toStrictEqual([]);
			expect(recipe.imageUrl).toStrictEqual([]);
			expect(recipe.keywords).toStrictEqual([]);
			expect(recipe.totalTime).toBeUndefined();
			expect(recipe.cookTime).toBeUndefined();
			expect(recipe.prepTime).toBeUndefined();
			expect(recipe.nutrition).toBeUndefined();
			expect(recipe.recipeIngredient).toStrictEqual([]);
			expect(recipe.recipeYield).toBeUndefined();
			expect(recipe.supply).toStrictEqual([]);
			expect(recipe.recipeInstructions).toStrictEqual([]);
			expect(recipe.tool).toStrictEqual([]);
			expect(recipe.url).toStrictEqual([]);
		});

		test('should return same value for id and identifier', () => {
			const recipe = new Recipe(recipeId, recipeName, undefined);

			expect(recipe.identifier).toBe(recipe.id);
		});
	});

	// fromJSON() tests
	describe('fromJSON', () => {
		test('should create a Recipe instance from valid JSON with minimal properties', () => {
			const minimalJson = {
				identifier: 'recipeMinimal',
				name: 'Minimal Recipe',
			};

			const recipe = Recipe.fromJSON(minimalJson);

			// Assertions
			expect(recipe.identifier).toBe('recipeMinimal');
			expect(recipe.name).toBe('Minimal Recipe');
		});

		test('should create a Recipe instance with default values for missing/undefined/null properties', () => {
			const jsonWithDefaults = {
				identifier: 'recipeWithDefaults',
				name: 'Recipe With Defaults',
				description: null,
				dateCreated: undefined,
				dateModified: null,
				image: undefined,
				imageUrl: null,
				keywords: null,
				cookTime: null,
				prepTime: undefined,
				totalTime: null,
				nutrition: null,
				recipeIngredient: undefined,
				recipeYield: null,
				supply: null,
				recipeInstructions: null,
				tool: undefined,
				url: null,
			};

			const recipe = Recipe.fromJSON(jsonWithDefaults);

			// Assertions
			expect(recipe.identifier).toBe('recipeWithDefaults');
			expect(recipe.name).toBe('Recipe With Defaults');
			expect(recipe.description).toBeUndefined();
			expect(recipe.dateCreated).toBeUndefined();
			expect(recipe.dateModified).toBeUndefined();
			expect(recipe.image).toEqual([]);
			expect(recipe.imageUrl).toEqual([]);
			expect(recipe.keywords).toEqual([]);
			expect(recipe.cookTime).toBeUndefined();
			expect(recipe.prepTime).toBeUndefined();
			expect(recipe.totalTime).toBeUndefined();
			expect(recipe.nutrition).toBeUndefined();
			expect(recipe.recipeIngredient).toEqual([]);
			expect(recipe.recipeYield).toBeUndefined();
			expect(recipe.supply).toEqual([]);
			expect(recipe.recipeInstructions).toEqual([]);
			expect(recipe.tool).toEqual([]);
			expect(recipe.url).toEqual([]);
		});

		test('should handle variations of valid JSON with null/undefined properties', () => {
			const jsonWithVariations = {
				identifier: 'recipeVariations',
				name: 'Recipe Variations',
				description: 'Some description',
				dateCreated: null,
				dateModified: '2022-02-15T10:00:00Z',
				image: ['image1.jpg', 'image2.jpg'],
				imageUrl: undefined,
				keywords: null,
				cookTime: 'PT45M',
				prepTime: null,
				totalTime: undefined,
				nutrition: {
					calories: null,
					fatContent: '10g',
				},
				recipeIngredient: 'Ingredient',
				recipeYield: null,
				supply: { name: 'Supply1' },
				recipeInstructions: [
					{
						'@type': 'HowToDirection',
						text: 'Step 1: Do something',
					},
					{
						'@type': 'HowToSection',
						name: 'Section 1',
						itemListElement: null,
					},
				],
				tool: undefined,
				url: 'https://example.com/recipeVariations',
			};

			const recipe = Recipe.fromJSON(jsonWithVariations);

			// Assertions
			expect(recipe.identifier).toBe('recipeVariations');
			expect(recipe.name).toBe('Recipe Variations');
			expect(recipe.description).toBe('Some description');
			expect(recipe.dateCreated).toBeUndefined();
			expect(recipe.dateModified).toBe('2022-02-15T10:00:00Z');
			expect(recipe.image).toEqual(['image1.jpg', 'image2.jpg']);
			expect(recipe.imageUrl).toEqual([]);
			expect(recipe.keywords).toEqual([]);
			expect(recipe.cookTime).toBe('PT45M');
			expect(recipe.prepTime).toBeUndefined();
			expect(recipe.totalTime).toBeUndefined();
			expect(recipe.nutrition).toBeInstanceOf(NutritionInformation);
			expect(recipe.recipeIngredient).toEqual(['Ingredient']);
			expect(recipe.recipeYield).toBeUndefined();
			expect(recipe.supply[0].name).toBe('Supply1');
			expect(recipe.recipeInstructions).toHaveLength(2);
			expect(recipe.recipeInstructions[0]).toBeInstanceOf(HowToDirection);
			expect(recipe.recipeInstructions[1]).toBeInstanceOf(HowToSection);
			expect(recipe.tool).toEqual([]);
			expect(recipe.url).toStrictEqual([
				'https://example.com/recipeVariations',
			]);
		});

		test('should throw an error for invalid JSON', () => {
			const invalidJson = 'Invalid JSON string';

			expect(() => Recipe.fromJSON(invalidJson)).toThrow(
				'Error mapping to "Recipe". Received invalid JSON: "Invalid JSON string"',
			);
		});

		test('should handle variations of valid JSON with arrays for properties supporting array values', () => {
			const jsonWithArrays = {
				identifier: 'recipeArrays',
				name: 'Recipe with arrays',
				image: ['image1.jpg', 'image2.jpg'], // Array value
				imageUrl: ['image3.jpg', 'image4.jpg'], // Array value
				keywords: ['keyword1', 'keyword2'], // Array value
				recipeIngredient: ['1 cup flour', '1 kg butter'], // Array value
				recipeInstructions: [
					{ text: 'Step 1: Do something' },
					{ text: 'Step 2: So something else' },
				], // Array value
				supply: [{ name: 'Supply1' }, { name: 'Supply2' }], // Array value
				tool: [{ name: 'Tool1' }, { name: 'Tool2' }], // Array value
				url: [
					'https://example.com/recipe',
					'https://example.com/recipe2',
				], // Array value
			};

			const recipe = Recipe.fromJSON(jsonWithArrays);

			// Assertions
			expect(recipe.identifier).toBe('recipeArrays');
			expect(recipe.name).toBe('Recipe with arrays');
			expect(recipe.image).toEqual(['image1.jpg', 'image2.jpg']);
			expect(recipe.imageUrl).toEqual(['image3.jpg', 'image4.jpg']);
			expect(recipe.keywords).toEqual(['keyword1', 'keyword2']);
			expect(recipe.recipeIngredient).toEqual([
				'1 cup flour',
				'1 kg butter',
			]);
			expect(recipe.recipeInstructions).toBeInstanceOf(
				Array<HowToDirection>,
			);
			expect((recipe.recipeInstructions[0] as HowToDirection).text).toBe(
				'Step 1: Do something',
			);
			expect((recipe.recipeInstructions[1] as HowToDirection).text).toBe(
				'Step 2: So something else',
			);
			expect(recipe.supply).toBeInstanceOf(Array<HowToSupply>);
			expect((recipe.supply[0] as HowToSupply).name).toBe('Supply1');
			expect((recipe.supply[1] as HowToSupply).name).toBe('Supply2');
			expect(recipe.tool).toBeInstanceOf(Array<HowToTool>);
			expect((recipe.tool[0] as HowToTool).name).toBe('Tool1');
			expect((recipe.tool[1] as HowToTool).name).toBe('Tool2');
			expect(recipe.url).toEqual([
				'https://example.com/recipe',
				'https://example.com/recipe2',
			]); // Converted to array
		});

		test('should handle variations of valid JSON with arrays for properties supporting single values', () => {
			const jsonWithArrays = {
				identifier: 'recipeArrays',
				name: 'Recipe with arrays',
				image: 'image1.jpg', // Single value
				imageUrl: 'image3.jpg', // Single value
				keywords: 'keyword1', // Single value
				recipeIngredient: '1 cup flour', // Single value
				recipeInstructions: { text: 'Step 1: Do something' }, // Single value
				supply: { name: 'Supply1' }, // Single value
				tool: { name: 'Tool1' }, // Single value
				url: 'https://example.com/recipe', // Single value
			};

			const recipe = Recipe.fromJSON(jsonWithArrays);

			// Assertions
			expect(recipe.identifier).toBe('recipeArrays');
			expect(recipe.name).toBe('Recipe with arrays');
			expect(recipe.image).toEqual(['image1.jpg']); // Converted to array
			expect(recipe.imageUrl).toEqual(['image3.jpg']); // Converted to array
			expect(recipe.keywords).toEqual(['keyword1']); // Converted to array
			expect(recipe.recipeIngredient).toEqual(['1 cup flour']); // Converted to array
			expect(recipe.recipeInstructions).toBeInstanceOf(
				Array<HowToDirection>,
			); // Converted to array
			expect((recipe.recipeInstructions[0] as HowToDirection).text).toBe(
				'Step 1: Do something',
			);
			expect(recipe.supply).toBeInstanceOf(Array<HowToSupply>); // Converted to array
			expect((recipe.supply[0] as HowToSupply).name).toBe('Supply1');
			expect(recipe.tool).toBeInstanceOf(Array<HowToTool>); // Converted to array
			expect((recipe.tool[0] as HowToTool).name).toBe('Tool1');
			expect(recipe.url).toEqual(['https://example.com/recipe']); // Converted to array
		});
	});
});
