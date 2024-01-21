import NutritionInformation, {
	NutritionInformationProperties,
} from '../../../../js/Models/schema/NutritionInformation';

describe('NutritionInformation', () => {
	describe('constructor', () => {
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
			expect(nutritionInfo.proteinContent).toBe(
				properties.proteinContent,
			);
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

	describe('fromJSON', () => {
		it('should create a NutritionInformation instance from valid JSON', () => {
			const validJSON =
				'{"calories": "100", "carbohydrateContent": "20g", "cholesterolContent": "10mg", "fatContent": "5g", "fiberContent": "3g", "proteinContent": "8g", "saturatedFatContent": "2g", "servingSize": "1 cup", "sodiumContent": "300mg", "sugarContent": "5g", "transFatContent": "0g", "unsaturatedFatContent": "3g"}';

			const nutritionInfo = NutritionInformation.fromJSON(validJSON);

			expect(nutritionInfo).toBeInstanceOf(NutritionInformation);
			expect(nutritionInfo.calories).toEqual('100');
			expect(nutritionInfo.carbohydrateContent).toEqual('20g');
			expect(nutritionInfo.cholesterolContent).toEqual('10mg');
			expect(nutritionInfo.fatContent).toEqual('5g');
			expect(nutritionInfo.fiberContent).toEqual('3g');
			expect(nutritionInfo.proteinContent).toEqual('8g');
			expect(nutritionInfo.saturatedFatContent).toEqual('2g');
			expect(nutritionInfo.servingSize).toEqual('1 cup');
			expect(nutritionInfo.sodiumContent).toEqual('300mg');
			expect(nutritionInfo.sugarContent).toEqual('5g');
			expect(nutritionInfo.transFatContent).toEqual('0g');
			expect(nutritionInfo.unsaturatedFatContent).toEqual('3g');
		});

		it('should create a NutritionInformation instance with missing properties from JSON', () => {
			const validJSON =
				'{"calories": "100", "fatContent": "5g", "proteinContent": "8g", "saturatedFatContent": "2g"}';

			const nutritionInfo = NutritionInformation.fromJSON(validJSON);

			expect(nutritionInfo).toBeInstanceOf(NutritionInformation);
			expect(nutritionInfo.calories).toEqual('100');
			expect(nutritionInfo.fatContent).toEqual('5g');
			expect(nutritionInfo.proteinContent).toEqual('8g');
			expect(nutritionInfo.saturatedFatContent).toEqual('2g');
			// Other properties should be undefined
			expect(nutritionInfo.carbohydrateContent).toBeUndefined();
			expect(nutritionInfo.cholesterolContent).toBeUndefined();
			expect(nutritionInfo.fiberContent).toBeUndefined();
			expect(nutritionInfo.servingSize).toBeUndefined();
			expect(nutritionInfo.sodiumContent).toBeUndefined();
			expect(nutritionInfo.sugarContent).toBeUndefined();
			expect(nutritionInfo.transFatContent).toBeUndefined();
			expect(nutritionInfo.unsaturatedFatContent).toBeUndefined();
		});

		it('should throw an error for invalid JSON with non-string (number) property values', () => {
			const invalidJSON =
				'{"calories": 100, "fatContent": "5g", "proteinContent": "8g", "saturatedFatContent": "2g"}';

			expect(() =>
				NutritionInformation.fromJSON(invalidJSON),
			).toThrowError(
				'Invalid property value: "calories" must be a string',
			);
		});

		it('should throw an error for invalid JSON with non-string (object) property values', () => {
			const invalidJSON =
				'{"calories": "100", "fatContent": {"value": "5g"}, "proteinContent": "8g", "saturatedFatContent": "2g"}';

			expect(() =>
				NutritionInformation.fromJSON(invalidJSON),
			).toThrowError(
				'Invalid property value: "fatContent" must be a string',
			);
		});

		test('should throw an error for invalid JSON', () => {
			const invalidJson = 'Invalid JSON string';

			expect(() => NutritionInformation.fromJSON(invalidJson)).toThrow(
				'Error mapping to "NutritionInformation". Received invalid JSON: "Invalid JSON string"',
			);
		});
	});
});
