import QuantitativeValue from './QuantitativeValue';

/**
 * Represents a tool used in the recipe instructions.
 * @class
 */
export default class HowToTool {
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
		this['@type'] = 'HowToTool';
		this.name = name;
		// eslint-disable-next-line prefer-destructuring
		if (args[0]) this.identifier = args[0];
		// eslint-disable-next-line prefer-destructuring
		if (args[1]) this.description = args[1];
		// eslint-disable-next-line prefer-destructuring
		if (args[2]) this.requiredQuantity = args[2];
	}
}
