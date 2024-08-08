import { computed, ref } from 'vue';
import {
    RecipeCategoriesFilter as CategoriesFilter,
    RecipeKeywordsFilter as KeywordsFilter,
    RecipeNamesFilter as NamesFilter,
} from '../../js/RecipeFilters';
import { useStore } from '../../store';
import { AndOperator, OrOperator } from '../../js/LogicOperators';

export default function useRecipeFilterControls(props) {
    const store = useStore();

    // Helper method that sorts strings case insensitively
    let caseInsensitiveSort = (a, b) => {
        a = a.toUpperCase();
        b = b.toUpperCase();
        if (a < b) return -1;
        if (b < a) return 1;
        return 0;
    };

    /**
     * @type {import('vue').Ref<string>}
     */
    const searchTerm = ref('');

    /**
     * List of all selected categories.
     * @type {import('vue').Ref<Array>}
     */
    const selectedCategories = ref([]);

    /**
     * Value of the toggle for switching between the `AND` and `OR` operator fot the categories filter.
     *
     * `true` is associated with the `AndOperator`, `false` with the `OrOperator`.
     * @type {import('vue').Ref<boolean>}
     */
    const categoriesOperatorToggleValue = ref(false);

    /**
     * Logic operator to be used for filtering categories.
     * @type {import('vue').Ref<AndOperator|OrOperator>}
     */
    const categoriesOperator = computed(() =>
        categoriesOperatorToggleValue.value
            ? new AndOperator()
            : new OrOperator(),
    );

    /**
     * List of all selected keywords.
     * @type {import('vue').Ref<Array>}
     */
    const selectedKeywords = ref([]);

    /**
     * Value of the toggle for switching between the `AND` and `OR` operator fot the keywords filter.
     *
     * `true` is associated with the `AndOperator`, `false` with the `OrOperator`.
     * @type {import('vue').Ref<boolean>}
     */
    const keywordsOperatorToggleValue = ref(true);

    /**
     * Logic operator to be used for filtering keywords.
     * @type {import('vue').Ref<AndOperator|OrOperator>}
     */
    const keywordsOperator = computed(() =>
        keywordsOperatorToggleValue.value
            ? new AndOperator()
            : new OrOperator(),
    );

    /**
     * Local value of all set filters.
     * @type {import('vue').ComputedRef<{searchTerm: string, keywords: RecipeKeywordsFilter, categories: RecipeCategoriesFilter}>}
     */
    const localFiltersValue = computed(() => ({
        categories: new CategoriesFilter(
            selectedCategories.value,
            categoriesOperator.value,
        ),
        keywords: new KeywordsFilter(
            selectedKeywords.value,
            keywordsOperator.value,
            true,
        ),
        searchTerm: searchTerm.value,
    }));

    /**
     * An array of all categories in the recipes. These are neither sorted nor unique
     */
    const rawCategories = computed(() => {
        const categoriesArray = props.recipes.map((r) => {
            if (!('category' in r)) {
                return [];
            }
            if (r.category != null) {
                return r.category.split(',');
            }
            return [];
        });
        return [].concat(...categoriesArray);
    });

    /**
     * List of sections with their visible state.
     * @type {import('vue').ComputedRef<boolean>}
     */
    const hiddenSections = computed(() => ({
        categories: props.preappliedFilters.some(
            (f) => f instanceof CategoriesFilter,
        ),
        keywords: props.preappliedFilters.some(
            (f) => f instanceof KeywordsFilter,
        ),
        names: props.preappliedFilters.some((f) => f instanceof NamesFilter),
    }));

    /**
     * A unique set of all categories in the recipes.
     * @type {import('vue').ComputedRef<Array<string>>}
     */
    const uniqueCategories = computed(() =>
        [...new Set(rawCategories.value)].sort(caseInsensitiveSort));

    /**
     * An array of all keywords in the recipes. These are neither sorted nor unique
     */
    const rawKeywords = computed(() => {
        const keywordsArray = props.recipes.map((r) => {
            if (!('keywords' in r)) {
                return [];
            }
            if (r.keywords != null) {
                return r.keywords.split(',');
            }
            return [];
        });
        return [].concat(...keywordsArray);
    });

    /**
     * A unique and sorted set of all keywords in all recipes.
     */
    const uniqueKeywords = computed(() =>
        [...new Set(rawKeywords.value)].sort(caseInsensitiveSort));

    return {
        uniqueCategories,
        selectedCategories,
        uniqueKeywords,
        selectedKeywords,
        hiddenSections,
        searchTerm,
        localFiltersValue,
        categoriesOperatorToggleValue,
        categoriesOperator,
        keywordsOperatorToggleValue,
        keywordsOperator,
        store,
    };
}
