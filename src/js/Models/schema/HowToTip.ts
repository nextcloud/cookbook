import {
	mapInteger,
	mapString,
	mapStringOrStringArray,
} from 'cookbook/js/utils/jsonMapper';
import JsonMappingException from 'cookbook/js/Exceptions/JsonMappingException';
import { asCleanedArray } from '../../helper';

/**
 * Interface representing the options for constructing a `HowToTip` instance.
 * @interface
 */
interface HowToTipOptions {
	/** The position of the tip in the sequence. */
	position?: number;

	/** The images associated with the tip. */
	image?: string | string[];

	/** The thumbnail URLs for the images. */
	thumbnailUrl?: string | string[];

	/** The time required for the tip. */
	timeRequired?: string;
}

/**
 * Represents a tip in the recipe instructions.
 * @class
 */
export default class HowToTip {
	/** The text content of the tip. */
	public text: string;

	/** The position of the tip in the sequence. */
	public position?: number;

	/** The images associated with the tip. */
	public image: string[];

	/** The thumbnail URLs for the images. */
	public thumbnailUrl: string[];

	/** The time required for the tip. */
	public timeRequired?: string;

	/**
	 * Creates a `HowToTip` instance.
	 * @constructor
	 * @param {string} text - The text content of the tip.
	 * @param {HowToTipOptions} options - An options object containing additional properties.
	 */
	public constructor(text: string, options?: HowToTipOptions) {
		this['@type'] = 'HowToTip';
		this.text = text;
		if (options) {
			this.position = options.position;
			this.image = asCleanedArray(options.image);
			this.thumbnailUrl = asCleanedArray(options.thumbnailUrl);
			this.timeRequired = options.timeRequired;
		}
	}

	/**
	 * Create a `HowToTip` instance from a JSON string or object.
	 * @param {string | object} json - The JSON string or object.
	 * @returns {HowToTip} - The created HowToTip instance.
	 * @throws {Error} If the input JSON is invalid or missing required properties.
	 */
	static fromJSON(json: string | object): HowToTip {
		// eslint-disable-next-line @typescript-eslint/no-explicit-any
		let jsonObj: any;
		try {
			jsonObj = typeof json === 'string' ? JSON.parse(json) : json;
		} catch {
			throw new JsonMappingException(
				`Error mapping to "HowToTip". Received invalid JSON: "${json}"`,
			);
		}

		const text = mapString(
			jsonObj.text,
			"HowToTip 'text'",
		) as NonNullable<string>;

		const position = mapInteger(
			jsonObj.position,
			"HowToTip 'position'",
			true,
		);

		const image = mapStringOrStringArray(
			jsonObj.image,
			"HowToTip 'image'",
			true,
		);

		const thumbnailUrl = mapStringOrStringArray(
			jsonObj.thumbnailUrl,
			"HowToTip 'thumbnailUrl'",
			true,
		);

		const timeRequired = mapString(
			jsonObj.timeRequired,
			"HowToTip 'timeRequired'",
			true,
		);

		return new HowToTip(text, {
			position: position || undefined,
			image: image || [],
			thumbnailUrl: thumbnailUrl || [],
			timeRequired: timeRequired || undefined,
		});
	}
}
