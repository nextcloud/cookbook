<template>
    <div class="pt-2">
        <div v-if="loading" class="loading-indicator">
            <LoadingIndicator :delay="800" :size="40" />
        </div>
        <div v-else>
            <div v-if="recipeObjects.length === 0">
                <EmptyList />
            </div>
            <RecipeFilterControlsModal
                v-if="isMobile"
                v-model="filterValue"
                :preapplied-filters="props.preappliedFilters"
                :recipes="recipes"
                :is-loading="loading"
                :is-visible="isFilterControlsVisible"
                @close="() => (isFilterControlsVisible = false)"
            />
            <RecipeFilterControlsInline
                v-else
                v-model="inlineControlsValue"
                :preapplied-filters="props.preappliedFilters"
                :recipes="recipes"
                :is-loading="loading"
                :is-visible="isFilterControlsVisible"
                @input="handleInlineControlsValueUpdated"
                @close="() => (isFilterControlsVisible = false)"
            />
            <div
                v-if="isMobile"
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
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import FilterIcon from 'vue-material-design-icons/FilterVariant.vue';

import NcButton from '@nextcloud/vue/dist/Components/NcButton.js';
import { useIsMobile } from '../../composables/useIsMobile';
import { useStore } from '../../store';
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
const store = useStore();

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

onMounted(() => {
    store.dispatch('clearRecipeFilters');
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
    const rec = JSON.parse(JSON.stringify(recipes));
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
        new NamesFilter(store.state.recipeFilters, new AndOperator(), 'fuzzy'),
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
    function makeObject(rec) {
        return {
            recipe: rec,
            show: filteredRecipes.value
                .map((r) => r.recipe_id)
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
    margin-right: 1rem;
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
