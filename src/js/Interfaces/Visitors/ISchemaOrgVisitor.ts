import type {
	HowToDirection,
	HowToSection,
	HowToStep,
	HowToTip,
} from 'cookbook/js/Models/schema';

export interface ISchemaOrgVisitor {
	visitHowToDirection(element: HowToDirection): void;
	visitHowToSection(element: HowToSection): void;
	visitHowToStep(element: HowToStep): void;
	visitHowToTip(tip: HowToTip): void;
}
