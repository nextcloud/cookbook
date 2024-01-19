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
}
