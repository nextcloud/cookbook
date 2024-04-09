import { Recipe } from 'cookbook/js/Models/schema';
import { mapStringOrStringArray } from 'cookbook/js/utils/jsonMapper';

export function mapApiRecipeResponseToRecipe(recipeDTO: {
	id: string;
	identifier: string;
	keywords: string | string[];
	category: string | string[];
}): Recipe {
	// Create copy
	const recipe = JSON.parse(JSON.stringify(recipeDTO));

	// The cookbook API returns the `recipeCategory` property as `category`
	recipe.recipeCategory = recipeDTO.category;

	// The cookbook API returns the `identifier` property as `id`
	recipe.identifier = recipeDTO.id;
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

export function mapRecipeToApiRecipe(recipe: Recipe): string {
	// The cookbook API returns the `identifier` property as `id`
	const recipeDTO = JSON.parse(JSON.stringify(recipe));
	recipeDTO.id = recipe.identifier;
	return JSON.stringify(recipeDTO);
}
