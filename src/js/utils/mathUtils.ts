/**
 * Adjusts a numeric value by a specified step size.
 * If the original value is a float, it will be rounded to the nearest integer.
 * The adjusted value will always be greater than 0.
 *
 * @param {number} value - The original numeric value.
 * @param {number} step - The step size by which to adjust the value.
 * @returns {number} - The adjusted value.
 */
export function adjustToInteger(value: number, step: number): number {
	// Add the step
	const modifiedValue = value + step;

	// Round the value to the nearest integer
	let adjustedValue =
		step > 0 ? Math.floor(modifiedValue) : Math.ceil(modifiedValue);

	// Ensure the adjusted value is at least 1
	adjustedValue = Math.max(adjustedValue, 1);

	// If the original value is between 0 and 1 and the step is negative, adjust accordingly
	if (value > 0 && value < 1 && step < 0) {
		adjustedValue = Math.min(value, adjustedValue);
	}

	return adjustedValue;
}

/**
 * Clamps val between the minimum min and maximum max value.
 * @param val The value to be clamped between min and max
 * @param min The upper limit
 * @param max The lower limit
 * @returns {number} min if val is <= min, max if val is >= max and val if min <= val <= max.
 */
export function clamp(val: number, min: number, max: number): number {
	return Math.min(max, Math.max(min, val));
}

/**
 * Rounds a number to a given precision.
 * @param {number} num - The number to round.
 * @param {int} precision - The number of decimals.
 */
export function roundTo(num: number, precision: number) {
	const factor = 10 ** precision;
	return Math.round(num * factor) / factor;
}
