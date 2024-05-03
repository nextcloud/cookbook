/**
 * Test suite for the OrOperator class.
 */
import { OrOperator } from 'cookbook/js/LogicOperators';

describe('OrOperator', () => {
	const operator = new OrOperator();

	test('it should return correct search string for multiple items with a given label', () => {
		const items = ['"pizza"', '"pasta"'];
		const label = 'tag';
		const expectedOutput = 'tag:"pizza","pasta"';
		expect(
			operator.generateStringRepresentationForMultipleOperandsWithLabel(
				items,
				label,
			),
		).toEqual(expectedOutput);
	});

	test('it should return correct search string for a single item with a given label', () => {
		const items = ['"pizza"'];
		const label = 'tag';
		const expectedOutput = 'tag:"pizza"';
		expect(
			operator.generateStringRepresentationForMultipleOperandsWithLabel(
				items,
				label,
			),
		).toEqual(expectedOutput);
	});

	test('it should return an empty string for no items with a given label', () => {
		const items = [];
		const label = 'tag';
		const expectedOutput = '';
		expect(
			operator.generateStringRepresentationForMultipleOperandsWithLabel(
				items,
				label,
			),
		).toEqual(expectedOutput);
	});
});