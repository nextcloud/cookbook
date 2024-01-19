import NutritionInformation, {
	NutritionInformationProperties,
} from '../../../../js/Models/schema/NutritionInformation';

describe('NutritionInformation', () => {
	test('should set the @type property to "NutritionInformation"', () => {
		const properties: NutritionInformationProperties = {};

		const nutritionInfo = new NutritionInformation(properties);

		expect(nutritionInfo['@type']).toBe('NutritionInformation');
	});

	test('should create an instance of NutritionInformation with specified properties', () => {
		const properties: NutritionInformationProperties = {
			calories: '100',
			carbohydrateContent: '20',
			proteinContent: '15',
			servingSize: '1 cup',
			sodiumContent: '200',
		};

		const nutritionInfo = new NutritionInformation(properties);

		expect(nutritionInfo).toBeInstanceOf(NutritionInformation);
		expect(nutritionInfo.calories).toBe(properties.calories);
		expect(nutritionInfo.carbohydrateContent).toBe(
			properties.carbohydrateContent,
		);
		expect(nutritionInfo.cholesterolContent).toBeUndefined(); // Added test for cholesterolContent
		expect(nutritionInfo.fatContent).toBeUndefined(); // Added test for fatContent
		expect(nutritionInfo.fiberContent).toBeUndefined(); // Added test for fiberContent
		expect(nutritionInfo.proteinContent).toBe(properties.proteinContent);
		expect(nutritionInfo.saturatedFatContent).toBeUndefined(); // Added test for saturatedFatContent
		expect(nutritionInfo.servingSize).toBe(properties.servingSize);
		expect(nutritionInfo.sodiumContent).toBe(properties.sodiumContent);
		expect(nutritionInfo.sugarContent).toBeUndefined(); // Added test for sugarContent
		expect(nutritionInfo.transFatContent).toBeUndefined(); // Added test for transFatContent
		expect(nutritionInfo.unsaturatedFatContent).toBeUndefined(); // Added test for unsaturatedFatContent
	});

	test('should create an instance of NutritionInformation with all properties set to undefined', () => {
		const nutritionInfo = new NutritionInformation();

		expect(nutritionInfo).toBeInstanceOf(NutritionInformation);
		expect(nutritionInfo.calories).toBeUndefined();
		expect(nutritionInfo.carbohydrateContent).toBeUndefined();
		expect(nutritionInfo.cholesterolContent).toBeUndefined();
		expect(nutritionInfo.fatContent).toBeUndefined();
		expect(nutritionInfo.fiberContent).toBeUndefined();
		expect(nutritionInfo.proteinContent).toBeUndefined();
		expect(nutritionInfo.saturatedFatContent).toBeUndefined();
		expect(nutritionInfo.servingSize).toBeUndefined();
		expect(nutritionInfo.sodiumContent).toBeUndefined();
		expect(nutritionInfo.sugarContent).toBeUndefined();
		expect(nutritionInfo.transFatContent).toBeUndefined();
		expect(nutritionInfo.unsaturatedFatContent).toBeUndefined();
	});
});
