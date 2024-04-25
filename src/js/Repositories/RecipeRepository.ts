import axios from '@nextcloud/axios';
import Vue from 'vue';
import { generateUrl } from '@nextcloud/router';
import { Recipe } from 'cookbook/js/Models/schema';
import {
	mapApiRecipeByCategoryResponseToRecipe,
	mapApiRecipeResponseToRecipe,
	mapRecipeToApiRecipe,
} from 'cookbook/js/Api/Mappers/RecipeMappers';

const baseUrl = `${generateUrl('apps/cookbook')}/webapp`;

// Add a debug log for every request
axios.interceptors.request.use((config) => {
	Vue.$log.debug(
		`[axios] Making "${config.method}" request to "${config.url}"`,
		config,
	);
	const contentType = config.headers['Content-Type'];
	if (
		contentType &&
		typeof contentType === 'string' &&
		!['application/json', 'text/json'].includes(contentType)
	) {
		Vue.$log.warn(
			`[axios] Request to "${config.url}" is using Content-Type "${contentType}", not JSON`,
		);
	}
	return config;
});

axios.interceptors.response.use(
	(response) => {
		Vue.$log.debug('[axios] Received response', response);
		return response;
	},
	(error) => {
		Vue.$log.warn('[axios] Received error', error);
		return Promise.reject(error);
	},
);

axios.defaults.headers.common.Accept = 'application/json';

class RecipeRepository {
	// eslint-disable-next-line class-methods-use-this
	async getRecipes(): Promise<Recipe[]> {
		try {
			const response = await axios.get(`${baseUrl}/recipes`);
			return response.data.map(mapApiRecipeResponseToRecipe);
		} catch (error) {
			Vue.$log.error(error);
			throw new Error('Failed to fetch recipes');
		}
	}

	// eslint-disable-next-line class-methods-use-this
	async getRecipeById(id: string): Promise<Recipe> {
		try {
			const response = await axios.get(`${baseUrl}/recipes/${id}`);
			return mapApiRecipeResponseToRecipe(response.data);
		} catch (error) {
			Vue.$log.error(error);
			throw new Error(`Failed to fetch recipe with ID ${id}`);
		}
	}

	// eslint-disable-next-line class-methods-use-this
	async createRecipe(recipe: Recipe): Promise<Recipe> {
		try {
			const recipeDTO = mapRecipeToApiRecipe(recipe);
			const response = await axios.post(`${baseUrl}/recipes`, recipeDTO);
			return mapApiRecipeResponseToRecipe(response.data);
		} catch (error) {
			Vue.$log.error(error);
			throw new Error('Failed to create recipe');
		}
	}

	// eslint-disable-next-line class-methods-use-this
	async updateRecipe(id: string, recipe: Recipe): Promise<Recipe> {
		try {
			const recipeDTO = mapRecipeToApiRecipe(recipe);
			const response = await axios.put(
				`${baseUrl}/recipes/${id}`,
				recipeDTO,
			);
			return mapApiRecipeResponseToRecipe(response.data);
		} catch (error) {
			Vue.$log.error(error);
			throw new Error(`Failed to update recipe with ID ${id}`);
		}
	}

	// eslint-disable-next-line class-methods-use-this
	async deleteRecipe(id: string): Promise<void> {
		try {
			await axios.delete(`${baseUrl}/recipes/${id}`);
		} catch (error) {
			Vue.$log.error(error);
			throw new Error(`Failed to delete recipe with ID ${id}`);
		}
	}

	// eslint-disable-next-line class-methods-use-this
	async getRecipesByCategory(categoryName: string): Promise<Recipe[]> {
		let response;

		// Fetch response
		try {
			response = await axios.get(`${baseUrl}/category/${categoryName}`);
		} catch (error) {
			Vue.$log.error(error);
			throw new Error(
				`Failed to fetch recipes of category ${categoryName}`,
			);
		}
		// Map response
		try {
			return response?.data.map(mapApiRecipeByCategoryResponseToRecipe);
			// return response !== null
			// 	? response.data.map(mapApiRecipeByCategoryResponseToRecipe)
			// 	: null;
		} catch (error) {
			Vue.$log.error(error);
			throw new Error(
				`Failed to fetch recipes of category ${categoryName}. Error mapping recipe objects.`,
			);
		}
	}

	// eslint-disable-next-line class-methods-use-this
	async getRecipesByTag(tags: string): Promise<Recipe[]> {
		let response;
		try {
			response = await axios.get(`${baseUrl}/tags/${tags}`);
		} catch (error) {
			Vue.$log.error(error);
			throw new Error(`Failed to fetch recipes with tags ${tags}`);
		}

		try {
			return response?.data.map(mapApiRecipeResponseToRecipe);
		} catch (error) {
			Vue.$log.error(error);
			throw new Error(
				`Failed to fetch recipes with tags ${tags}. Error mapping recipe objects.`,
			);
		}
	}

	// eslint-disable-next-line class-methods-use-this
	async searchRecipes(search: string): Promise<Recipe[]> {
		try {
			const response = await axios.get(`${baseUrl}/search/${search}`);
			return response.data.map(mapApiRecipeResponseToRecipe);
		} catch (error) {
			Vue.$log.error(error);
			throw new Error(`Failed to search recipes with query ${search}`);
		}
	}

	// eslint-disable-next-line class-methods-use-this
	async importRecipe(url: string): Promise<Recipe> {
		try {
			const response = await axios.post(
				`${baseUrl}/import`,
				`url=${url}`,
			);
			return mapApiRecipeResponseToRecipe(response.data);
		} catch (error) {
			Vue.$log.error(error);
			throw new Error(`Failed to import recipe from ${url}`);
		}
	}
}

export default RecipeRepository;
