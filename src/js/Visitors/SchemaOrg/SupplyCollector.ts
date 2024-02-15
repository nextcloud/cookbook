import type {
	HowToDirection,
	HowToSection,
	HowToStep,
	HowToSupply,
	HowToTip,
} from 'cookbook/js/Models/schema';
import { ISchemaOrgVisitor } from 'cookbook/js/Interfaces/Visitors/ISchemaOrgVisitor';

/**
 * Visitor implementation to collect `HowToSupply` items.
 */
export default class SupplyCollector implements ISchemaOrgVisitor {
	private supplies: HowToSupply[] = [];

	/**
	 * Visits a HowToDirection element and extracts supplies from its `supply` property.
	 * @param {HowToDirection} direction - The HowToDirection element to visit.
	 */
	visitHowToDirection(direction: HowToDirection) {
		// Check if the direction contains a supply and collect it
		this.supplies.push(...direction.supply);
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
	 * Visits a HowToStep element and collects supplies from its directions.
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
	 * Visits a HowToStep element and collects supplies from its directions.
	 * @param {HowToTip} tip - The HowToStep element to visit.
	 */
	// eslint-disable-next-line @typescript-eslint/no-unused-vars,class-methods-use-this
	visitHowToTip(tip: HowToTip) {
		// Nothing to do for tip
	}

	/**
	 * Gets the collected supplies.
	 * @returns {HowToSupply[]} - The array of collected supplies.
	 */
	getSupplies(): HowToSupply[] {
		return this.supplies;
	}
}
