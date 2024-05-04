<template>
    <RecipesList
        v-if="useRecipesList"
        :recipes="results"
        :loading="isLoadingRecipeList"
        :hide-filter-types="hiddenFilterTypes"
        @filters-updated="onFiltersUpdated"
        @recipe-deletion-requested="onDeleteRecipe"
        @recipe-renamed="onRecipeRenamed"
    />
    <RecipesGrid
        v-else
        :recipes="results"
        :loading="isLoadingRecipeList"
        :hide-filter-types="hiddenFilterTypes"
        @filters-updated="onFiltersUpdated"
    />
</template>

<script setup>
import {
    computed,
    inject,
    onActivated,
    onDeactivated,
    onMounted,
    provide,
    ref,
    watch,
} from 'vue';
import { showError } from '@nextcloud/dialogs';
import { useRoute, useRouter } from 'vue-router/composables';
import RecipesGrid from 'cookbook/components/List/RecipesGrid.vue';
import RecipesList from 'cookbook/components/List/RecipesList.vue';
import api from 'cookbook/js/utils/api-interface';
import helpers from 'cookbook/js/helper';
import { showSimpleAlertModal } from 'cookbook/js/modals';
import ListStyle from 'cookbook/js/Enums/ListStyle';
import FilterType from 'cookbook/js/Enums/FilterType';
import parseSearchString from 'cookbook/js/utils/parseSearchString';
import emitter from 'cookbook/bus';

const route = useRoute();
const router = useRouter();

// DI
const recipeRepository = inject('RecipeRepository');
const store = inject('Store');

const recipesLinkBasePath = computed(() => {
    const cleanedValue = encodeURI(props.value.replaceAll('"', '').trim());
    switch (props.query) {
        case 'cat':
            return `/category/${cleanedValue}/`;
        case 'general':
            return `/search/${cleanedValue}/`;
        case 'name':
            return `/name/${cleanedValue}/`;
        case 'tags':
            return `/tags/${cleanedValue}/`;
        case 'index':
            return '/recipe/';
        default:
            return '/recipe/';
    }
});

// Pass this down to child components
provide('recipes-link-base-path', recipesLinkBasePath);

// Props, provided by the router
const props = defineProps({
    query: {
        type: String,
        default: undefined,
    },
    searchQuery: {
        type: String,
        default: '',
    },
    value: {
        type: String,
        default: '',
    },
});

// Reactive properties
/**
 * @type {import('vue').Ref<boolean>}
 */
const isComponentActive = ref(true);
/**
 * If the list of recipes is currently being fetched from the server.
 * @type {import('vue').Ref<boolean>}
 */
const isLoadingRecipeList = ref(false);
/**
 * List of recipes.
 * @type {import('vue').Ref<Recipe[]>}
 */
const results = ref([]);
/**
 * List of filters that are pre-applied to the list. This can be used to hide filters from the selection since they are
 * already applied.
 * @type {import('vue').Ref<RecipeFilter[]>}
 */
const filters = ref([]);

/**
 * Types of filter that should be hidden from the list of recipe-filters UI.
 * @type {import('vue').Ref<UnwrapRef<FilterType[]>>}
 */
const hiddenFilterTypes = ref([]);

/**
 * Creates a deep copy of the params, path, and query value of the route.
 * @param {Route} route - Route of which to clone the values.
 * @return {{path: any, query: any, params: any}}
 */
function cloneRouteValues(route) {
    return {
        params: JSON.parse(JSON.stringify(route.params)),
        path: JSON.parse(JSON.stringify(route.path)),
        query: JSON.parse(JSON.stringify(route.query)),
    };
}

/**
 * Data of previous route for watching route changes
 * @type {import('vue').Ref<UnwrapRef<{path: string, query: Dictionary<string | (string | null)[]>, params: Dictionary<string>}>>}
 */
const previousRoute = ref(cloneRouteValues(route));

const useRecipesList = computed(
    () => store.state.localSettings.recipesListStyle === ListStyle.List,
);

// Methods
const setup = async () => {
    // TODO: This is a mess of different implementation styles, needs cleanup
    if (props.query === 'name') {
        // Search by name
        // TODO
    } else if (props.query === 'tags') {
        hiddenFilterTypes.value = [FilterType.KeywordsFilter];

        // Search by tags
        const tags = route.params.value;

        // List of filters defined by the query params
        let queryFilters = props.searchQuery
            ? parseSearchString(props.searchQuery)
            : [];

        // Remove category filters since they make no sense on this route
        queryFilters = queryFilters.filter(
            (f) => f.type !== FilterType.KeywordsFilter,
        );

        // Update filters in store
        await store.dispatch('setRecipeFilters', queryFilters);

        try {
            isLoadingRecipeList.value = true;
            results.value = await recipeRepository.getRecipesByTag(tags);
        } catch (e) {
            results.value = [];
            await showSimpleAlertModal(
                // prettier-ignore
                t('cookbook', 'Failed to load recipes with keywords: {tags}',
                    {
                        tags,
                    }
                ),
            );
            if (e && e instanceof Error) {
                throw e;
            }
        } finally {
            isLoadingRecipeList.value = false;
        }
    } else if (props.query === 'cat') {
        hiddenFilterTypes.value = [FilterType.CategoriesFilter];
        // Search by category
        const cat = route.params.value;

        // List of filters defined by the query params
        let queryFilters = props.searchQuery
            ? parseSearchString(props.searchQuery)
            : [];

        // Remove category filters since they make no sense on this route
        queryFilters = queryFilters.filter(
            (f) => f.type !== FilterType.CategoriesFilter,
        );

        // Update filters in store
        await store.dispatch('setRecipeFilters', queryFilters);

        try {
            isLoadingRecipeList.value = true;
            results.value = await recipeRepository.getRecipesByCategory(cat);
        } catch (e) {
            results.value = [];
            await showSimpleAlertModal(
                // prettier-ignore
                t('cookbook', 'Failed to load category {category} recipes',
                    {
                        category: cat,
                    }
                ),
            );
            if (e && e instanceof Error) {
                throw e;
            }
        } finally {
            isLoadingRecipeList.value = false;
        }
    } else if (props.query === 'index') {
        // No filters need to be hidden, since all recipes are listed
        hiddenFilterTypes.value = [];

        // List of filters defined by the query params
        const queryFilters = props.searchQuery
            ? parseSearchString(props.searchQuery)
            : [];

        // Update filters in store
        await store.dispatch('setRecipeFilters', queryFilters);

        try {
            isLoadingRecipeList.value = true;
            results.value = await recipeRepository.getRecipes();
        } catch (e) {
            results.value = [];
            await showSimpleAlertModal(
                // prettier-ignore
                t('cookbook', 'Failed to load all recipes'),
            );
            if (e && e instanceof Error) {
                throw e;
            }
        } finally {
            isLoadingRecipeList.value = false;
        }
    } else {
        // General search
        // TODO Update
        try {
            isLoadingRecipeList.value = true;
            const response = await api.recipes.search(route.params.value);
            results.value = response.data;
        } catch (e) {
            results.value = [];
            await showSimpleAlertModal(
                t('cookbook', 'Failed to load search results'),
            );
            if (e && e instanceof Error) {
                throw e;
            }
        } finally {
            isLoadingRecipeList.value = false;
        }
        store.dispatch('setPage', { page: 'search' });
    }
    store.dispatch('setPage', { page: 'search' });
};

watch(
    () => store.state.recipeFilters,
    (filters) => {
        const query = filters.map((f) => f.toSearchString()).join(' ');

        if (store.state.recipeFilters.length > 0) {
            if (props.searchQuery !== query) {
                router.push({
                    path: route.path,
                    query: { q: encodeURI(query) },
                });
            }
        }
        // Push route without query params only if there are query params in the route
        else if (Object.keys(route.query).length !== 0) {
            router.push({
                path: route.path,
                query: undefined,
            });
        }
    },
);

function onFiltersUpdated(filters) {
    store.dispatch('setRecipeFilters', filters);
}

function onRouteUpdated() {
    // Clear search when, e.g., category changes
    if (
        JSON.stringify(route.params) !==
        JSON.stringify(previousRoute.value?.params)
    ) {
        store.dispatch('clearRecipeFilters');
        filters.value = [];
    }

    // Clear search when, e.g., category changes
    const prevRouteParts = previousRoute.value.path.split('/');
    const routeParts = route.path.split('/');
    if (
        // Change from, e.g., /#/category to /#/tags
        (routeParts[1] !== prevRouteParts[1] ||
            // Change from, e.g., /#/category/Dinner to /#/category/Soup
            // but not from, e.g., /#/recipe/1 to /#/recipe/2
            (routeParts[1] !== 'recipe' &&
                routeParts[2] !== prevRouteParts[2])) &&
        Object.keys(route.query).length !== 0
    ) {
        store.dispatch('clearRecipeFilters');
        filters.value = [];

        // Remove query params
        router.push({
            path: route.path,
            params: route.params,
            query: null,
        });
        return;
    }

    // Reload view
    if (route.path !== previousRoute.value?.path) {
        setup();
    }

    previousRoute.value = cloneRouteValues(route);
}

/**
 * Handles requested deletion of recipe with identifier `id`.
 * @param {string} id - Identifier of the recipe to be deleted.
 * @return {Promise<void>}
 */
async function onDeleteRecipe(id) {
    try {
        // Ensure that recipe is still there
        await recipeRepository.getRecipeById(id);

        recipeRepository.deleteRecipe(id).then(() => {
            const recipeIdx = results.value.findIndex(
                (recipe) => recipe.id === id,
            );
            if (recipeIdx !== -1) {
                results.value.splice(recipeIdx, 1);
            }
        });
    } catch (e) {
        showError(t('cookbook', 'Error during preparing recipe for deletion.'));
    }
}

/**
 * Handle recipe that has been renamed by a child.
 * @param args
 */
function onRecipeRenamed(args) {
    const { id, name } = args;
    const idx = results.value.findIndex((r) => r.identifier === id);
    if (idx !== -1) {
        results.value[idx].name = name;
    }
}

// Lifecycle hooks
watch(() => route.params, onRouteUpdated);
watch(() => route.path, onRouteUpdated);
watch(() => route.query, onRouteUpdated);

onMounted(() => {
    previousRoute.value = cloneRouteValues(route);
    setup();
    emitter.off('categoryRenamed');
    emitter.on('categoryRenamed', (val) => {
        if (
            // eslint-disable-next-line no-underscore-dangle
            isComponentActive.value &&
            props.query === 'cat' &&
            route.params.value === val[1]
        ) {
            helpers.goTo(`/category/${val[0]}`);
        }
    });
});

onActivated(() => {
    isComponentActive.value = true;
});

onDeactivated(() => {
    isComponentActive.value = false;
});
</script>

<script>
export default {
    name: 'SearchResults',
};
</script>
