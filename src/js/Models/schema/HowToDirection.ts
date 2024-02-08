import {
	mapInteger,
	mapString,
	mapStringOrStringArray,
} from 'cookbook/js/utils/jsonMapper';
import JsonMappingException from 'cookbook/js/Exceptions/JsonMappingException';
import BaseSchemaOrgModel from './BaseSchemaOrgModel';
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
export default class HowToDirection extends BaseSchemaOrgModel {
	/** @inheritDoc */
	// eslint-disable-next-line class-methods-use-this
	public readonly '@type' = 'HowToDirection';

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
	public constructor(text: string, options?: HowToDirectionOptions) {
		super();
		this.text = text;
		if (options) {
			this.position = options.position;
			this.image = asCleanedArray(options.image);
			this.thumbnailUrl = asCleanedArray(options.thumbnailUrl);
			this.timeRequired = options.timeRequired;
			this.supply = asCleanedArray(options.supply);
			this.tool = asCleanedArray(options.tool);
		} else {
			this.image = [];
			this.thumbnailUrl = [];
			this.supply = [];
			this.tool = [];
		}
	}

	/**
	 * Create a `HowToDirection` instance from a JSON string or object.
	 * @param {string | object} json - The JSON string or object.
	 * @returns {HowToDirection} - The created HowToDirection instance.
	 * @throws {Error} If the input JSON is invalid or missing required properties.
	 */
	static fromJSON(json: string | object): HowToDirection {
		// eslint-disable-next-line @typescript-eslint/no-explicit-any
		let jsonObj: any;
		try {
			jsonObj = typeof json === 'string' ? JSON.parse(json) : json;
		} catch {
			throw new JsonMappingException(
				`Error mapping to "HowToDirection". Received invalid JSON: "${json}"`,
			);
		}

		const text = mapString(
			jsonObj.text,
			"HowToDirection 'text'",
		) as NonNullable<string>;

		const position = mapInteger(
			jsonObj.position,
			"HowToDirection 'position'",
			true,
		);

		const image = mapStringOrStringArray(
			jsonObj.image,
			"HowToDirection 'image'",
			true,
		);

		const thumbnailUrl = mapStringOrStringArray(
			jsonObj.thumbnailUrl,
			"HowToDirection 'thumbnailUrl'",
			true,
		);

		const timeRequired = mapString(
			jsonObj.timeRequired,
			"HowToDirection 'timeRequired'",
			true,
		);

		// supply
		let supply: HowToSupply | HowToSupply[] = [];
		if (jsonObj.supply) {
			if (Array.isArray(jsonObj.supply)) {
				supply = jsonObj.supply.map((s) =>
					HowToSupply.fromJSONOrString(s),
				);
			} else {
				supply = HowToSupply.fromJSONOrString(jsonObj.supply);
			}
		}

		// tool
		let tool: HowToTool | HowToTool[] = [];
		if (jsonObj.tool) {
			if (Array.isArray(jsonObj.tool)) {
				tool = jsonObj.tool.map((t) => HowToTool.fromJSONOrString(t));
			} else {
				tool = HowToTool.fromJSONOrString(jsonObj.tool);
			}
		}

		return new HowToDirection(text, {
			position: position || undefined,
			image: image || [],
			thumbnailUrl: thumbnailUrl || [],
			timeRequired: timeRequired || undefined,
			supply,
			tool,
		});
	}
}
