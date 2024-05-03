/**
 * Rounds a number to a given precision.
 * @param {number} num - The number to round.
 * @param {int} precision - The number of decimals.
 */
// eslint-disable-next-line import/prefer-default-export
export function roundTo(num: number, precision: number) {
	const factor = 10 ** precision;
	return Math.round(num * factor) / factor;
}
