import JsonMappingException from 'cookbook/js/Exceptions/JsonMappingException';
import {
	mapInteger,
	mapString,
	mapStringOrStringArray,
} from 'cookbook/js/utils/jsonMapper';
import HowToDirection from './HowToDirection';
import HowToSection from './HowToSection';
import HowToSupply from './HowToSupply';
import HowToTool from './HowToTool';
import NutritionInformation from './NutritionInformation';
import { asArray } from '../../helper';

/** Options for creating a recipe */
interface RecipeOptions {
	/** The category of the recipe. */
	recipeCategory?: string;
	/** The timestamp of the recipe's creation date. */
	dateCreated?: string;
	/** The timestamp of the recipe's modification date. */
	dateModified?: string;
	/** The description of the recipe. */
	description?: string;
	/** The original image Urls of the recipe. */
	image?: string | string[];
	/** Urls to the images of the recipe on the Nextcloud instance. */
	imageUrl?: string | string[];
	/** The keywords of the recipe. */
	keywords?: string | string[];
	/** The total time required for the recipe. */
	totalTime?: string;
	/** The time it takes to actually cook the dish, in ISO 8601 duration format. */
	cookTime?: string;
	/** The length of time it takes to prepare the items to be used in instructions or a direction, in ISO 8601 duration
	 *  format. */
	prepTime?: string;
	/** Nutritional information about the recipe. */
	nutrition?: NutritionInformation;
	/** The list of ingredients for the recipe. */
	recipeIngredient?: string | string[];
	/** The number of servings for the recipe */
	recipeYield?: number;
	/** The list of supplies needed for the recipe. */
	supply?: HowToSupply | HowToSupply[];
	/** The step-by-step instructions for the recipe. */
	recipeInstructions?:
		| HowToSection
		| HowToDirection
		| (HowToSection | HowToDirection)[];
	/** The tools required for the recipe. */
	tool?: HowToTool | HowToTool[];
	/** The URL of the recipe. */
	url?: string | string[];
}

/**
 * Represents a Recipe in Schema.org standard.
 * @class
 */
export default class Recipe {
	/** The unique identifier of the recipe */
	public identifier: string;

	/** The name/title of the recipe */
	public name: string;

	/** The category of the recipe. */
	public recipeCategory?: string;

	/** The original image Urls of the recipe. */
	public image: string[];

	/** Urls to the images of the recipe on the Nextcloud instance. */
	public imageUrl: string[];

	/** The keywords of the recipe. */
	public keywords: string[];

	/** The total time required for the recipe. */
	public totalTime: string | undefined;

	/** The time it takes to actually cook the dish, in ISO 8601 duration format. */
	public cookTime: string | undefined;

	/** The length of time it takes to prepare the items to be used in instructions or a direction, in ISO 8601 duration
	 *  format. */
	public prepTime: string | undefined;

	/** The timestamp of the recipe's creation date. */
	public dateCreated: string | undefined;

	/** The timestamp of the recipe's modification date. */
	public dateModified: string | undefined;

	/** The description of the recipe. */
	public description: string | undefined;

	/** Nutritional information about the recipe. */
	public nutrition: NutritionInformation | undefined;

	/** The list of ingredients for the recipe. */
	public recipeIngredient: string[];

	/** The number of servings for the recipe */
	public recipeYield: number | undefined;

	/** The list of supplies needed for the recipe. */
	public supply: HowToSupply[];

	/** The step-by-step instructions for the recipe. */
	public recipeInstructions: (HowToSection | HowToDirection)[];

	/** The tools required for the recipe. */
	public tool: HowToTool[];

	/** The URLs associated with the recipe. In the current setup, should be a single URL, but let's already allow an
	 * array of URLs.  */
	public url: string[];

	constructor(identifier: string, name: string, options: RecipeOptions = {}) {
		this['@context'] = 'https://schema.org';
		this['@type'] = 'Recipe';
		this.identifier = identifier;
		this.name = name;
		// if (options) {
		this.recipeCategory = options.recipeCategory || undefined;
		this.description = options.description || undefined;
		this.dateCreated = options.dateCreated || undefined;
		this.dateModified = options.dateModified || undefined;
		this.image = options.image ? asArray(options.image) : [];
		this.imageUrl = options.imageUrl ? asArray(options.imageUrl) : [];
		this.keywords = options.keywords ? asArray(options.keywords) : [];
		this.cookTime = options.cookTime || undefined;
		this.prepTime = options.prepTime || undefined;
		this.totalTime = options.totalTime || undefined;
		this.nutrition = options.nutrition || undefined;
		this.recipeIngredient = options.recipeIngredient
			? asArray(options.recipeIngredient)
			: [];
		this.recipeYield = options.recipeYield;
		this.supply = options.supply ? asArray(options.supply) : [];
		this.recipeInstructions = options.recipeInstructions
			? asArray(options.recipeInstructions)
			: [];
		this.tool = options.tool ? asArray(options.tool) : [];
		this.url = options.url ? asArray(options.url) : [];
	}

	/**
	 * The unique identifier of the recipe object. This is equivalent to `identifier` in schema.org.
	 */
	get id(): string {
		return this.identifier;
	}

	/**
	 * Create a `Recipe` instance from a JSON string or object.
	 * @param {string | object} json - The JSON string or object.
	 * @returns {Recipe} - The created Recipe instance.
	 * @throws {Error} If the input JSON is invalid or missing required properties.
	 */
	static fromJSON(json: string | object): Recipe {
		// eslint-disable-next-line @typescript-eslint/no-explicit-any
		let jsonObj: any;
		try {
			jsonObj = typeof json === 'string' ? JSON.parse(json) : json;
		} catch {
			throw new JsonMappingException(
				`Error mapping to "Recipe". Received invalid JSON: "${json}"`,
			);
		}

		// Required
		const identifier = mapString(
			jsonObj.identifier,
			"Recipe 'identifier'",
		) as NonNullable<string>;
		const name = mapString(
			jsonObj.name,
			"Recipe 'name'",
		) as NonNullable<string>;

		// Optional
		const recipeCategory = mapString(
			jsonObj.recipeCategory,
			"Recipe 'recipeCategory'",
			true,
		);
		const description = mapString(
			jsonObj.description,
			"Recipe 'description'",
			true,
		);
		const dateCreated = mapString(
			jsonObj.dateCreated,
			"Recipe 'dateCreated'",
			true,
		);
		const dateModified = mapString(
			jsonObj.dateModified,
			"Recipe 'dateModified'",
			true,
		);
		const image = mapStringOrStringArray(
			jsonObj.image,
			"Recipe 'image'",
			true,
		);
		const imageUrl = mapStringOrStringArray(
			jsonObj.imageUrl,
			"Recipe 'imageUrl'",
			true,
		);
		const keywords = mapStringOrStringArray(
			jsonObj.keywords,
			"Recipe 'keywords'",
			true,
		);
		const cookTime = mapString(jsonObj.cookTime, "Recipe 'cookTime'", true);
		const prepTime = mapString(jsonObj.prepTime, "Recipe 'prepTime'", true);
		const totalTime = mapString(
			jsonObj.totalTime,
			"Recipe 'totalTime'",
			true,
		);
		const nutrition = jsonObj.nutrition
			? NutritionInformation.fromJSON(jsonObj.nutrition)
			: undefined;
		const recipeIngredient = mapStringOrStringArray(
			jsonObj.recipeIngredient,
			"Recipe 'recipeIngredient'",
			true,
		);
		const recipeYield = mapInteger(
			jsonObj.recipeYield,
			"Recipe 'recipeYield'",
			true,
		);
		const supply = jsonObj.supply
			? asArray(jsonObj.supply).map((s) => HowToSupply.fromJSON(s))
			: [];
		const recipeInstructions = jsonObj.recipeInstructions
			? asArray(jsonObj.recipeInstructions).map((i) => {
					if (i['@type'] === 'HowToSection') {
						return HowToSection.fromJSON(i);
					} else {
						return HowToDirection.fromJSON(i);
					}
				})
			: [];
		const tool = jsonObj.tool
			? asArray(jsonObj.tool).map((t) => HowToTool.fromJSON(t))
			: [];
		const url = mapStringOrStringArray(jsonObj.url, "Recipe 'url'", true);

		// Create and return the Recipe instance
		return new Recipe(identifier, name, {
			recipeCategory: recipeCategory || undefined,
			description: description || undefined,
			dateCreated: dateCreated || undefined,
			dateModified: dateModified || undefined,
			image: image || undefined,
			imageUrl: imageUrl || undefined,
			keywords: keywords || undefined,
			cookTime: cookTime || undefined,
			prepTime: prepTime || undefined,
			totalTime: totalTime || undefined,
			nutrition,
			recipeIngredient: recipeIngredient || [],
			recipeYield: recipeYield || undefined,
			supply,
			recipeInstructions,
			tool,
			url: url || [],
		});
	}
}
