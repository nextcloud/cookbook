import { computed, ComputedRef, onMounted, ref, Ref } from 'vue';
import {
	RecipeCategoriesFilter as CategoriesFilter,
	RecipeFilter,
	RecipeKeywordsFilter as KeywordsFilter,
} from 'cookbook/js/RecipeFilters';
import FilterType from 'cookbook/js/Enums/FilterType';
import LogicOperatorType from 'cookbook/js/Enums/LogicOperatorType';
import {
	AndOperator,
	BinaryOperator,
	OrOperator,
} from 'cookbook/js/LogicOperators';
import parseSearchString from 'cookbook/js/utils/parseSearchString';
import compareRecipeFilters from 'cookbook/js/utils/compareRecipeFilters';
import { asArray } from 'cookbook/js/helper';
import { Recipe } from 'cookbook/js/Models/schema';

export default function useRecipeFilterControls(props, store) {
	/**
	 * @type {import('vue').Ref<string>}
	 */
	const searchTerm: Ref<string> = ref('');

	/**
	 * List of all selected categories.
	 * @type {import('vue').Ref<Array>}
	 */
	const selectedCategories: Ref<string[]> = ref([]);

	/**
	 * Value of the toggle for switching between the `AND` and `OR` operator fot the categories filter.
	 *
	 * `true` is associated with the `AndOperator`, `false` with the `OrOperator`.
	 * @type {import('vue').Ref<boolean>}
	 */
	const categoriesOperatorToggleValue: Ref<boolean> = ref(false);

	/**
	 * Logic operator to be used for filtering categories.
	 * @type {import('vue').Ref<BinaryOperator>}
	 */
	const categoriesOperator: ComputedRef<BinaryOperator> = computed(() =>
		categoriesOperatorToggleValue.value
			? new AndOperator()
			: new OrOperator(),
	);

	/**
	 * List of all selected keywords.
	 * @type {import('vue').Ref<string[]>}
	 */
	const selectedKeywords: Ref<string[]> = ref([]);

	/**
	 * Value of the toggle for switching between the `AND` and `OR` operator fot the keywords filter.
	 *
	 * `true` is associated with the `AndOperator`, `false` with the `OrOperator`.
	 * @type {import('vue').Ref<boolean>}
	 */
	const keywordsOperatorToggleValue: Ref<boolean> = ref(true);

	/**
	 * Local value of all set filters.
	 * @type {import('vue').Ref<RecipeFilter[]>}
	 */
	const localFiltersValue: Ref<RecipeFilter[]> = ref(
		store.state.recipeFilters,
	);

	/**
	 * Logic operator to be used for filtering keywords.
	 * @type {import('vue').Ref<BinaryOperator>}
	 */
	const keywordsOperator: ComputedRef<BinaryOperator> = computed(() =>
		keywordsOperatorToggleValue.value
			? new AndOperator()
			: new OrOperator(),
	);

	/**
	 * Updates the filter-controls UI elements like category and keyword dropdown.
	 * @param {RecipeFilter[]} filters - List of filters that should be represented by the UI
	 * @param {boolean} updateSearchField - If the search input should also be updated. Default is `true`.
	 */
	function updateFiltersUI(
		filters: RecipeFilter[],
		updateSearchField: boolean = true,
	) {
		// Collect all filters that filter for category
		const categoryFilters = filters.filter(
			(f) => f.type === FilterType.CategoriesFilter,
		);
		// Update category selection control element with selected categories
		if (categoryFilters.length === 1) {
			selectedCategories.value = asArray(
				(categoryFilters[0] as CategoriesFilter).categories,
			);
			categoriesOperatorToggleValue.value =
				categoryFilters[0].operator.type === LogicOperatorType.And;
		}

		// Collect all filters that filter for keyword
		const keywordFilters = filters.filter(
			(f) => f.type === FilterType.KeywordsFilter,
		);
		// Update keyword selection control element with selected keywords
		if (keywordFilters.length === 1) {
			selectedKeywords.value = asArray(
				(keywordFilters[0] as KeywordsFilter).keywords,
			);
			keywordsOperatorToggleValue.value =
				keywordFilters[0].operator.type === LogicOperatorType.And;
		}

		// Update string shown in the search field
		if (updateSearchField) {
			searchTerm.value = filters.map((f) => f.toSearchString()).join(' ');
		}
	}

	onMounted(() => {
		updateFiltersUI(localFiltersValue.value);
	});

	/**
	 * Update UI controls when the filters are updated.
	 */
	// watch(
	// 	() => localFiltersValue.value,
	// 	(filters, oldFilters) => {
	// 		if (filters === oldFilters) return;
	//
	// 		updateFiltersUI(filters);
	// 	},
	// );

	/**
	 * An array of all categories in the recipes. These are neither sorted nor unique
	 */
	const rawCategories: ComputedRef<string[]> = computed(() => {
		const categoriesArray = props.recipes.map((r: Recipe) => {
			if (!('recipeCategory' in r)) {
				return [];
			}
			if (Array.isArray(r.recipeCategory)) return r.recipeCategory;
			if (r.recipeCategory != null) {
				return r.recipeCategory.split(',');
			}
			return [];
		});
		return [].concat(...categoriesArray);
	});

	/**
	 * A unique set of all categories in the recipes.
	 * @type {import('vue').ComputedRef<Array<string>>}
	 */
	const uniqueCategories: ComputedRef<string[]> = computed(() => [
		...new Set(rawCategories.value),
	]);

	/**
	 * An array of all keywords in the recipes. These are neither sorted nor unique
	 */
	const rawKeywords: ComputedRef<string[]> = computed(() => {
		const keywordsArray = props.recipes.map((r: Recipe) => {
			if (!('keywords' in r)) {
				return [];
			}
			return r.keywords;
		});
		return [].concat(...keywordsArray);
	});

	/**
	 * A unique set of all keywords in all recipes.
	 */
	const uniqueKeywords: ComputedRef<string[]> = computed(() => [
		...new Set(rawKeywords.value),
	]);

	function onCategoriesSelectionUpdated() {
		// Create new filter from current selection
		const filter = new CategoriesFilter(
			selectedCategories.value,
			categoriesOperator.value,
		);

		// remove all current category filters
		localFiltersValue.value = localFiltersValue.value.filter(
			(f) => f.type !== 'CategoriesFilter',
		);

		// Add UI-based category filter
		localFiltersValue.value.push(filter);
		updateFiltersUI(localFiltersValue.value);
	}

	function onKeywordsSelectionUpdated(): void {
		// Create new filter from current selection
		const filter = new KeywordsFilter(
			selectedKeywords.value,
			keywordsOperator.value,
		);

		// remove all current keyword filters
		localFiltersValue.value = localFiltersValue.value.filter(
			(f) => f.type !== 'KeywordsFilter',
		);

		// Add UI-based keyword filter
		localFiltersValue.value.push(filter);
		updateFiltersUI(localFiltersValue.value);
	}

	function onSearchInputUpdated(): void {
		const parsedFilters = parseSearchString(searchTerm.value);

		if (compareRecipeFilters(parsedFilters, store.state.recipeFilters))
			return;

		localFiltersValue.value = parsedFilters;

		// Update category/keyword/... selection controls
		updateFiltersUI(parsedFilters, false);
	}

	function onUiControlsUpdated(): void {
		// Create new filter from current selection
		const categoriesFilter = new CategoriesFilter(
			selectedCategories.value,
			categoriesOperator.value,
		);

		// Create new filter from current selection
		const keywordsFilter = new KeywordsFilter(
			selectedKeywords.value,
			keywordsOperator.value,
		);

		// remove all current category and keyword filters
		localFiltersValue.value = localFiltersValue.value.filter(
			(f) =>
				f.type !== FilterType.CategoriesFilter &&
				f.type !== FilterType.KeywordsFilter,
		);

		// Add UI-based category and keyword filters
		localFiltersValue.value.push(categoriesFilter);
		localFiltersValue.value.push(keywordsFilter);
		updateFiltersUI(localFiltersValue.value);
	}

	function submitFilters(): void {
		store.dispatch('setRecipeFilters', parseSearchString(searchTerm.value));
	}

	return {
		uniqueCategories,
		selectedCategories,
		uniqueKeywords,
		selectedKeywords,
		searchTerm,
		localFiltersValue,
		categoriesOperatorToggleValue,
		categoriesOperator,
		keywordsOperatorToggleValue,
		keywordsOperator,
		onCategoriesSelectionUpdated,
		onKeywordsSelectionUpdated,
		onSearchInputUpdated,
		onUiControlsUpdated,
		submitFilters,
		updateFiltersUI,
	};
}
