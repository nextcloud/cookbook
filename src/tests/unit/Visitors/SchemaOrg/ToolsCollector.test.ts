import {
	HowToDirection,
	HowToSection,
	HowToStep,
	HowToTool,
	HowToTip,
} from 'cookbook/js/Models/schema';
import ToolsCollector from 'cookbook/js/Visitors/SchemaOrg/ToolsCollector';

/**
 * Test suite for the ToolsCollector.
 */
describe('ToolsCollector', () => {
	let collector: ToolsCollector;

	beforeEach(() => {
		collector = new ToolsCollector();
	});

	it('should collect tools from HowToDirection', () => {
		const direction = new HowToDirection('', {
			tool: [new HowToTool('Tool 1')],
		});

		collector.visitHowToDirection(direction);

		expect(collector.getTools()).toEqual(
			expect.arrayContaining(direction.tool),
		);
	});

	it('should collect tools from HowToStep', () => {
		const step = new HowToStep('', [
			new HowToDirection('', {
				tool: new HowToTool('Tool 1'),
			}),
		]);

		collector.visitHowToStep(step);

		expect(collector.getTools()).toEqual(
			expect.arrayContaining(
				(step.itemListElement[0] as HowToDirection).tool,
			),
		);
	});

	it('should collect tools from HowToSection', () => {
		const section = new HowToSection('');
		section.itemListElement = [
			new HowToDirection('', {
				tool: new HowToTool('Tool 1'),
			}),
		];

		collector.visitHowToSection(section);

		expect(collector.getTools()).toEqual(
			expect.arrayContaining(
				(section.itemListElement[0] as HowToDirection).tool,
			),
		);
	});

	it('should collect tools from HowToDirection and HowToStep with nested HowToDirection', () => {
		const tool1 = new HowToTool('Tool 1');
		const tool2 = new HowToTool('Tool 2');

		const direction1 = new HowToDirection('', { tool: [tool1] });
		const direction2 = new HowToDirection('', { tool: [tool2] });

		const step = new HowToStep('', [direction2]);

		const section = new HowToSection('', {
			itemListElement: [direction1, step],
		});

		collector.visitHowToSection(section);

		expect(collector.getTools()).toEqual(
			expect.arrayContaining([tool1, tool2]),
		);
	});

	it('should not collect tools from HowToTip', () => {
		const tip = new HowToTip('');

		collector.visitHowToTip(tip);

		expect(collector.getTools()).toEqual([]);
	});
});
