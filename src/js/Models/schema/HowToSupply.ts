import { mapString } from 'cookbook/js/utils/jsonMapper';
import JsonMappingException from 'cookbook/js/Exceptions/JsonMappingException';
import BaseSchemaOrgModel from './BaseSchemaOrgModel';
import QuantitativeValue from './QuantitativeValue';

/**
 * Represents a supply item in the `HowToSupply` section.
 * @class
 */
export default class HowToSupply extends BaseSchemaOrgModel {
	/** @inheritDoc */
	// eslint-disable-next-line class-methods-use-this
	public readonly '@type' = 'HowToSupply';

	/** The name of the supply item. */
	public name: string;

	/** The identifier of the supply item. */
	public identifier?: string;

	/** The description of the supply item. */
	public description?: string;

	/** The required quantity of the supply item. */
	public requiredQuantity?: QuantitativeValue;

	/**
	 * Creates a `HowToSupply` instance.
	 * @constructor
	 * @param name - The name of the supply item.
	 */
	public constructor(name: string);

	/**
	 * Creates a `HowToSupply` instance.
	 * @constructor
	 * @param name - The name of the supply item.
	 * @param identifier - The identifier of the supply item.
	 * @param description - The description of the supply item.
	 * @param requiredQuantity - The required quantity of the supply item.
	 */
	public constructor(
		name: string,
		identifier?: string,
		description?: string,
		requiredQuantity?: QuantitativeValue,
	);

	/**
	 * Creates a `HowToSupply` instance.
	 * @constructor
	 * @param name - The name of the supply item.
	 * @param args - Remaining supported arguments.
	 */
	constructor(name: string, ...args: never[]) {
		super();
		this.name = name;
		// eslint-disable-next-line prefer-destructuring
		if (args[0]) this.identifier = args[0];
		// eslint-disable-next-line prefer-destructuring
		if (args[1]) this.description = args[1];
		// eslint-disable-next-line prefer-destructuring
		if (args[2]) this.requiredQuantity = args[2];
	}

	/**
	 * Create a `HowToSupply` instance from a JSON string.
	 * @param {string | object} json - The JSON string or object.
	 * @returns {HowToSupply} - The created HowToSupply instance.
	 * @throws {Error} If the input JSON is invalid or missing required properties.
	 */
	static fromJSON(json: string | object): HowToSupply {
		// eslint-disable-next-line @typescript-eslint/no-explicit-any
		let jsonObj: any;
		try {
			jsonObj = typeof json === 'string' ? JSON.parse(json) : json;
		} catch {
			throw new JsonMappingException(
				`Error mapping to "HowToSupply". Received invalid JSON: "${json}"`,
			);
		}

		const name = mapString(
			jsonObj.name,
			"HowToSupply 'name'",
		) as NonNullable<string>;

		const identifier = mapString(
			jsonObj.identifier,
			"HowToSupply 'identifier'",
			true,
		);

		const description = mapString(
			jsonObj.description,
			"HowToSupply 'description'",
			true,
		);

		const requiredQuantity = jsonObj.requiredQuantity
			? QuantitativeValue.fromJSON(jsonObj.requiredQuantity)
			: undefined;

		return new HowToSupply(
			name,
			identifier || undefined,
			description || undefined,
			requiredQuantity,
		);
	}

	/**
	 * Create a `HowToSupply` instance from a JSON string. If the string can't be parsed as a JSON
	 * object it will be used as the tool's name.
	 * @param {string | object} json - The JSON string or object.
	 * @returns {HowToSupply} - The created HowToSupply instance.
	 * @throws {Error} If the input JSON is invalid or missing required properties.
	 */
	static fromJSONOrString(json: string | object): HowToSupply {
		// eslint-disable-next-line @typescript-eslint/no-explicit-any
		let jsonObj: any;
		try {
			jsonObj = typeof json === 'string' ? JSON.parse(json) : json;
		} catch {
			if (typeof json === 'string') return new HowToSupply(json);
		}
		return this.fromJSON(jsonObj);
	}
}
