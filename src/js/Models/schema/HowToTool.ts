import { mapString } from 'cookbook/js/utils/jsonMapper';
import JsonMappingException from 'cookbook/js/Exceptions/JsonMappingException';
import BaseSchemaOrgModel from './BaseSchemaOrgModel';
import QuantitativeValue from './QuantitativeValue';

/**
 * Represents a tool used in the recipe instructions.
 * @class
 */
export default class HowToTool extends BaseSchemaOrgModel {
	/** @inheritDoc */
	// eslint-disable-next-line class-methods-use-this
	public readonly '@type' = 'HowToTool';

	/** The name of the tool. */
	public name: string;

	/** The identifier of the tool. */
	public identifier?: string;

	/** The description of the tool. */
	public description?: string;

	/** The required quantity of the tool. */
	public requiredQuantity?: QuantitativeValue;

	/**
	 * Creates a HowToTool instance.
	 * @constructor
	 * @param name - The name of the tool.
	 */
	constructor(name: string);

	/**
	 * Creates a HowToTool instance.
	 * @constructor
	 * @param name - The name of the tool.
	 * @param identifier - The identifier of the tool.
	 * @param description - The description of the tool.
	 * @param requiredQuantity - The required quantity of the tool.
	 */
	constructor(
		name: string,
		identifier?: string,
		description?: string,
		requiredQuantity?: QuantitativeValue,
	);

	/**
	 * Creates a HowToTool instance.
	 * @constructor
	 * @param name - The name of the tool.
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
	 * Create a `HowToTool` instance from a JSON string.
	 * @param {string | object} json - The JSON string or object.
	 * @returns {HowToTool} - The created HowToTool instance.
	 * @throws {Error} If the input JSON is invalid or missing required properties.
	 */
	static fromJSON(json: string | object): HowToTool {
		// eslint-disable-next-line @typescript-eslint/no-explicit-any
		let jsonObj: any;
		try {
			jsonObj = typeof json === 'string' ? JSON.parse(json) : json;
		} catch {
			throw new JsonMappingException(
				`Error mapping to "HowToTool". Received invalid JSON: "${json}"`,
			);
		}

		const name = mapString(
			jsonObj.name,
			"HowToTool 'name'",
		) as NonNullable<string>;

		const identifier = mapString(
			jsonObj.identifier,
			"HowToTool 'identifier'",
			true,
		);

		const description = mapString(
			jsonObj.description,
			"HowToTool 'description'",
			true,
		);

		const requiredQuantity = jsonObj.requiredQuantity
			? QuantitativeValue.fromJSON(jsonObj.requiredQuantity)
			: undefined;

		return new HowToTool(
			name,
			identifier || undefined,
			description || undefined,
			requiredQuantity,
		);
	}

	/**
	 * Create a `HowToTool` instance from a JSON string or a simple string. If the string can't be parsed as a JSON
	 * object it will be used as the tool's name.
	 * @param {string | object} json - The JSON string or object.
	 * @returns {HowToTool} - The created HowToTool instance.
	 * @throws {Error} If the input JSON is invalid or missing required properties.
	 */
	static fromJSONOrString(json: string | object): HowToTool {
		// eslint-disable-next-line @typescript-eslint/no-explicit-any
		let jsonObj: any;
		try {
			jsonObj = typeof json === 'string' ? JSON.parse(json) : json;
		} catch {
			if (typeof json === 'string') return new HowToTool(json);
		}
		return this.fromJSON(jsonObj);
	}
}
