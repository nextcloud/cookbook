import { Recipe } from 'cookbook/js/Models/schema';
import { mapStringOrStringArray } from 'cookbook/js/utils/jsonMapper';

export function mapApiRecipeResponseToRecipe(recipeDTO: {
	id: string;
	recipe_id: string;
	identifier: string;
	keywords: string | string[];
	category: string | string[];
	recipeCategory: string | string[];
}): Recipe {
	// Create copy
	const recipe = JSON.parse(JSON.stringify(recipeDTO));

	// The cookbook API returns the `recipeCategory` property as `category` or `recipeCategory
	if (recipeDTO.recipeCategory) {
		recipe.recipeCategory = recipeDTO.recipeCategory;
	} else if (recipeDTO.category) {
		recipe.recipeCategory = recipeDTO.category;
	}

	// TODO This should be unified some time (when the backend returns consistent responses ;)
	// The cookbook API returns the `identifier` property as `id`
	if (recipeDTO.id) recipe.identifier = recipeDTO.id;
	else if (recipeDTO.recipe_id) recipe.identifier = recipeDTO.recipe_id;

	// The cookbook API currently returns the `keywords` property as a comma-separated list instead of an array
	const keywords = mapStringOrStringArray(
		recipeDTO.keywords,
		"Recipe 'keywords'",
		true,
		true,
	);
	recipe.keywords = keywords || [];

	return Recipe.fromJSON(recipe);
}

export function mapApiRecipeByCategoryResponseToRecipe(recipeDTO: {
	recipe_id: string;
	identifier: string;
	keywords: string | string[];
	category: string | string[];
}): Recipe {
	// Create copy
	const recipe = JSON.parse(JSON.stringify(recipeDTO));

	// The cookbook API returns the `recipeCategory` property as `category`
	recipe.recipeCategory = recipeDTO.category;

	// The cookbook API returns the `identifier` property as `id`
	recipe.identifier = recipeDTO.recipe_id;
	// The cookbook API returns the `keywords` property as a comma-separated list instead of an array
	const keywords = mapStringOrStringArray(
		recipeDTO.keywords,
		"Recipe 'keywords'",
		true,
		true,
	);
	recipe.keywords = keywords || [];

	return Recipe.fromJSON(recipe);
}

export function mapRecipeToApiRecipe(recipe: Recipe): object {
	// The cookbook API returns the `identifier` property as `id`
	const recipeDTO = JSON.parse(JSON.stringify(recipe));
	recipeDTO.id = recipe.identifier;
	return recipeDTO;
}
