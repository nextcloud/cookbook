<template>
    <div>
        <RecipeList
            :recipes="results"
            :loading="isLoadingRecipeList"
            :preapplied-filters="filters"
        />
    </div>
</template>

<script setup>
import {
    markRaw,
    onActivated,
    onDeactivated,
    onMounted,
    ref,
    shallowRef,
} from 'vue';
import { onBeforeRouteUpdate, useRoute } from 'vue-router/composables';
import api from 'cookbook/js/api-interface';
import helpers from 'cookbook/js/helper';
import { showSimpleAlertModal } from 'cookbook/js/modals';
import { RecipeCategoriesFilter as CategoriesFilter } from '../js/RecipeFilters';

import RecipeList from './List/RecipeList.vue';
import { useLegacyStore } from '../store';
import emitter from '../bus';

const route = useRoute();
const legacyStore = useLegacyStore();

// Props
const props = defineProps({
    query: {
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
 * shallowRef + markRaw: avoid wrapping every recipe in a reactive Proxy on
 * libraries with tens of thousands of entries. See AppIndex.vue for the
 * full rationale.
 *
 * @type {import('vue').ShallowRef<Array>}
 */
const results = shallowRef([]);
/**
 * List of filters that are pre-applied to the list. This can be used to hide filters from the selection since they are
 * already applied.
 * @type {import('vue').Ref<Array>}
 */
const filters = ref([]);

// watch(route, (to, from) => {
//     keywordFilter.value = [];
// });

// Methods
const setup = async () => {
    // TODO: This is a mess of different implementation styles, needs cleanup
    if (props.query === 'name') {
        // Search by name
        // TODO
    } else if (props.query === 'tags') {
        // Search by tags
        const tags = route.params.value;
        try {
            isLoadingRecipeList.value = true;
            const response = await api.recipes.allWithTag(tags);
            results.value = markRaw(response.data);
        } catch (e) {
            results.value = [];
            await showSimpleAlertModal(
                /* prettier-ignore */
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
        // Search by category
        const cat = route.params.value;
        filters.value = [new CategoriesFilter(cat)];
        try {
            isLoadingRecipeList.value = true;
            const response = await api.recipes.allInCategory(cat);
            results.value = markRaw(response.data);
        } catch (e) {
            results.value = [];
            await showSimpleAlertModal(
                /* prettier-ignore */
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
    } else {
        // General search
        try {
            isLoadingRecipeList.value = true;
            const response = await api.recipes.search(route.params.value);
            results.value = markRaw(response.data);
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
        legacyStore.setPage({ page: 'search' });
    }
    legacyStore.setPage({ page: 'search' });
};

// Lifecycle hooks
onBeforeRouteUpdate((to, from, next) => {
    // Move to next route as expected
    next();
    // Reload view
    setup();
});

onMounted(() => {
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
