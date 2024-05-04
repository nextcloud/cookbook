<template>
    <div class="pt-2 px-4 md:px-8">
        <div v-if="loading" class="loading-indicator">
            <LoadingIndicator :delay="800" :size="40" />
        </div>
        <div v-else>
            <div v-if="recipeObjects.length === 0">
                <EmptyList />
            </div>
            <div v-else style="margin-bottom: 30vh">
                <div v-if="showFiltersInRecipeList">
                    <RecipeFilterControlsModal
                        v-if="isMobile"
                        v-model="filterControlsValue"
                        class="mt-2 mb-4 -mr-1"
                        :recipes="recipes"
                        :is-loading="loading"
                        :hide-filter-types="hideFilterTypes"
                        @input="handleFilterControlsValueUpdated"
                    />
                    <RecipeFilterControlsInline
                        v-else
                        v-model="filterControlsValue"
                        :recipes="recipes"
                        :is-loading="loading"
                        :hide-filter-types="hideFilterTypes"
                        @input="handleFilterControlsValueUpdated"
                    />
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
    </div>
</template>

<script setup>
import { computed, inject, ref, watch } from 'vue';
import useRecipeFiltering from 'cookbook/composables/useRecipeFiltering';
import { useIsMobile } from 'cookbook/composables/useIsMobile';
import LoadingIndicator from 'cookbook/components/Utilities/LoadingIndicator.vue';
import EmptyList from './EmptyGrid.vue';
import RecipeCard from './RecipeCard.vue';
import RecipeFilterControlsInline from './RecipeFilterControlsInline.vue';
import RecipeFilterControlsModal from './RecipeFilterControlsModal.vue';

// DI
const store = inject('Store');

/** Local set of filters that get updated when global filters in store are updated, but may be different from the store
 * filters if non-confirmed local changes are present.
 * @type {import('vue').Ref<UnwrapRef<*[]>>}
 */
const localRecipeFilters = ref([]);

const isMobile = useIsMobile();

const emit = defineEmits(['filters-updated']);

const props = defineProps({
    loading: {
        type: Boolean,
        default: false,
    },
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

const { orderBy, recipeObjects } = useRecipeFiltering(
    props,
    localRecipeFilters,
);

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

// ===================
// Methods
// ===================

/**
 * Handle updated value of the inline filter controls.
 */
function handleFilterControlsValueUpdated(value) {
    localRecipeFilters.value = value.filters;
    orderBy.value = value.orderBy;
    emit('filters-updated', localRecipeFilters.value);
}

/**
 * If filter UI should be displayed.
 * @type {import('vue').ComputedRef<boolean>}
 */
const showFiltersInRecipeList = computed(
    () => store.state.localSettings.showFiltersInRecipeList,
);
</script>

<script>
export default {
    name: 'RecipesGrid',
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
.loading-indicator {
    display: flex;
    justify-content: center;
    padding: 3rem 0;
}

.recipes {
    display: flex;
    width: 100%;
    flex-direction: row;
    flex-wrap: wrap;
    column-gap: 1.5rem;
    row-gap: 1.5rem;

    li {
        max-width: 350px;
        flex: 1 1 auto;
    }
}
</style>
