/**
 * Represents a quantitative value with unit information.
 * @class
 */
export default class QuantitativeValue {
	/** The numerical value. */
	public value: number;

	/** The unit of measurement (e.g., "cup", "kilogram"). */
	public unitText: string;

	/** The unit code (e.g., "CU", "KGM"). */
	public unitCode?: string;

	/**
	 * Creates a QuantitativeValue instance.
	 * @constructor
	 * @param value - The numerical value.
	 * @param unitText - The unit of measurement (e.g., "cup", "kilogram").
	 */
	constructor(value: number, unitText: string);

	/**
	 * Creates a QuantitativeValue instance.
	 * @constructor
	 * @param value - The numerical value.
	 * @param unitText - The unit of measurement (e.g., "cup", "kilogram").
	 * @param unitCode - The unit code (e.g., "CU", "KGM").
	 */
	constructor(value: number, unitText: string, unitCode?: string);

	/**
	 * Creates a QuantitativeValue instance.
	 * @constructor
	 */
	constructor(value: number, unitText: string, ...args: never[]) {
		this['@type'] = 'QuantitativeValue';
		this.value = value;
		this.unitText = unitText;
		// eslint-disable-next-line prefer-destructuring
		if (args[0]) this.unitCode = args[0];
	}
}
