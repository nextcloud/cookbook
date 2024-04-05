<template>
    <NcAppSidebar
        v-if="isOpen"
        :name="
            recipe !== null ? recipe.name : t('cookbook', 'No recipe selected')
        "
        @close="emit('close')"
    >
        <RecipeViewSidebarLoadingSkeleton
            v-if="store.loadingRecipe"
            :delay="800"
        />
        <RecipeDetailsTab v-else />
    </NcAppSidebar>
</template>

<script setup>
import NcAppSidebar from '@nextcloud/vue/dist/Components/NcAppSidebar.js';
import RecipeDetailsTab from 'cookbook/components/RecipeView/Sidebar/RecipeDetailsTab.vue';
import { computed } from 'vue';
import { useStore } from 'cookbook/store';
import RecipeViewSidebarLoadingSkeleton from './RecipeViewSidebarLoadingSkeleton.vue';

const store = useStore();

const emit = defineEmits(['close']);

defineProps({
    isOpen: {
        type: Boolean,
        default: false,
    },
});

/**
 * The recipe data
 * @type {import('vue').ComputedRef<import('cookbook/js/Models/schema/Recipe').Recipe>}
 */
const recipe = computed(() => store.state.recipe);
</script>
