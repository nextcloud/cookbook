<template>
    <RecipeList :recipes="recipes" :loading="isLoadingRecipeList" />
</template>

<script setup>
import api from 'cookbook/js/api-interface';
import { computed, getCurrentInstance, onMounted, ref, watch } from 'vue';

import RecipeList from './List/RecipeList.vue';
import { useStore } from '../store';

const store = useStore();

/**
 * The known recipes in the cookbook
 * @type {import('vue').Ref<Array>}
 */
const recipes = ref([]);

/**
 * If the list of recipes is currently being fetched from the server.
 * @type {import('vue').Ref<boolean>}
 */
const isLoadingRecipeList = ref(false);

/**
 * Is the Cookbook recipe directory currently being changed?
 */
const updatingRecipeDirectory = computed(
    () => store.state.updatingRecipeDirectory,
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
            recipes.value = response.data;

            // Always set page name last
            store.dispatch('setPage', { page: 'index' });
        })
        .catch(() => {
            // Always set page name last
            store.dispatch('setPage', { page: 'index' });
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
