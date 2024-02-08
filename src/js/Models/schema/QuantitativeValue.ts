import JsonMappingException from 'cookbook/js/Exceptions/JsonMappingException';
import { mapInteger, mapString } from 'cookbook/js/utils/jsonMapper';
import BaseSchemaOrgModel from 'cookbook/js/Models/schema/BaseSchemaOrgModel';

/**
 * Represents a quantitative value with unit information.
 * @class
 */
export default class QuantitativeValue extends BaseSchemaOrgModel {
	/** @inheritDoc */
	// eslint-disable-next-line class-methods-use-this
	public readonly '@type' = 'QuantitativeValue';

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
		super();
		this.value = value;
		this.unitText = unitText;
		// eslint-disable-next-line prefer-destructuring
		if (args[0]) this.unitCode = args[0];
	}

	/**
	 * Create a `QuantitativeValue` instance from a JSON string.
	 * @param {string | object} json - The JSON string or object.
	 * @returns {QuantitativeValue} - The created QuantitativeValue instance.
	 * @throws {Error} If the input JSON is invalid or missing required properties.
	 */
	static fromJSON(json: string | object): QuantitativeValue {
		// eslint-disable-next-line @typescript-eslint/no-explicit-any
		let jsonObj: any;
		try {
			jsonObj = typeof json === 'string' ? JSON.parse(json) : json;
		} catch {
			throw new JsonMappingException(
				`Error mapping to "QuantitativeValue". Received invalid JSON: "${json}"`,
			);
		}

		const value = mapInteger(
			jsonObj.value,
			"QuantitativeValue 'value'",
		) as NonNullable<number>;

		const unitText = mapString(
			jsonObj.unitText,
			"QuantitativeValue 'value'",
		) as NonNullable<string>;

		const unitCode = mapString(
			jsonObj.unitCode,
			"QuantitativeValue 'value'",
			true,
		);

		return new QuantitativeValue(value, unitText, unitCode || undefined);
	}
}
