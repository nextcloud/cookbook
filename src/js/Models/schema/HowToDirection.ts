import HowToSupply from './HowToSupply';
import HowToTool from './HowToTool';
import { asCleanedArray } from '../../helper';

/**
 * Interface representing the options for constructing a HowToDirection instance.
 * @interface
 */
interface HowToDirectionOptions {
	/** The position of the direction in the sequence. */
	position?: number;

	/** The images associated with the direction. */
	image?: string | string[];

	/** The thumbnail URLs for the images. */
	thumbnailUrl?: string | string[];

	/** The time required for the direction. */
	timeRequired?: string;

	/** The list of supplies needed for the direction. */
	supply?: HowToSupply | HowToSupply[];

	/** The list of tools needed for the direction. */
	tool?: HowToTool | HowToTool[];
}

/**
 * Represents a step or direction in the recipe instructions.
 * @class
 */
export default class HowToDirection {
	/** The text content of the direction. */
	public text: string;

	/** The position of the direction in the sequence. */
	public position?: number;

	/** The images associated with the direction. */
	public image: string[];

	/** The thumbnail URLs for the images. */
	public thumbnailUrl: string[];

	/** The time required for the direction. */
	public timeRequired?: string;

	/** The list of supplies needed for the direction. */
	public supply: HowToSupply[];

	/** The list of tools needed for the direction. */
	public tool: HowToTool[];

	/**
	 * Creates a `HowToDirection` instance.
	 * @constructor
	 * @param {string} text - The text content of the direction.
	 * @param {HowToDirectionOptions} options - An options object containing additional properties.
	 */
	public constructor(text: string, options: HowToDirectionOptions = {}) {
		this['@type'] = 'HowToDirection';
		this.text = text;
		this.position = options.position;
		this.image = asCleanedArray(options.image);
		this.thumbnailUrl = asCleanedArray(options.thumbnailUrl);
		this.timeRequired = options.timeRequired;
		this.supply = asCleanedArray(options.supply);
		this.tool = asCleanedArray(options.tool);
	}
}
