<template>
    <NcAppSidebarTab id="details" :name="t('cookbook', 'Details')" :order="0">
        <template #icon>
            <DetailsIcon :size="20" />
        </template>
        <div class="recipe-category">
            Category: {{ recipe !== null ? recipe.recipeCategory : '' }}
        </div>
    </NcAppSidebarTab>
</template>

<script setup>
import NcAppSidebarTab from '@nextcloud/vue/dist/Components/NcAppSidebarTab.js';
import DetailsIcon from 'icons/InformationOutline.vue';

defineProps({
    /** @type {Recipe|null} */
    recipe: {
        type: Object,
        default: null,
    },
});
</script>
