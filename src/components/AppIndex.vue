<template>
    <RecipeList :recipes="recipes" :loading="isLoadingRecipeList" />
</template>

<script setup>
import api from 'cookbook/js/api-interface';
import {
    computed,
    getCurrentInstance,
    markRaw,
    onMounted,
    ref,
    shallowRef,
    watch,
} from 'vue';

import RecipeList from './List/RecipeList.vue';
import { useLegacyStore } from '../store';

const legacyStore = useLegacyStore();

/**
 * The known recipes in the cookbook.
 *
 * shallowRef + markRaw: ref() recursively wraps every nested object in a
 * reactive Proxy. For libraries with tens of thousands of recipes that is
 * tens of thousands of Proxy allocations on first paint and freezes the
 * browser for several seconds. The list view never mutates individual
 * recipe fields, only replaces the whole array, so shallow reactivity is
 * sufficient.
 *
 * @type {import('vue').ShallowRef<Array>}
 */
const recipes = shallowRef([]);

/**
 * If the list of recipes is currently being fetched from the server.
 * @type {import('vue').Ref<boolean>}
 */
const isLoadingRecipeList = ref(false);

/**
 * Is the Cookbook recipe directory currently being changed?
 */
const updatingRecipeDirectory = computed(
    () => legacyStore.updatingRecipeDirectory,
);

// Methods
/**
 * Load all recipes from the database
 */
const loadAll = () => {
    isLoadingRecipeList.value = true;
    api.recipes
        .getAll()
        .then((response) => {
            recipes.value = markRaw(response.data);

            // Always set page name last
            legacyStore.setPage({ page: 'index' });
        })
        .catch(() => {
            // Always set page name last
            legacyStore.setPage({ page: 'index' });
        })
        .finally(() => {
            isLoadingRecipeList.value = false;
        });
};

// Watchers
/**
 * If the Cookbook recipe directory currently was changed, reload
 * the recipes in the index component.
 */
watch(updatingRecipeDirectory, async (newVal, oldVal) => {
    if (newVal === false && newVal !== oldVal) {
        loadAll();
    }
});

// Vue lifecycle
onMounted(() => {
    getCurrentInstance().proxy.$log.info('AppIndex mounted');
    loadAll();
});
</script>

<script>
export default {
    name: 'AppIndex',
};
</script>
