import Recipe from '../../../../js/Models/schema/Recipe';
import HowToDirection from '../../../../js/Models/schema/HowToDirection';
// import HowToSection from '../../../../js/Models/schema/HowToSection';
import HowToSupply from '../../../../js/Models/schema/HowToSupply';
import HowToTool from '../../../../js/Models/schema/HowToTool';
import NutritionInformation from '../../../../js/Models/schema/NutritionInformation';

describe('Recipe', () => {
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
		expect(recipe.recipeInstructions).toEqual([options.recipeInstructions]);
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
