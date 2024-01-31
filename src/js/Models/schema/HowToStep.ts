import {
	mapInteger,
	mapString,
	mapStringOrStringArray,
} from 'cookbook/js/utils/jsonMapper';
import JsonMappingException from 'cookbook/js/Exceptions/JsonMappingException';
import HowToDirection from 'cookbook/js/Models/schema/HowToDirection';
import HowToTip from 'cookbook/js/Models/schema/HowToTip';
import { asArray, asCleanedArray } from '../../helper';

/**
 * Interface representing the options for constructing a `HowToStep` instance.
 * @interface
 */
interface HowToStepOptions {
	/** The position of the step in the sequence. */
	position?: number;

	/** The images associated with the step. */
	image?: string | string[];

	/** The thumbnail URLs for the images. */
	thumbnailUrl?: string | string[];

	/** The time required for the step. */
	timeRequired?: string;
}

/**
 * Represents a step in the recipe instructions.
 * @class
 */
export default class HowToStep {
	/** The text content of the step. Required if `itemListElement` is not set. */
	public _text?: string;

	/** The position of the step in the sequence. */
	public position?: number;

	/** The images associated with the step. */
	public image: string[];

	/** A list of substeps. This may include directions or tips. Required if `text` is not set. */
	public _itemListElement: (HowToDirection | HowToTip)[];

	/** The thumbnail URLs for the images. */
	public thumbnailUrl: string[];

	/** The time required for the step. */
	public timeRequired?: string;

	private validationMsg =
		'HowToStep requires either `text` or `itemListElement` to be set';

	/**
	 * Creates a `HowToStep` instance.
	 * @constructor
	 * @param {string} text - The text content of the step.
	 * @param {HowToStepOptions} options - An options object containing additional properties.
	 */
	public constructor(
		text: string,
		itemListElements: (HowToDirection | HowToTip)[],
		options?: HowToStepOptions,
	) {
		if (!text && !itemListElements) {
			throw Error(this.validationMsg);
		}

		this['@type'] = 'HowToStep';
		this._text = text;
		if (options) {
			this._itemListElement = itemListElements;
			this.position = options.position;
			this.image = asCleanedArray(options.image);
			this.thumbnailUrl = asCleanedArray(options.thumbnailUrl);
			this.timeRequired = options.timeRequired;
		}
	}

	/** A list of substeps. This may include directions or tips. Required if `text` is not set. */
	public get itemListElement(): (HowToDirection | HowToTip)[] {
		return this._itemListElement;
	}

	/** A list of substeps. This may include directions or tips. Required if `text` is not set. */
	public set itemListElement(
		value:
			| HowToDirection
			| HowToTip
			| (HowToDirection | HowToTip)[]
			| undefined,
	) {
		if (!this.text && !value) {
			throw Error(this.validationMsg);
		}
		this._itemListElement = value ? asArray(value) : [];
	}

	/** The text content of the step. Required if `itemListElement` is not set. */
	public get text(): string | undefined {
		return this._text;
	}

	/** The text content of the step. Required if `itemListElement` is not set. */
	public set text(value: string | undefined) {
		if (!this._itemListElement && !value) {
			throw Error(this.validationMsg);
		}
		this._text = value;
	}

	/**
	 * Create a `HowToStep` instance from a JSON string or object.
	 * @param {string | object} json - The JSON string or object.
	 * @returns {HowToStep} - The created HowToStep instance.
	 * @throws {Error} If the input JSON is invalid or missing required properties.
	 */
	static fromJSON(json: string | object): HowToStep {
		// eslint-disable-next-line @typescript-eslint/no-explicit-any
		let jsonObj: any;
		try {
			jsonObj = typeof json === 'string' ? JSON.parse(json) : json;
		} catch {
			throw new JsonMappingException(
				`Error mapping to "HowToStep". Received invalid JSON: "${json}"`,
			);
		}

		const text = mapString(
			jsonObj.text,
			"HowToStep 'text'",
		) as NonNullable<string>;

		const itemListElements = this.mapDirectionOrTipArray(
			jsonObj.itemListElement,
		);

		const position = mapInteger(
			jsonObj.position,
			"HowToStep 'position'",
			true,
		);

		const image = mapStringOrStringArray(
			jsonObj.image,
			"HowToStep 'image'",
			true,
		);

		const thumbnailUrl = mapStringOrStringArray(
			jsonObj.thumbnailUrl,
			"HowToStep 'thumbnailUrl'",
			true,
		);

		const timeRequired = mapString(
			jsonObj.timeRequired,
			"HowToStep 'timeRequired'",
			true,
		);

		return new HowToStep(text, itemListElements, {
			position: position || undefined,
			image: image || [],
			thumbnailUrl: thumbnailUrl || [],
			timeRequired: timeRequired || undefined,
		});
	}

	/**
	 * Tries to map `json` to a string or an array of strings.
	 * @param json The value to be mapped.
	 * @returns The value as an array of `HowToDirection` and `HowToTip` items.
	 */
	private static mapDirectionOrTipArray(
		json: unknown,
	): (HowToDirection | HowToTip)[] {
		const jsonArray = json ? asArray(json) : [];
		const mappedArray = jsonArray.map((item) => {
			if (typeof item === 'string') {
				return new HowToDirection(item);
			}
			try {
				return HowToDirection.fromJSON(item);
			} catch {}
			try {
				return HowToTip.fromJSON(item);
			} catch {}
			return null;
		});

		return mappedArray.filter((itm) => !!itm).map((itm) => itm!);
	}
}
