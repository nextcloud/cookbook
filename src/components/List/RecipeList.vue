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
                <ul class="recipes">
                    <li
                        v-for="recipeObj in recipeObjects"
                        v-show="recipeObj.show"
                        :key="recipeObj.recipe.recipe_id"
                    >
                        <RecipeCard :recipe="recipeObj.recipe" />
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
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
        default: () => [],
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
    const rec = JSON.parse(JSON.stringify(recipes));
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
    function makeObject(rec: any) {
        return {
            recipe: rec,
            show: filteredRecipes.value
                .map((r: any) => r.recipe_id)
                .includes(rec.recipe_id),
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
