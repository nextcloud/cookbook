import axios from '@nextcloud/axios';
import { generateUrl } from '@nextcloud/router';
import { Recipe } from 'cookbook/js/Models/schema';
import {
	mapApiRecipeResponseToRecipe,
	mapRecipeToApiRecipe,
} from 'cookbook/js/Api/Mappers/RecipeMappers';

const baseUrl = `${generateUrl('apps/cookbook')}/webapp`;

class RecipeRepository {
	// eslint-disable-next-line class-methods-use-this
	async getRecipes(): Promise<Recipe[]> {
		try {
			const response = await axios.get(`${baseUrl}/recipes`);
			return response.data.map(mapApiRecipeResponseToRecipe);
		} catch (error) {
			throw new Error('Failed to fetch recipes');
		}
	}

	async getRecipeById(id: string): Promise<Recipe> {
		try {
			const response = await axios.get(`${baseUrl}/recipes/${id}`);
			return mapApiRecipeResponseToRecipe(response.data);
		} catch (error) {
			throw new Error(`Failed to fetch recipe with ID ${id}`);
		}
	}

	async createRecipe(recipe: Recipe): Promise<Recipe> {
		try {
			const recipeDTO = mapRecipeToApiRecipe(recipe);
			const response = await axios.post(`${baseUrl}/recipes`, recipeDTO);
			return mapApiRecipeResponseToRecipe(response.data);
		} catch (error) {
			throw new Error('Failed to create recipe');
		}
	}

	async updateRecipe(id: string, recipe: Recipe): Promise<Recipe> {
		try {
			const recipeDTO = mapRecipeToApiRecipe(recipe);
			const response = await axios.put(
				`${baseUrl}/recipes/${id}`,
				recipeDTO,
			);
			return mapApiRecipeResponseToRecipe(response.data);
		} catch (error) {
			throw new Error(`Failed to update recipe with ID ${id}`);
		}
	}

	async deleteRecipe(id: string): Promise<void> {
		try {
			await axios.delete(`${baseUrl}/recipes/${id}`);
		} catch (error) {
			throw new Error(`Failed to delete recipe with ID ${id}`);
		}
	}

	async getAllRecipesOfCategory(categoryName: string): Promise<Recipe[]> {
		try {
			const response = await axios.get(
				`${baseUrl}/category/${categoryName}`,
			);
			return response.data.map(mapApiRecipeResponseToRecipe);
		} catch (error) {
			throw new Error(
				`Failed to fetch recipes of category ${categoryName}`,
			);
		}
	}

	async getAllRecipesWithTag(tags: string): Promise<Recipe[]> {
		try {
			const response = await axios.get(`${baseUrl}/tags/${tags}`);
			return response.data.map(mapApiRecipeResponseToRecipe);
		} catch (error) {
			throw new Error(`Failed to fetch recipes with tags ${tags}`);
		}
	}

	async searchRecipes(search: string): Promise<Recipe[]> {
		try {
			const response = await axios.get(`${baseUrl}/search/${search}`);
			return response.data.map(mapApiRecipeResponseToRecipe);
		} catch (error) {
			throw new Error(`Failed to search recipes with query ${search}`);
		}
	}

	async importRecipe(url: string): Promise<Recipe> {
		try {
			const response = await axios.post(
				`${baseUrl}/import`,
				`url=${url}`,
			);
			return mapApiRecipeResponseToRecipe(response.data);
		} catch (error) {
			throw new Error(`Failed to import recipe from ${url}`);
		}
	}
}

export default RecipeRepository;
