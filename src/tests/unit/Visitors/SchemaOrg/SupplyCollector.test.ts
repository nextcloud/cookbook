import {
	HowToDirection,
	HowToSection,
	HowToStep,
	HowToSupply,
	HowToTip,
} from 'cookbook/js/Models/schema';
import SupplyCollector from 'cookbook/js/Visitors/SchemaOrg/SupplyCollector';

/**
 * Test suite for the SupplyCollector.
 */
describe('SupplyCollector', () => {
	let collector: SupplyCollector;

	beforeEach(() => {
		collector = new SupplyCollector();
	});

	it('should collect supplies from HowToDirection', () => {
		const direction = new HowToDirection('');
		direction.supply = [
			new HowToSupply('Ingredient 1'),
			new HowToSupply('Ingredient 2'),
		];

		collector.visitHowToDirection(direction);

		expect(collector.getSupplies()).toEqual(
			expect.arrayContaining(direction.supply),
		);
	});

	it('should collect supplies from HowToStep', () => {
		const step = new HowToStep('', [
			new HowToDirection('', {
				supply: [new HowToSupply('Ingredient 1')],
			}),
		]);

		collector.visitHowToStep(step);

		expect(collector.getSupplies()).toEqual(
			expect.arrayContaining(
				(step.itemListElement[0] as HowToDirection).supply,
			),
		);
	});

	it('should collect supplies from HowToSection', () => {
		const section = new HowToSection('');
		section.itemListElement = [
			new HowToDirection('', {
				supply: [new HowToSupply('Ingredient 1')],
			}),
		];

		collector.visitHowToSection(section);

		expect(collector.getSupplies()).toEqual(
			expect.arrayContaining(
				(section.itemListElement[0] as HowToDirection).supply,
			),
		);
	});

	it('should collect supplies from HowToDirection and HowToStep with nested HowToDirection', () => {
		const supply1 = new HowToSupply('Ingredient 1');
		const supply2 = new HowToSupply('Ingredient 2');

		const direction1 = new HowToDirection('', { supply: [supply1] });
		const direction2 = new HowToDirection('', { supply: [supply2] });

		const step = new HowToStep('', [direction2]);

		const section = new HowToSection('', {
			itemListElement: [direction1, step],
		});

		collector.visitHowToSection(section);

		expect(collector.getSupplies()).toEqual(
			expect.arrayContaining([supply1, supply2]),
		);
	});

	it('should not collect supplies from HowToTip', () => {
		const tip = new HowToTip('');

		collector.visitHowToTip(tip);

		expect(collector.getSupplies()).toEqual([]);
	});
});
