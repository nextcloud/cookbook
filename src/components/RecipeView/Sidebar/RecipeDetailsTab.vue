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
import { computed } from 'vue';
import NcAppSidebarTab from '@nextcloud/vue/dist/Components/NcAppSidebarTab.js';
import DetailsIcon from 'icons/InformationOutline.vue';
import RecipeNutritionInformation from 'cookbook/components/RecipeView/NutritionInformation/RecipeNutritionInformation.vue';
import { useStore } from 'cookbook/store';

const store = useStore();

// ===================
// Computed properties
// ===================
const visibleInfoBlocks = computed(
    () => store.state.config?.visibleInfoBlocks ?? {},
);

const recipe = computed(() => store.state.recipe);

/**
 * If nutrition information should be shown depending on available data and user preferences.
 * @type {import('vue').ComputedRef<boolean>}
 */
const showNutritionData = computed(
    () =>
        visibleInfoBlocks.value['nutrition-information'] &&
        recipe?.value?.nutrition &&
        recipe.value.nutrition &&
        recipe.value.nutrition['@type'] === 'NutritionInformation' &&
        !recipe.value.nutrition.isUndefined(),
);
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

.nutrition {
    max-width: 380px;

    background-color: var(--color-background-hover);
}
</style>
