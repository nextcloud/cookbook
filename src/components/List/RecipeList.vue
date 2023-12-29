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
                v-model="filterValue"
                :preapplied-filters="props.preappliedFilters"
                :recipes="recipes"
                :is-loading="loading"
                :is-visible="isFilterControlsVisible"
                @close="() => (isFilterControlsVisible = false)"
            />
            <div id="recipes-submenu" class="recipes-submenu-container">
                <NcSelect
                    v-if="recipes.length > 0"
                    v-model="orderBy"
                    class="recipes-sorting-dropdown mr-4"
                    :clearable="false"
                    :multiple="false"
                    :searchable="false"
                    :placeholder="t('cookbook', 'Select order')"
                    :options="recipeOrderingOptions"
                >
                    <template #option="option">
                        <div class="ordering-selection-entry">
                            <TriangleSmallUpIcon
                                v-if="option.iconUp"
                                :size="20"
                            />
                            <TriangleSmallDownIcon
                                v-if="!option.iconUp"
                                :size="20"
                            />
                            <span class="option__title">{{
                                option.label
                            }}</span>
                        </div>
                    </template>
                </NcSelect>
                <NcButton
                    v-if="isMobile"
                    class="copy-ingredients print-hidden"
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
import TriangleSmallUpIcon from 'vue-material-design-icons/TriangleSmallUp.vue';
import TriangleSmallDownIcon from 'vue-material-design-icons/TriangleSmallDown.vue';

import NcButton from '@nextcloud/vue/dist/Components/NcButton.js';
import NcSelect from '@nextcloud/vue/dist/Components/NcSelect.js';
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
const filterValue = ref({ categories: [], keywords: [] });

/**
 * @type {import('vue').Ref<Array>}
 */
const recipeOrderingOptions = ref([
    {
        label: t('cookbook', 'Name'),
        iconUp: true,
        recipeProperty: 'name',
        order: 'ascending',
    },
    {
        label: t('cookbook', 'Name'),
        iconUp: false,
        recipeProperty: 'name',
        order: 'descending',
    },
    {
        label: t('cookbook', 'Creation date'),
        iconUp: true,
        recipeProperty: 'dateCreated',
        order: 'ascending',
    },
    {
        label: t('cookbook', 'Creation date'),
        iconUp: false,
        recipeProperty: 'dateCreated',
        order: 'descending',
    },
    {
        label: t('cookbook', 'Modification date'),
        iconUp: true,
        recipeProperty: 'dateModified',
        order: 'ascending',
    },
    {
        label: t('cookbook', 'Modification date'),
        iconUp: false,
        recipeProperty: 'dateModified',
        order: 'descending',
    },
]);
const orderBy = ref(recipeOrderingOptions.value[0]);

onMounted(() => {
    store.dispatch('clearRecipeFilters');
});

// ===================
// Methods
// ===================

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
        new CategoriesFilter(filterValue.value.categories),
        new KeywordsFilter(filterValue.value.keywords, new AndOperator(), true),
        new NamesFilter(store.state.recipeFilters),
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

.ordering-item-icon {
    margin-right: 0.5em;
}

.recipes {
    display: flex;
    width: 100%;
    flex-direction: row;
    flex-wrap: wrap;
}

.p-4 {
    padding: 1.5rem !important;
}

.ordering-selection-entry {
    display: flex;
    align-items: baseline;
}
</style>
