import { computed, ref } from 'vue';
import {
    RecipeCategoriesFilter as CategoriesFilter,
    RecipeKeywordsFilter as KeywordsFilter,
    RecipeNamesFilter as NamesFilter,
} from '../../js/RecipeFilters';
import { useStore } from '../../store';

export default function useRecipeFilterControls(props) {
    const store = useStore();

    /**
     * @type {import('vue').Ref<string>}
     */
    const searchTerm = ref('');

    /**
     * @type {import('vue').Ref<Array>}
     */
    const selectedCategories = ref([]);

    /**
     * @type {import('vue').Ref<Array>}
     */
    const selectedKeywords = ref([]);

    const localValue = computed(() => ({
        categories: selectedCategories.value,
        keywords: selectedKeywords.value,
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
     * @type {ComputedRef<boolean>}
     */
    const hiddenSections = computed(() => {
        return {
            categories: props.preappliedFilters.some(
                (f) => f instanceof CategoriesFilter,
            ),
            keywords: props.preappliedFilters.some(
                (f) => f instanceof KeywordsFilter,
            ),
            names: props.preappliedFilters.some(
                (f) => f instanceof NamesFilter,
            ),
        };
    });

    /**
     * A unique set of all categories in the recipes.
     */
    const uniqueCategories = computed(() => [...new Set(rawCategories.value)]);

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
     * A unique set of all keywords in all recipes.
     */
    const uniqueKeywords = computed(() => [...new Set(rawKeywords.value)]);

    return {
        uniqueCategories,
        selectedCategories,
        uniqueKeywords,
        selectedKeywords,
        hiddenSections,
        searchTerm,
        localValue,
        store,
    };
}
