<template>
    <div>
        <recipe-list :recipes="results" />
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { onBeforeRouteUpdate, useRoute } from "vue-router/composables";
import api from 'cookbook/js/api-interface';
import helpers from 'cookbook/js/helper';
import { showSimpleAlertModal } from 'cookbook/js/modals';

import RecipeList from './List/RecipeList.vue';
import {useStore} from '../store';
import emitter from '../bus';

let route = useRoute();
let store = useStore();

defineProps({
    query: {
        type: String,
        default: '',
    },
});

/**
 * @type {import('vue').Ref<Array>}
 */
const results = ref([]);

// watch(route, (to, from) => {
//     keywordFilter.value = [];
// });

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
            !_inactive &&
            props.query === 'cat' &&
            route.params.value === val[1]
        ) {
            helpers.goTo(`/category/${val[0]}`);
        }
    });
});

const setup = async () => {
    // TODO: This is a mess of different implementation styles, needs cleanup
    if (props.query === 'name') {
        // Search by name
        // TODO
    } else if (this.query === 'tags') {
        // Search by tags
        const tags = route.params.value;
        try {
            const response = await api.recipes.allWithTag(tags);
            results.value = response.data;
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
        }
    } else if (props.query === 'cat') {
        // Search by category
        const cat = route.params.value;
        try {
            const response = await api.recipes.allInCategory(cat);
            results.value = response.data;
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
        }
    } else {
        // General search
        try {
            const response = await api.recipes.search(
                route.params.value,
            );
            results.value = response.data;
        } catch (e) {
            results.value = [];
            await showSimpleAlertModal(
                t('cookbook', 'Failed to load search results'),
            );
            if (e && e instanceof Error) {
                throw e;
            }
        }
        store.dispatch('setPage', { page: 'search' });
    }
    store.dispatch('setPage', { page: 'search' });
};
</script>

<script>
export default {
    name: 'SearchResults',
};
</script>
