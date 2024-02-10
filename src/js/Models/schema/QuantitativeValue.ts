import JsonMappingException from 'cookbook/js/Exceptions/JsonMappingException';
import { mapInteger, mapString } from 'cookbook/js/utils/jsonMapper';
import BaseSchemaOrgModel from 'cookbook/js/Models/schema/BaseSchemaOrgModel';

/**
 * Interface representing the options for constructing a QuantitativeValue instance.
 * @interface
 */
interface QuantitativeValueOptions {
	/** The unit of measurement (e.g., "cup", "kilogram"). */
	unitText?: string;

	/** The unit code (e.g., "CU", "KGM"). */
	unitCode?: string;
}

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
	public unitText?: string;

	/** The unit code (e.g., "CU", "KGM"). */
	public unitCode?: string;

	/**
	 * Creates a `QuantitativeValue` instance.
	 * @constructor
	 * @param {number} value - The numeric value (amount).
	 * @param {QuantitativeValueOptions} options - An options object containing additional properties regarding the unit.
	 */
	public constructor(value: number, options?: QuantitativeValueOptions) {
		super();
		this.value = value;
		if (options) {
			this.unitText = options.unitText;
			this.unitCode = options.unitCode;
		} else {
			this.unitText = undefined;
			this.unitCode = undefined;
		}
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

		return new QuantitativeValue(value, {
			unitText,
			unitCode: unitCode || undefined,
		});
	}

	/**
	 * Create a `QuantitativeValue` instance from a JSON string.
	 * @param {string | object} json - The JSON string or object.
	 * @returns {QuantitativeValue} - The created QuantitativeValue instance.
	 * @throws {Error} If the input JSON is invalid or missing required properties.
	 */
	static fromJSONOrString(json: string | object): QuantitativeValue {
		try {
			return QuantitativeValue.fromJSON(json);
		} catch {
			const number = parseFloat(json as string);
			if (!Number.isNaN(number)) {
				return new QuantitativeValue(number);
			}
		}
		throw new JsonMappingException(
			`Error mapping to "QuantitativeValue". Received invalid JSON: "${json}"`,
		);
	}
}
