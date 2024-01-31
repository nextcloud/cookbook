import JsonMappingException from '../../Exceptions/JsonMappingException';

/**
 * Interface representing the properties of the NutritionInformation class.
 * @interface
 */
export interface NutritionInformationProperties {
	/** The number of calories. */
	calories?: string;

	/** The number of grams of carbohydrates. */
	carbohydrateContent?: string;

	/** The number of milligrams of cholesterol. */
	cholesterolContent?: string;

	/** The number of grams of fat. */
	fatContent?: string;

	/** The number of grams of fiber. */
	fiberContent?: string;

	/** The number of grams of protein. */
	proteinContent?: string;

	/** The number of grams of saturated fat. */
	saturatedFatContent?: string;

	/** The serving size, in terms of the number of volume or mass. */
	servingSize?: string;

	/** The number of milligrams of sodium. */
	sodiumContent?: string;

	/** The number of grams of sugar. */
	sugarContent?: string;

	/** The number of grams of trans fat. */
	transFatContent?: string;

	/** The number of grams of unsaturated fat. */
	unsaturatedFatContent?: string;
}

/**
 * Represents nutrition information.
 * @class
 */
export default class NutritionInformation {
	/** The number of calories. */
	public calories?: string;

	/** The number of grams of carbohydrates. */
	public carbohydrateContent?: string;

	/** The number of milligrams of cholesterol. */
	public cholesterolContent?: string;

	/** The number of grams of fat. */
	public fatContent?: string;

	/** The number of grams of fiber. */
	public fiberContent?: string;

	/** The number of grams of protein. */
	public proteinContent?: string;

	/** The number of grams of saturated fat. */
	public saturatedFatContent?: string;

	/** The serving size, in terms of the number of volume or mass. */
	public servingSize?: string;

	/** The number of milligrams of sodium. */
	public sodiumContent?: string;

	/** The number of grams of sugar. */
	public sugarContent?: string;

	/** The number of grams of trans fat. */
	public transFatContent?: string;

	/** The number of grams of unsaturated fat. */
	public unsaturatedFatContent?: string;

	/**
	 * Creates a NutritionInformation instance.
	 * @constructor
	 * @param properties - An optional object containing the nutrition information properties.
	 */
	constructor(properties?: NutritionInformationProperties) {
		this['@type'] = 'NutritionInformation';

		// Set the properties from the provided object, or default to undefined
		Object.assign(this, properties);
	}

	/**
	 * Checks if any nutrition value in this object is a non-empty string.
	 * @returns {boolean} - `true` if there is a nutrition value defined. `false` otherwise.
	 */
	public isUndefined(): boolean {
		return !(
			// Does any of these have a value?
			(
				(this.calories && this.calories !== '') ||
				(this.carbohydrateContent && this.carbohydrateContent !== '') ||
				(this.cholesterolContent && this.cholesterolContent !== '') ||
				(this.fatContent && this.fatContent !== '') ||
				(this.fiberContent && this.fiberContent !== '') ||
				(this.proteinContent && this.proteinContent !== '') ||
				(this.saturatedFatContent && this.saturatedFatContent !== '') ||
				(this.servingSize && this.servingSize !== '') ||
				(this.sodiumContent && this.sodiumContent !== '') ||
				(this.sugarContent && this.sugarContent !== '') ||
				(this.transFatContent && this.transFatContent !== '') ||
				(this.unsaturatedFatContent &&
					this.unsaturatedFatContent !== '')
			)
		);
	}

	/**
	 * Create a `NutritionInformation` instance from a JSON string.
	 * @param {string | object} json - The JSON string or object.
	 * @returns {NutritionInformation} - The created NutritionInformation instance.
	 * @throws {Error} If the input JSON is invalid or missing required properties.
	 */
	static fromJSON(json: string | object): NutritionInformation {
		// eslint-disable-next-line @typescript-eslint/no-explicit-any
		let jsonObj: any;
		try {
			jsonObj = typeof json === 'string' ? JSON.parse(json) : json;
		} catch {
			throw new JsonMappingException(
				`Error mapping to "NutritionInformation". Received invalid JSON: "${json}"`,
			);
		}

		const validateStringProperty = (propertyName: string) => {
			if (
				jsonObj[propertyName] !== undefined &&
				jsonObj[propertyName] !== null &&
				typeof jsonObj[propertyName] !== 'string'
			) {
				throw new JsonMappingException(
					`Invalid property value: "${propertyName}" must be a string`,
				);
			}
		};

		validateStringProperty('calories');
		validateStringProperty('carbohydrateContent');
		validateStringProperty('cholesterolContent');
		validateStringProperty('fatContent');
		validateStringProperty('fiberContent');
		validateStringProperty('proteinContent');
		validateStringProperty('saturatedFatContent');
		validateStringProperty('servingSize');
		validateStringProperty('sodiumContent');
		validateStringProperty('sugarContent');
		validateStringProperty('transFatContent');
		validateStringProperty('unsaturatedFatContent');

		return new NutritionInformation({
			calories: jsonObj.calories,
			carbohydrateContent: jsonObj.carbohydrateContent,
			cholesterolContent: jsonObj.cholesterolContent,
			fatContent: jsonObj.fatContent,
			fiberContent: jsonObj.fiberContent,
			proteinContent: jsonObj.proteinContent,
			saturatedFatContent: jsonObj.saturatedFatContent,
			servingSize: jsonObj.servingSize,
			sodiumContent: jsonObj.sodiumContent,
			sugarContent: jsonObj.sugarContent,
			transFatContent: jsonObj.transFatContent,
			unsaturatedFatContent: jsonObj.unsaturatedFatContent,
		});
	}
}
