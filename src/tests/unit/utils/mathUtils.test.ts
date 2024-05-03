// adjustToIntegerToInteger() tests
import { adjustToInteger, clamp, roundTo } from 'cookbook/js/utils/mathUtils';

describe('adjustToInteger function', () => {
	it('should increase the value by 1 when step is 1', () => {
		expect(adjustToInteger(1, 1)).toBe(2);
		expect(adjustToInteger(1.5, 1)).toBe(2);
	});

	it('should increase the value by 2 when step is 2', () => {
		expect(adjustToInteger(1, 2)).toBe(3);
		expect(adjustToInteger(1.5, 2)).toBe(3);
	});

	it('should decrease the value by 1 when step is -1', () => {
		expect(adjustToInteger(2, -1)).toBe(1);
		expect(adjustToInteger(2.5, -1)).toBe(2);
		expect(adjustToInteger(1, -1)).toBe(1);
		expect(adjustToInteger(0.5, -1)).toBe(0.5);
	});

	it('should decrease the value by 2 when step is -2', () => {
		expect(adjustToInteger(3, -2)).toBe(1);
		expect(adjustToInteger(3.5, -2)).toBe(2);
		expect(adjustToInteger(1.5, -2)).toBe(1);
		expect(adjustToInteger(0.5, -2)).toBe(0.5);
	});

	it('should handle values between 0 and 1 correctly with negative step', () => {
		expect(adjustToInteger(0.5, -1)).toBe(0.5);
		expect(adjustToInteger(0.5, -2)).toBe(0.5);
		expect(adjustToInteger(0.3, -1)).toBe(0.3);
		expect(adjustToInteger(0.3, -2)).toBe(0.3);
	});
});

describe('clamp', () => {
	it('should return the value if it is within the min and max range', () => {
		expect(clamp(5, 0, 10)).toBe(5);
	});

	it('should return the minimum value if the input is less than the minimum', () => {
		expect(clamp(-5, 0, 10)).toBe(0);
	});

	it('should return the maximum value if the input is greater than the maximum', () => {
		expect(clamp(15, 0, 10)).toBe(10);
	});
});

describe('roundTo', () => {
	it('should round a number to the specified precision', () => {
		expect(roundTo(3.14159, 2)).toBe(3.14);
		expect(roundTo(2.718, 3)).toBe(2.718);
		expect(roundTo(1.2345, 1)).toBe(1.2);
	});

	it('should round to the nearest integer if precision is 0', () => {
		expect(roundTo(4.6, 0)).toBe(5);
		expect(roundTo(7.2, 0)).toBe(7);
	});

	it('should handle negative precision', () => {
		expect(roundTo(1234.5, -2)).toBe(1200);
		expect(roundTo(9876.54, -1)).toBe(9880);
	});

	it('should handle very small numbers', () => {
		expect(roundTo(0.00001, 5)).toBe(0.00001);
		expect(roundTo(0.000001, 6)).toBe(0.000001);
	});

	it('should handle very large numbers', () => {
		expect(roundTo(1e20, 2)).toBe(1e20);
		expect(roundTo(1e30, 0)).toBe(1e30);
	});
});
