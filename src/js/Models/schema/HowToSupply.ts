import QuantitativeValue from './QuantitativeValue';

/**
 * Represents a supply item in the `HowToSupply` section.
 * @class
 */
export default class HowToSupply {
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
		this['@type'] = 'HowToSupply';
		this.name = name;
		// eslint-disable-next-line prefer-destructuring
		if (args[0]) this.identifier = args[0];
		// eslint-disable-next-line prefer-destructuring
		if (args[1]) this.description = args[1];
		// eslint-disable-next-line prefer-destructuring
		if (args[2]) this.requiredQuantity = args[2];
	}
}
