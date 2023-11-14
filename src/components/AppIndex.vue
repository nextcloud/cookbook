<template>
    <recipe-list :recipes="recipes" />
</template>

<script>
export default {
    name: 'Location',
}
</script>
<script setup>

import api from "cookbook/js/api-interface";
import { computed, getCurrentInstance, onMounted, ref, watch } from 'vue';

import RecipeList from "./List/RecipeList.vue";
import { useStore } from '../store';

let store = useStore();

// The known recipes in the cookbook
const recipes = ref([]);

/**
 * Is the Cookbook recipe directory currently being changed?
 */
const updatingRecipeDirectory = computed(() => {
    return store.state.updatingRecipeDirectory
});

/**
 * If the Cookbook recipe directory currently was changed, reload
 * the recipes in the index component.
 */
watch(updatingRecipeDirectory, async (newVal, oldVal) => {
    if (newVal === false && newVal !== oldVal) {
        this.loadAll();
    }
});

onMounted(() => {
    getCurrentInstance().proxy.$log.info("AppIndex mounted");
    loadAll();
})

/**
 * Load all recipes from the database
 */
const loadAll = () =>  {
    api.recipes
        .getAll()
        .then((response) => {
            recipes.value = response.data

            // Always set page name last
            store.dispatch("setPage", { page: "index" })
        })
        .catch(() => {
            // Always set page name last
            store.dispatch("setPage", { page: "index" })
        });
};
</script>
