<template>
    <div class="pt-2">
        <div v-if="loading" class="loading-indicator">
            <LoadingIndicator :delay="800" :size="40" />
        </div>
        <div v-else>
            <div v-if="recipes.length === 0">
                <EmptyList />
            </div>
            <div v-else>
                <RecipeFilterControlsModal
                    v-if="isMobile && showFiltersInRecipeList"
                    v-model="filterValue"
                    :preapplied-filters="props.preappliedFilters"
                    :recipes="recipes"
                    :is-loading="loading"
                    :is-visible="isFilterControlsVisible"
                    @close="() => (isFilterControlsVisible = false)"
                />
                <RecipeFilterControlsInline
                    v-else-if="showFiltersInRecipeList"
                    v-model="inlineControlsValue"
                    :preapplied-filters="props.preappliedFilters"
                    :recipes="recipes"
                    :is-loading="loading"
                    :is-visible="isFilterControlsVisible"
                    @input="handleInlineControlsValueUpdated"
                    @close="() => (isFilterControlsVisible = false)"
                />
                <div
                    v-if="isMobile && showFiltersInRecipeList"
                    id="recipes-submenu"
                    class="recipes-submenu-container"
                >
                    <RecipeSortSelect
                        v-if="recipes.length > 0"
                        v-model="orderBy"
                        class="mr-4"
                        :title="t('cookbook', 'Show filter settings')"
                        aria-label="t('cookbook', 'Show settings for filtering recipe list')"
                    />
                    <NcButton
                        :type="'secondary'"
                        aria-label="t('cookbook', 'Show settings for filtering recipe list')"
                        :title="t('cookbook', 'Show filter settings')"
                        @click="toggleFilterControls"
                    >
                        <template #icon>
                            <FilterIcon :size="20" />
                        </template>
                    </NcButton>
                </div>
                <RecycleScroller
                    page-mode
                    class="recipes-virtual"
                    :items="visibleRecipes"
                    :item-size="130"
                    :grid-items="gridItems"
                    :item-secondary-size="332"
                    key-field="recipe_id"
                >
                    <template #default="{ item }">
                        <RecipeCard :recipe="item" />
                    </template>
                </RecycleScroller>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import FilterIcon from 'vue-material-design-icons/FilterVariant.vue';
import { RecycleScroller } from 'vue-virtual-scroller';
import 'vue-virtual-scroller/dist/vue-virtual-scroller.css';

import { NcButton } from '@nextcloud/vue';
import { useIsMobile } from '../../composables/useIsMobile';
import { useLegacyStore } from '../../store';
import applyRecipeFilters from '../../js/utils/applyRecipeFilters';
import {
    RecipeCategoriesFilter as CategoriesFilter,
    RecipeKeywordsFilter as KeywordsFilter,
    RecipeNamesFilter as NamesFilter,
} from '../../js/RecipeFilters';
import EmptyList from './EmptyList.vue';
import LoadingIndicator from '../Utilities/LoadingIndicator.vue';
import RecipeCard from './RecipeCard.vue';
import RecipeFilterControlsInline from './RecipeFilterControlsInline.vue';
import RecipeFilterControlsModal from './RecipeFilterControlsModal.vue';
import RecipeSortSelect from './RecipeSortSelect.vue';
import { AndOperator } from '../../js/LogicOperators';

const isMobile = useIsMobile();
const legacyStore = useLegacyStore();

const props = defineProps({
    loading: {
        type: Boolean,
        default: false,
    },
    /**
     * Array of `RecipeFilter`s which have already been applied in advance
     */
    preappliedFilters: {
        type: Array,
        default: () => [],
    },
    recipes: {
        type: Array,
        default: () => [],
        required: true,
    },
});

/**
 * If the filter controls are visible
 * @type {import('vue').Ref<boolean>}
 */
const isFilterControlsVisible = ref(false);
/**
 *
 * @type {import('vue').Ref<object>}
 */
const filterValue = ref({
    categories: new CategoriesFilter([]),
    keywords: new KeywordsFilter([]),
});

/**
 * Workaround. Should be replaced by two v-models in vue3
 * @type {import('vue').Ref<object>}
 */
const inlineControlsValue = ref();

const orderBy = ref({
    label: t('cookbook', 'Name'),
    iconUp: true,
    recipeProperty: 'name',
    order: 'ascending',
});

// The recipe grid adapts to window width. One card cell is 300px wide
// (.recipe-card) plus a 1rem margin on each side ≈ 332px. The Nextcloud
// navigation pane occupies roughly 300px when visible; subtract it from
// window.innerWidth to estimate the available content area.
const ITEM_SECONDARY_SIZE = 332;
const NC_SIDEBAR = 300;
const calcGridItems = () =>
    Math.max(
        1,
        Math.floor((window.innerWidth - NC_SIDEBAR) / ITEM_SECONDARY_SIZE),
    );
const gridItems = ref(calcGridItems());
const onWindowResize = () => {
    gridItems.value = calcGridItems();
};

onMounted(() => {
    legacyStore.clearRecipeFilters();
    window.addEventListener('resize', onWindowResize);
});
onBeforeUnmount(() => {
    window.removeEventListener('resize', onWindowResize);
});

// ===================
// Methods
// ===================

/**
 * Handle updated value of the inline filter controls. Should be fixed in vue3 by using two v-model directives.
 */
function handleInlineControlsValueUpdated() {
    filterValue.value = inlineControlsValue.value.filters;
    orderBy.value = inlineControlsValue.value.orderBy;
}

/* Sort recipes according to the property of the recipe ascending or
 * descending
 */
const sortRecipes = (recipes, recipeProperty, order) => {
    // A shallow copy is enough — sort() needs a fresh array because it
    // mutates in place, but it does not touch the recipe objects
    // themselves. Deep-cloning via JSON round-trip allocates millions of
    // strings/objects on libraries with tens of thousands of recipes for
    // no benefit.
    const rec = recipes.slice();
    return rec.sort((r1, r2) => {
        if (order !== 'ascending' && order !== 'descending') return 0;
        if (order === 'ascending') {
            if (
                recipeProperty === 'dateCreated' ||
                recipeProperty === 'dateModified'
            ) {
                return (
                    new Date(r1[recipeProperty]) - new Date(r2[recipeProperty])
                );
            }
            if (recipeProperty === 'name') {
                return r1[recipeProperty].localeCompare(r2[recipeProperty]);
            }
            if (!Number.isNaN(r1[recipeProperty] - r2[recipeProperty])) {
                return r1[recipeProperty] - r2[recipeProperty];
            }
            return 0;
        }

        if (
            recipeProperty === 'dateCreated' ||
            recipeProperty === 'dateModified'
        ) {
            return new Date(r2[recipeProperty]) - new Date(r1[recipeProperty]);
        }
        if (recipeProperty === 'name') {
            return r2[recipeProperty].localeCompare(r1[recipeProperty]);
        }
        if (!Number.isNaN(r2[recipeProperty] - r1[recipeProperty])) {
            return r2[recipeProperty] - r1[recipeProperty];
        }
        return 0;
    });
};

function toggleFilterControls() {
    isFilterControlsVisible.value = !isFilterControlsVisible.value;
}

// ===================
// Computed properties
// ===================

/**
 * An array of the filtered recipes, with all filters applied.
 */
const filteredRecipes = computed(() => {
    const recipeFilters = [
        filterValue.value.categories,
        filterValue.value.keywords,
        new NamesFilter(legacyStore.recipeFilters, new AndOperator(), 'fuzzy'),
    ];
    return applyRecipeFilters(props.recipes, recipeFilters);
});

// Recipes ordered ascending by name
const recipesNameAsc = computed(() =>
    sortRecipes(props.recipes, 'name', 'ascending'),
);

// Recipes ordered descending by name
const recipesNameDesc = computed(() =>
    sortRecipes(props.recipes, 'name', 'descending'),
);

// Recipes ordered ascending by creation date
const recipesDateCreatedAsc = computed(() =>
    sortRecipes(props.recipes, 'dateCreated', 'ascending'),
);

// Recipes ordered descending by creation date
const recipesDateCreatedDesc = computed(() =>
    sortRecipes(props.recipes, 'dateCreated', 'descending'),
);

// Recipes ordered ascending by modification date
const recipesDateModifiedAsc = computed(() =>
    sortRecipes(props.recipes, 'dateModified', 'ascending'),
);

// Recipes ordered descending by modification date
const recipesDateModifiedDesc = computed(() =>
    sortRecipes(props.recipes, 'dateModified', 'descending'),
);

// An array of recipe objects of all recipes with links to the recipes and a property if the recipe is to be shown
const recipeObjects = computed(() => {
    // Materialise the filter match-set into a Set once per recomputation.
    // The previous "filteredRecipes.value.map(...).includes(...)" pattern
    // ran an O(N) scan inside the per-recipe map() — i.e. O(N^2) overall,
    // which is roughly a billion operations and a multi-second freeze on
    // a 30k-recipe cookbook. Set.has() is O(1).
    const filteredIds = new Set(
        filteredRecipes.value.map((r) => r.recipe_id),
    );
    function makeObject(rec) {
        return {
            recipe: rec,
            show: filteredIds.has(rec.recipe_id),
        };
    }

    if (
        orderBy.value === null ||
        orderBy.value === undefined ||
        (orderBy.value.order !== 'ascending' &&
            orderBy.value.order !== 'descending')
    ) {
        return props.recipes.map(makeObject);
    }
    if (orderBy.value.recipeProperty === 'dateCreated') {
        if (orderBy.value.order === 'ascending') {
            return recipesDateCreatedAsc.value.map(makeObject);
        }
        return recipesDateCreatedDesc.value.map(makeObject);
    }
    if (orderBy.value.recipeProperty === 'dateModified') {
        if (orderBy.value.order === 'ascending') {
            return recipesDateModifiedAsc.value.map(makeObject);
        }
        return recipesDateModifiedDesc.value.map(makeObject);
    }
    if (orderBy.value.recipeProperty === 'name') {
        if (orderBy.value.order === 'ascending') {
            return recipesNameAsc.value.map(makeObject);
        }
        return recipesNameDesc.value.map(makeObject);
    }
    return props.recipes.map(makeObject);
});

// Final list passed to the virtualized scroller. Hidden recipeObjects
// are filtered out completely so the scroller's primary axis size and
// scrollbar match the visible content.
const visibleRecipes = computed(() =>
    recipeObjects.value
        .filter((o) => o.show)
        .map((o) => o.recipe),
);

const showFiltersInRecipeList = computed(
    () => legacyStore.localSettings.showFiltersInRecipeList,
);
</script>

<script>
export default {
    name: 'RecipeList',
};
</script>

<style>
/* stylelint-disable selector-class-pattern */
#recipes-submenu .multiselect .multiselect__tags {
    border: 0;
}
/* stylelint-enable selector-class-pattern */
</style>

<style scoped>
.mr-4 {
    margin-inline-end: 1rem;
}

.pt-2 {
    padding-top: 0.5rem;
}

.loading-indicator {
    display: flex;
    justify-content: center;
    padding: 3rem 0;
}

.recipes-submenu-container {
    display: flex;
    padding: 0.5rem 16px 16px;
    margin-bottom: 0.75ex;
}

.recipes {
    display: flex;
    width: 100%;
    flex-direction: row;
    flex-wrap: wrap;
}

/* Virtualized scroller container. page-mode uses the document scrollbar
 * so no explicit height is required — items render inside the normal
 * flow and only the visible cells live in the DOM. */
.recipes-virtual {
    width: 100%;
}
</style>
