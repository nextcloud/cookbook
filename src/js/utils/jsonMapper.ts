import JsonMappingException from 'cookbook/js/Exceptions/JsonMappingException';

/**
 * Tries to map `value` to an integer.
 * @param value The value to be mapped.
 * @param targetName The name of the target property. Only used for error message.
 * @param allowNullOrUndefined If true `null` or `undefined` will be immediately returned. If false, an exception will be thrown.
 * @throws JsonMappingException Thrown if `value` cannot be mapped to an integer number.
 * @returns Either the value as an integer if mapping was successful or null/undefined if the value was null/undefined
 * and allowNullOrUndefined is true.
 */
export function mapInteger(
	value: unknown,
	targetName: string = '',
	allowNullOrUndefined: boolean = false,
): number | null | undefined {
	if (value === undefined || value === null) {
		// Return null or undefined immediately
		if (allowNullOrUndefined) return value;
		// Throw
		throw new JsonMappingException(
			`Error mapping ${targetName}. Expected integer number but received "${value}".`,
		);
	}

	// Only numbers and strings can be mapped to an integer. Early return.
	if (typeof value !== 'number' && typeof value !== 'string') {
		throw new JsonMappingException(
			`Error mapping ${targetName}. Expected integer number but received "${typeof value}".`,
		);
	}

	// `value` is a number, but is it an integer?
	if (typeof value === 'number') {
		if (Number.isInteger(value)) {
			return value;
		}
		throw new JsonMappingException(
			`Error mapping ${targetName}. Expected integer number but received non-integer "${value}".`,
		);
	}

	// `value` is a string, can it be parsed to an integer?

	const parsedValue: number = parseInt(value, 10);
	if (Number.isNaN(parsedValue)) {
		throw new JsonMappingException(
			`Error mapping ${targetName}. Expected integer number but received non-parsable string "${value}".`,
		);
	}
	return parsedValue;
}

/**
 * Tries to map `value` to a string or an array of strings.
 * @param value The value to be mapped.
 * @param targetName The name of the target property. Only used for error message.
 * @param allowNullOrUndefined If true `null` or `undefined` will be immediately returned. If false, an exception will be thrown.
 * @param treatStringAsCommaSeparatedList If true and a single string is passed, the string is treated as a
 * 	comma-separated list and mapped to an array.
 * @throws JsonMappingException Thrown if `value` cannot be mapped to a string or an array of strings.
 * @returns Either the value as a string or an array of strings if mapping was successful or null/undefined if the
 * value was null/undefined.
 */
export function mapStringOrStringArray(
	value: unknown,
	targetName: string = '',
	allowNullOrUndefined: boolean = false,
	treatStringAsCommaSeparatedList = false,
): string | string[] | null | undefined {
	if (value === undefined || value === null) {
		// Return null or undefined immediately
		if (allowNullOrUndefined) return value;
		// Throw
		throw new JsonMappingException(
			`Error mapping ${targetName}. Expected string or string array but received "${value}".`,
		);
	}

	// Only strings and string arrays can be mapped. Early return.
	if (typeof value !== 'string' && !Array.isArray(value)) {
		throw new JsonMappingException(
			`Error mapping ${targetName}. Expected string or array but received "${typeof value}".`,
		);
	}

	// `value` is an array but is it an array of strings?
	if (Array.isArray(value)) {
		if (value.every((i) => typeof i === 'string')) return value;

		throw new JsonMappingException(
			`Error mapping ${targetName}. Expected string or string array received array with non-string elements.`,
		);
	}

	// `value` is a string, return.
	if (!treatStringAsCommaSeparatedList) return value;

	return (
		value
			.split(',')
			.map((str) => str.trim())
			// Remove any empty strings
			// If empty string, split will create an array of a single empty string
			.filter((str) => str !== '')
	);
}

/**
 * Tries to map `value` to a string.
 * @param value The value to be mapped.
 * @param targetName The name of the target property. Only used for error message.
 * @param allowNullOrUndefined If true `null` or `undefined` will be immediately returned. If false, an exception will be thrown.
 * @throws JsonMappingException Thrown if `value` cannot be mapped to a string.
 * @returns Either the value as a string if mapping was successful or null/undefined if the value was null/undefined.
 */
export function mapString(
	value: unknown,
	targetName: string = '',
	allowNullOrUndefined: boolean = false,
): string | null | undefined {
	if (value === undefined || value === null) {
		// Return null or undefined immediately
		if (allowNullOrUndefined) return value;
		// Throw
		throw new JsonMappingException(
			`Error mapping ${targetName}. Expected string but received "${value}".`,
		);
	}

	// Only strings can be mapped. Early return.
	if (typeof value !== 'string') {
		throw new JsonMappingException(
			`Error mapping ${targetName}. Expected string but received "${typeof value}".`,
		);
	}

	// `value` is a string, return.
	return value;
}
