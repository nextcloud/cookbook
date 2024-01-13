import api from 'cookbook/js/api-interface';

import { generateUrl } from '@nextcloud/router';
import { AxiosResponse } from 'axios/index';

interface Recipe {
	id: string;
	name: string;
}

/**
 * Relative base URL of the cookbook app.
 */
const baseUrl = generateUrl('apps/cookbook');

/**
 * Extracts a list of unique recipe ids from recipe references in `content`.
 * @param content The text to search for recipe references.
 * @returns List of unique recipe ids.
 */
function extractAllRecipeLinkIds(content: string): string[] {
	const re = /(?:^|[^#])#r\/([0-9]+)/g;
	let ret: string[] = [];
	let matches: RegExpExecArray | null;
	for (
		matches = re.exec(content);
		matches !== null;
		matches = re.exec(content)
	) {
		ret.push(matches[1]);
	}

	// Make the ids unique, see https://stackoverflow.com/a/14438954/882756
	function onlyUnique(value: string, index: number, self: string[]): boolean {
		return self.indexOf(value) === index;
	}
	ret = ret.filter(onlyUnique);

	return ret;
}

/**
 * Loads recipe data from the server for all recipes with ids in `linkIds`.
 * @param linkIds List of recipe ids.
 * @returns List of API responses with recipe data.
 */
async function getRecipesFromLinks(
	linkIds: string[],
): Promise<(AxiosResponse<Recipe> | null)[]> {
	return Promise.all(
		linkIds.map(async (x): Promise<AxiosResponse<Recipe> | null> => {
			let recipeResponse: AxiosResponse<Recipe> | null;
			try {
				recipeResponse = await api.recipes.get(x);
			} catch (ex) {
				recipeResponse = null;
			}
			return recipeResponse;
		}),
	);
}

/**
 * Takes list of response objects from the API call, removes all null objects and extracts only the response data.
 * @param recipes List of response objects.
 * @returns The response data without null objects.
 */
function cleanUpRecipeList(
	recipes: (AxiosResponse<Recipe> | null)[],
): Recipe[] {
	return recipes.filter((r) => r !== null).map((x) => x!.data);
}

/**
 * Constructs the relative URL to the recipe with identifier `id`.
 * @param id Recipe id.
 * @returns URL to recipe.
 */
function getRecipeUrl(id: string): string {
	return `${baseUrl}/#/recipe/${id}`;
}

/**
 * Replaces all recipe references in `content` by the Markdown link to the recipes.
 * @param content Text containing recipe references.
 * @param recipes List of recipe objects used to extract names and create links.
 * @returns Text with inserted links.
 */
function insertMarkdownLinks(content: string, recipes: Recipe[]) {
	let ret = content;
	recipes.forEach((r) => {
		const { id, name } = r;

		// Replace link urls in dedicated links (like [this example](#r/123))
		ret = ret.replace(`](${id})`, `](${getRecipeUrl(id)})`);

		// Replace plain references with recipe name
		const rePlain = RegExp(
			`(^|\\s|[,._+&?!-])#r/${id}($|\\s|[,._+&?!-])`,
			'g',
		);
		// const re = /(^|\s|[,._+&?!-])#r\/(\d+)(?=$|\s|[.,_+&?!-])/g
		ret = ret.replace(
			rePlain,
			`$1[${name} (\\#r/${id})](${getRecipeUrl(id)})$2`,
		);
	});
	return ret;
}

/**
 * Checks the `content` for reference to recipes and replaces them by Markdown links with the recipes' titles.
 * @param content The text to search for and replace recipe references.
 * @returns `content` with replaced references.
 */
async function normalizeNamesMarkdown(content: string): Promise<string> {
	// console.log(`Content: ${content}`)
	const linkIds = extractAllRecipeLinkIds(content);
	const recipeResponses = await getRecipesFromLinks(linkIds);
	const recipes = cleanUpRecipeList(recipeResponses);
	// console.log("List of recipes", recipes)

	const markdown = insertMarkdownLinks(content, recipes);
	// console.log("Formatted markdown:", markdown)

	return markdown;
}

export default normalizeNamesMarkdown;
