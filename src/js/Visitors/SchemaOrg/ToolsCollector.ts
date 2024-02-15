import type {
	HowToDirection,
	HowToSection,
	HowToStep,
	HowToTool,
	HowToTip,
} from 'cookbook/js/Models/schema';
import { ISchemaOrgVisitor } from 'cookbook/js/Interfaces/Visitors/ISchemaOrgVisitor';

/**
 * Visitor implementation to collect `HowToTool` items.
 */
export default class ToolsCollector implements ISchemaOrgVisitor {
	/**
	 * Local list of collected tools.
	 * @private
	 */
	private tools: HowToTool[] = [];

	/**
	 * Visits a HowToDirection element and extracts tools from its `tool` property.
	 * @param {HowToDirection} direction - The HowToDirection element to visit.
	 */
	visitHowToDirection(direction: HowToDirection) {
		// Check if the direction contains a tool and collect it
		this.tools.push(...direction.tool);
	}

	/**
	 * Visits a HowToSection element and recursively visits its child elements.
	 * @param {HowToSection} section - The HowToSection element to visit.
	 */
	visitHowToSection(section: HowToSection) {
		for (const element of section.itemListElement) {
			element.accept(this);
		}
	}

	/**
	 * Visits a HowToStep element and collects tools from its directions.
	 * @param {HowToStep} step - The HowToStep element to visit.
	 */
	visitHowToStep(step: HowToStep) {
		if (step.itemListElement) {
			for (const direction of step.itemListElement) {
				direction.accept(this);
			}
		}
	}

	/**
	 * Visits a HowToStep element and collects tools from its directions.
	 * @param {HowToTip} tip - The HowToStep element to visit.
	 */
	// eslint-disable-next-line @typescript-eslint/no-unused-vars,class-methods-use-this
	visitHowToTip(tip: HowToTip) {
		// Nothing to do for tip
	}

	/**
	 * Gets the collected tools.
	 * @returns {HowToTool[]} - The array of collected tools.
	 */
	getTools(): HowToTool[] {
		return this.tools;
	}
}
