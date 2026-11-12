<!--
SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors

SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later
-->

<template>
    <div class="pt-2">
        <div v-if="loading" class="loading-indicator">
            <LoadingIndicator :delay="800" :size="40" />
        </div>
        <div v-else>
            <div v-if="recipeObjects.length === 0">
                <EmptyList />
            </div>
            <div v-else>
                <RecipeListFilter
                    v-model:search-term="searchTerm"
                    v-model:filter-categories="filterCategories"
                    v-model:filter-categories-type-use-and="
                        filterCategoriesTypeUseAnd
                    "
                    v-model:filter-keywords="filterKeywords"
                    v-model:filter-keywords-type-use-and="
                        filterKeywordsTypeUseAnd
                    "
                    v-model:order-by="orderBy"
                    :preapplied-filters="props.preappliedFilters"
                    :recipes="recipes"
                    :is-loading="loading"
                    :is-visible="isFilterControlsVisible"
                />
                <!--
                    Virtualized: one v-show'd <li> per recipe put every card
                    in the DOM - 30k nodes froze the browser on large
                    libraries. Only the visible rows are rendered now.
                -->
                <div ref="listEl">
                    <RecycleScroller
                        page-mode
                        class="recipes-virtual"
                        :items="visibleRecipes"
                        :item-size="cellHeight"
                        :grid-items="gridItems"
                        :item-secondary-size="cellWidth"
                        key-field="recipe_id"
                        emit-update
                        @update="measureCell"
                    >
                        <template #default="{ item }">
                            <RecipeCard :recipe="item" />
                        </template>
                    </RecycleScroller>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { RecycleScroller } from 'vue-virtual-scroller';
import 'vue-virtual-scroller/index.css';
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
import RecipeListFilter from './RecipeListFilter.vue';
import { AndOperator, OrOperator } from '../../js/LogicOperators';

const legacyStore = useLegacyStore();
const t = window.t;

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
        required: true,
    },
});

const searchTerm = ref('');
const filterCategories = ref([]);
const filterCategoriesTypeUseAnd = ref(false);
const filterKeywords = ref([]);
const filterKeywordsTypeUseAnd = ref(true);

/**
 * If the filter controls are visible
 * @type {import('vue').Ref<boolean>}
 */
const isFilterControlsVisible = ref(false);

// Grid geometry for the virtual scroller, measured instead of assumed: a cell
// is a rendered card plus its margins, the column count is the list's own
// width over the cell width. The initial values (today's RecipeCard: 300px
// + 2 × 1rem wide, 105px + 1.5rem tall) only last until the first card is
// laid out.
const listEl = ref<HTMLElement | null>(null);
const cellWidth = ref(332);
const cellHeight = ref(130);
const listWidth = ref(0);
const gridItems = computed(() =>
    Math.max(1, Math.floor(listWidth.value / cellWidth.value)),
);

function measureCell() {
    const card = listEl.value?.querySelector<HTMLElement>('.recipe-card');
    if (!card) return;
    const css = getComputedStyle(card);
    const width = Math.round(
        card.offsetWidth +
            parseFloat(css.marginLeft) +
            parseFloat(css.marginRight),
    );
    const height = Math.round(
        card.offsetHeight +
            parseFloat(css.marginTop) +
            parseFloat(css.marginBottom),
    );
    if (width > 0) cellWidth.value = width;
    if (height > 0) cellHeight.value = height;
}

const resizeObserver = new ResizeObserver(([entry]) => {
    listWidth.value = entry.contentRect.width;
});
watch(
    listEl,
    (el, old) => {
        if (old) resizeObserver.unobserve(old);
        if (el) resizeObserver.observe(el);
    },
    { flush: 'post' },
);
onBeforeUnmount(() => resizeObserver.disconnect());

const orderBy = ref({
    label: t('cookbook', 'Name'),
    iconUp: true,
    recipeProperty: 'name',
    order: 'ascending',
});

// ===================
// Methods
// ===================

/* Sort recipes according to the property of the recipe ascending or
 * descending
 */
const sortRecipes = (recipes: any[], recipeProperty: string, order: string) => {
    // A shallow copy is enough — sort() needs a fresh array because it
    // mutates in place, but it does not touch the recipe objects
    // themselves. Deep-cloning via JSON round-trip allocates millions of
    // strings/objects on libraries with tens of thousands of recipes for
    // no benefit.
    const rec = recipes.slice();
    return rec.sort((r1: any, r2: any) => {
        if (order !== 'ascending' && order !== 'descending') return 0;
        if (order === 'ascending') {
            if (
                recipeProperty === 'dateCreated' ||
                recipeProperty === 'dateModified'
            ) {
                return (
                    new Date(r1[recipeProperty]).getTime() -
                    new Date(r2[recipeProperty]).getTime()
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
            return (
                new Date(r2[recipeProperty]).getTime() -
                new Date(r1[recipeProperty]).getTime()
            );
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

function mapFilterTypeToOperator(useAndType: boolean) {
    return useAndType ? new AndOperator() : new OrOperator();
}

// ===================
// Computed properties
// ===================

const filterObjects = computed(() => ({
    categories: new CategoriesFilter(
        filterCategories.value,
        mapFilterTypeToOperator(filterCategoriesTypeUseAnd.value),
    ),
    keywords: new KeywordsFilter(
        filterKeywords.value,
        mapFilterTypeToOperator(filterKeywordsTypeUseAnd.value),
        true,
    ),
    searchTerm: searchTerm.value,
}));

/**
 * An array of the filtered recipes, with all filters applied.
 */
const filteredRecipes = computed(() => {
    const recipeFilters = [
        filterObjects.value.categories,
        filterObjects.value.keywords,
        new NamesFilter(
            filterObjects.value.searchTerm,
            new AndOperator(),
            'fuzzy',
        ),
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
    const filteredIds = new Set(filteredRecipes.value.map((r) => r.recipe_id));
    function makeObject(rec: any) {
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

// What the virtual scroller renders: the sorted recipes the filters let
// through. EmptyList stays gated on recipeObjects (all recipes), so a filter
// matching nothing keeps the filter controls on screen.
const visibleRecipes = computed(() =>
    recipeObjects.value.filter((o) => o.show).map((o) => o.recipe),
);

const showFiltersInRecipeList = computed(
    () => legacyStore.localSettings.showFiltersInRecipeList,
);
</script>

<script lang="ts">
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
</style>
