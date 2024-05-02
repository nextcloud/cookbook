/**
 * Test suite for the AndOperator class.
 */
import { AndOperator } from 'cookbook/js/LogicOperators';

describe('AndOperator', () => {
	const operator = new AndOperator();

	test('it should return correct search string for multiple items with a given label', () => {
		const items = ['"pizza"', '"pasta"'];
		const label = 'tag';
		const expectedOutput = 'tag:"pizza" tag:"pasta"';

		// Act
		const result =
			operator.generateStringRepresentationForMultipleOperandsWithLabel(
				items,
				label,
			);

		// Assert
		expect(result).toEqual(expectedOutput);
	});

	test('it should return correct search string for a single item with a given label', () => {
		const items = ['"pizza"'];
		const label = 'tag';
		const expectedOutput = 'tag:"pizza"';

		// Act
		const result =
			operator.generateStringRepresentationForMultipleOperandsWithLabel(
				items,
				label,
			);

		// Assert
		expect(result).toEqual(expectedOutput);
	});

	test('it should return an empty string for no items with a given label', () => {
		const items = [];
		const label = 'tag';
		const expectedOutput = '';

		// Act
		const result =
			operator.generateStringRepresentationForMultipleOperandsWithLabel(
				items,
				label,
			);

		// Assert
		expect(result).toEqual(expectedOutput);
	});
});
