<template>
    <NcAppSidebar
        v-if="showSidebar"
        :name="
            recipe !== null ? recipe.name : t('cookbook', 'No recipe selected')
        "
        @close="hideSidebar"
    >
        <RecipeDetailsTab />
    </NcAppSidebar>
</template>

<script setup>
import NcAppSidebar from '@nextcloud/vue/dist/Components/NcAppSidebar.js';
import RecipeDetailsTab from 'cookbook/components/RecipeView/Sidebar/RecipeDetailsTab.vue';
import { computed } from 'vue';
import { useStore } from 'cookbook/store';

const store = useStore();

/**
 * The recipe data
 * @type {import('vue').ComputedRef<import('cookbook/js/Models/schema/Recipe').Recipe>}
 */
const recipe = computed(() => store.state.recipe);

/**
 * If the recipe-details sidebar should be displayed.
 * @type {import('vue').ComputedRef<boolean>}
 */
const showSidebar = computed(() => store.state.isRecipeSidebarVisible);

/**
 * Hide the recipe-details sidebar.
 */
function hideSidebar() {
    store.dispatch('setRecipeSidebarVisible', false);
}
</script>
