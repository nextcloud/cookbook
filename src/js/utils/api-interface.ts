import Vue from 'vue';
import axios from '@nextcloud/axios';

import { generateUrl } from '@nextcloud/router';
import { Recipe } from 'cookbook/js/Models/schema';
import {
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

function createNewRecipe(recipe) {
	return axios.post(`${baseUrl}/recipes`, recipe);
}

async function getRecipe(id: string): Promise<Recipe> {
	const response = await axios.get(`${baseUrl}/recipes/${id}`);
	console.log(response);
	return mapApiRecipeResponseToRecipe(response.data);
}

function getAllRecipes() {
	return axios.get(`${baseUrl}/recipes`);
}

function getAllRecipesOfCategory(categoryName) {
	return axios.get(`${baseUrl}/category/${categoryName}`);
}

function getAllRecipesWithTag(tags) {
	return axios.get(`${baseUrl}/tags/${tags}`);
}

function searchRecipes(search) {
	return axios.get(`${baseUrl}/search/${search}`);
}

function updateRecipe(id: string, recipe: Recipe) {
	const recipeDTO = mapRecipeToApiRecipe(recipe);
	return axios.put(`${baseUrl}/recipes/${id}`, recipeDTO);
}

function deleteRecipe(id) {
	return axios.delete(`${baseUrl}/recipes/${id}`);
}

function importRecipe(url) {
	return axios.post(`${baseUrl}/import`, `url=${url}`);
}

function getAllCategories() {
	return axios.get(`${baseUrl}/categories`);
}

function updateCategoryName(oldName, newName) {
	return axios.put(`${baseUrl}/category/${encodeURIComponent(oldName)}`, {
		name: newName,
	});
}

function getAllKeywords() {
	return axios.get(`${baseUrl}/keywords`);
}

function getConfig() {
	return axios.get(`${baseUrl}/config`);
}

function updatePrintImageSetting(enabled) {
	return axios.post(`${baseUrl}/config`, { print_image: enabled ? 1 : 0 });
}

function updateUpdateInterval(newInterval) {
	return axios.post(`${baseUrl}/config`, { update_interval: newInterval });
}

function updateRecipeDirectory(newDir) {
	return axios.post(`${baseUrl}/config`, { folder: newDir });
}

function updateVisibleInfoBlocks(visibleInfoBlocks) {
	return axios.post(`${baseUrl}/config`, { visibleInfoBlocks });
}

function reindex() {
	return axios.post(`${baseUrl}/reindex`);
}

export default {
	recipes: {
		create: createNewRecipe,
		getAll: getAllRecipes,
		get: getRecipe,
		allInCategory: getAllRecipesOfCategory,
		allWithTag: getAllRecipesWithTag,
		search: searchRecipes,
		update: updateRecipe,
		delete: deleteRecipe,
		import: importRecipe,
		reindex,
	},
	categories: {
		getAll: getAllCategories,
		update: updateCategoryName,
	},
	keywords: {
		getAll: getAllKeywords,
	},
	config: {
		get: getConfig,
		directory: {
			update: updateRecipeDirectory,
		},
		printImage: {
			update: updatePrintImageSetting,
		},
		updateInterval: {
			update: updateUpdateInterval,
		},
		visibleInfoBlocks: {
			update: updateVisibleInfoBlocks,
		},
	},
};
