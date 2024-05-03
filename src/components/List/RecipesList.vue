<template>
    <NcAppContentList v-if="useRecipesList" class="content-list">
        <div v-if="loading" class="mt-12">
            <RecipesListLoadingSkeleton :delay="800" />
        </div>
        <div v-else>
            <div v-if="showFiltersInRecipeList">
                <RecipeFilterControlsModal
                    v-model="filterControlsValue"
                    :recipes="recipes"
                    :is-loading="loading"
                    :hide-filter-types="hideFilterTypes"
                    class="ml-12 mr-1 mt-2 mb-2"
                    @input="handleFilterControlsValueUpdated"
                />
            </div>
            <ul>
                <RecipesListItem
                    v-for="recipeObj in recipeObjects"
                    v-show="recipeObj.show"
                    :key="recipeObj.recipe.id"
                    :recipe="recipeObj.recipe"
                    :renaming="isRenaming(recipeObj.recipe.id)"
                    @recipe-selected="onRecipeSelected"
                    @start-renaming="onStartRenaming"
                    @recipe-deletion-requested="onDeleteRecipe"
                    @recipe-renamed="(args) => emit('recipe-renamed', args)"
                />
            </ul>
        </div>
    </NcAppContentList>
</template>

<script setup>
import { computed, inject, ref, watch } from 'vue';
import NcAppContentList from '@nextcloud/vue/dist/Components/NcAppContentList.js';
import RecipesListLoadingSkeleton from 'cookbook/components/List/Loading/RecipesListLoadingSkeleton.vue';
import RecipeFilterControlsModal from 'cookbook/components/List/RecipeFilterControlsModal.vue';
import RecipesListItem from 'cookbook/components/List/RecipeListItem.vue';
import ListStyle from 'cookbook/js/Enums/ListStyle';
import useRecipeFiltering from 'cookbook/composables/useRecipeFiltering';

// DI
const store = inject('Store');

const emit = defineEmits([
    'recipe-deletion-requested',
    'recipe-renamed',
    'recipe-selected',
    'filters-updated',
]);

const props = defineProps({
    loading: {
        type: Boolean,
        default: false,
    },
    // from parent
    /**
     * @type {Recipe[]} List of recipes to show
     */
    recipes: {
        type: Array,
        default: () => [],
        required: true,
    },
    hideFilterTypes: {
        type: Array,
        default: () => [],
    },
});

/** Local set of filters that get updated when global filters in store are updated, but may be different from the store
 * filters if non-confirmed local changes are present.
 * @type {import('vue').Ref<UnwrapRef<*[]>>}
 */
const localRecipeFilters = ref([]);

const { orderBy, recipeObjects } = useRecipeFiltering(
    props,
    localRecipeFilters,
);

/**
 * Update local filters if the filters are updated globally.
 */
watch(
    () => store.state.recipeFilters,
    (flt) => {
        localRecipeFilters.value = flt;
    },
);

/**
 * Workaround. Should be replaced by two v-models in vue3
 * @type {import('vue').Ref<object>}
 */
const filterControlsValue = ref();

const showFiltersInRecipeList = computed(
    () => store.state.localSettings.showFiltersInRecipeList,
);

// ===================
// Methods
// ===================

/**
 * If the user has chosen to use the list of recipes.
 * @type {import('vue').ComputedRef<boolean>}
 */
const useRecipesList = computed(
    () => store.state.localSettings.recipesListStyle === ListStyle.List,
);

// Filtering

/**
 * Handle updated value of the inline filter controls.
 */
function handleFilterControlsValueUpdated(value) {
    localRecipeFilters.value = value.filters;
    orderBy.value = value.orderBy;
    emit('filters-updated', localRecipeFilters.value);
}

/**
 * Handles user-requested recipe deletion.
 * @return {void}
 */
function onDeleteRecipe(id) {
    emit('recipe-deletion-requested', id);
}

// =================================================
// Recipe manipulations in the list
// =================================================

const renamingRecipes = ref([]);

function onStartRenaming(recipeId) {
    renamingRecipes.value.push(recipeId);
}

function isRenaming(recipeId) {
    return renamingRecipes.value.includes(recipeId);
}

/**
 * Handles user-selection of a recipe item in the list.
 * @param {string} recipeId - Identifier of the selected recipe.
 */
function onRecipeSelected(recipeId) {
    // Store current filters in the store and URL if not already done
    store.dispatch('setRecipeFilters', localRecipeFilters.value);
    emit('recipe-selected', recipeId);
}
</script>
