<template>
    <NcAppSidebarTab id="details" :name="t('cookbook', 'Details')" :order="0">
        <template #icon>
            <DetailsIcon :size="20" />
        </template>
        <section class="organizational">
            <h3 class="mt-0">{{ t('cookbook', 'Organizational') }}</h3>
            <div class="section-content recipe-category mb-4">
                <span>{{ t('cookbook', 'Category') }}:</span>

                <span
                    v-if="recipe?.recipeCategory"
                    class="inline-block border-1 border-solid rounded-full px-2.5 py-0.5"
                    >{{ recipe.recipeCategory }}</span
                >
                <span v-else> {{ 't("cookbook", "Uncategorized")' }}</span>
            </div>
        </section>
        <section v-if="showNutritionData" class="nutrition p-4 rounded">
            <h3 class="mt-0">{{ t('cookbook', 'Nutrition Information') }}</h3>
            <RecipeNutritionInformation :nutrition="recipe.nutrition" />
        </section>
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

<style lang="scss">
section {
    margin-bottom: 1.5rem;

    h3 {
        margin-bottom: 6px;
        font-weight: bolder;
    }

    .section-content {
        margin-left: 1rem;
    }
}
</style>
