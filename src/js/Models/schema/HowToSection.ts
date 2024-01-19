import HowToDirection from './HowToDirection';
import { asArray, asCleanedArray } from '../../helper';

/**
 * Interface representing the options for constructing a HowToSection instance.
 * @interface
 */
interface HowToSectionOptions {
	/** The description of the section. */
	description?: string;

	/** The position of the section in the sequence. */
	position?: number;

	/** The images associated with the section. */
	image?: string | string[];

	/** The thumbnail URLs for the images defined in `image`. */
	thumbnailUrl?: string | string[];

	/** The list of directions within the section. */
	itemListElement?: HowToDirection | HowToDirection[];
}

/**
 * Represents a section in the recipe instructions.
 * @class
 */
export default class HowToSection {
	/** The name of the section. */
	public name: string;

	/** The position of the section in the sequence. */
	public position?: number;

	/** The description of the section. */
	public description?: string;

	/** The images associated with the section. */
	public image: string[];

	/** The thumbnail URLs for the images defined in `image`. */
	public thumbnailUrl: string[];

	/** The list of directions within the section. */
	public itemListElement: HowToDirection[];

	/**
	 * Creates a HowToSection instance.
	 * @constructor
	 * @param {string} name - The name of the section.
	 * @param {HowToSectionOptions} options - An options object containing additional properties.
	 */
	public constructor(name: string, options: HowToSectionOptions = {}) {
		this['@type'] = 'HowToSection';
		this.name = name;
		this.description = options.description;
		this.position = options.position;
		this.image = asCleanedArray(options.image);
		this.thumbnailUrl = asCleanedArray(options.thumbnailUrl);
		this.itemListElement = asCleanedArray(options.itemListElement);
	}
}
