import {
	mapInteger,
	mapString,
	mapStringOrStringArray,
} from 'cookbook/js/utils/jsonMapper';
import JsonMappingException from 'cookbook/js/Exceptions/JsonMappingException';
import { asCleanedArray } from 'cookbook/js/helper';
import BaseSchemaOrgModel from './BaseSchemaOrgModel';
import HowToDirection from './HowToDirection';
import HowToStep from './HowToStep';
import HowToTip from './HowToTip';

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

	/** The time required for the direction. */
	timeRequired?: string;

	/** The thumbnail URLs for the images defined in `image`. */
	thumbnailUrl?: string | string[];

	/** The list of directions within the section. */
	itemListElement?:
		| HowToDirection
		| HowToStep
		| HowToTip
		| (HowToDirection | HowToStep | HowToTip)[];
}

/**
 * Represents a section in the recipe instructions.
 * @class
 */
export default class HowToSection extends BaseSchemaOrgModel {
	/** @inheritDoc */
	// eslint-disable-next-line class-methods-use-this
	public readonly '@type' = 'HowToSection';

	/** The name of the section. */
	public name: string;

	/** The position of the section in the sequence. */
	public position?: number;

	/** The description of the section. */
	public description?: string;

	/** The images associated with the section. */
	public image: string[] = [];

	/** The time required for the section. */
	public timeRequired?: string;

	/** The thumbnail URLs for the images defined in `image`. */
	public thumbnailUrl: string[] = [];

	/** The list of directions within the section. */
	public itemListElement: (HowToDirection | HowToStep | HowToTip)[] = [];

	/**
	 * Creates a HowToSection instance.
	 * @constructor
	 * @param {string} name - The name of the section.
	 * @param {HowToSectionOptions} options - An options object containing additional properties.
	 */
	public constructor(name: string, options?: HowToSectionOptions) {
		super();
		this.name = name;
		if (options) {
			this.description = options.description;
			this.position = options.position;
			this.image = asCleanedArray(options.image);
			this.timeRequired = options.timeRequired;
			this.thumbnailUrl = asCleanedArray(options.thumbnailUrl);
			this.itemListElement = asCleanedArray(options.itemListElement);
		}
	}

	/**
	 * Create a `HowToSection` instance from a JSON string or object.
	 * @param {string | object} json - The JSON string or object.
	 * @returns {HowToSection} - The created HowToSection instance.
	 * @throws {Error} If the input JSON is invalid or missing required properties.
	 */
	static fromJSON(json: string | object): HowToSection {
		// eslint-disable-next-line @typescript-eslint/no-explicit-any
		let jsonObj: any;
		try {
			jsonObj = typeof json === 'string' ? JSON.parse(json) : json;
		} catch {
			throw new JsonMappingException(
				`Error mapping to "HowToSection". Received invalid JSON: "${json}"`,
			);
		}

		const name = mapString(jsonObj.name, "HowToSection 'name'", true) ?? '';

		const description = mapString(
			jsonObj.description,
			"HowToSection 'description'",
			true,
		);

		const position = mapInteger(
			jsonObj.position,
			"HowToSection 'position'",
			true,
		);

		const image = mapStringOrStringArray(
			jsonObj.image,
			"HowToSection 'image'",
			true,
		);

		const timeRequired = mapString(
			jsonObj.timeRequired,
			"HowToSection 'timeRequired'",
			true,
		);

		const thumbnailUrl = mapStringOrStringArray(
			jsonObj.thumbnailUrl,
			"HowToSection 'thumbnailUrl'",
			true,
		);

		// itemListElement
		let itemListElement: (HowToDirection | HowToStep | HowToTip)[] = [];
		if (jsonObj.itemListElement) {
			itemListElement = asCleanedArray(
				jsonObj.itemListElement.map((item: string | object) => {
					if (typeof item === 'string') {
						return new HowToDirection(item);
					}
					if (item['@type'] === 'HowToStep') {
						try {
							return HowToStep.fromJSON(item);
						} catch {
							/* empty */
						}
					}
					if (item['@type'] === 'HowToDirection') {
						try {
							return HowToDirection.fromJSON(item);
						} catch {
							/* empty */
						}
					}
					if (item['@type'] === 'HowToTip') {
						try {
							return HowToTip.fromJSON(item);
						} catch {
							/* empty */
						}
					}
					// Type not set, in a final try, try to map to direction
					try {
						return HowToDirection.fromJSON(item);
					} catch {
						/* empty */
					}
					return null;
				}),
			);
		}

		return new HowToSection(name, {
			description: description || undefined,
			position: position || undefined,
			image: image || [],
			timeRequired: timeRequired || undefined,
			thumbnailUrl: thumbnailUrl || [],
			itemListElement: itemListElement || [],
		});
	}
}
