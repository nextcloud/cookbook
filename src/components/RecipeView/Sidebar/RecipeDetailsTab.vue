<template>
    <NcAppSidebarTab id="details" :name="t('cookbook', 'Details')" :order="0">
        <template #icon>
            <DetailsIcon :size="20" />
        </template>

        <section class="organizational">
            <h3 class="mt-0">{{ t('cookbook', 'Organizational') }}</h3>
            <div class="section-content mb-0.5">
                <span class="label">{{ t('cookbook', 'Category') }}:</span
                ><RecipeCategory
                    v-if="recipe?.recipeCategory"
                    class="inline-block ml-3"
                    :name="recipe.recipeCategory"
                    @category-clicked="categoryClicked(recipe.recipeCategory)"
                />
                <span v-else class="ml-3 italic">
                    {{ t('cookbook', 'Uncategorized') }}</span
                >
            </div>
            <div
                v-if="recipe?.keywords"
                class="section-content mb-0.5 flex flex-wrap"
            >
                <span class="mb-1 label">{{ t('cookbook', 'Keywords') }}:</span>
                <RecipeKeywords
                    v-if="recipe.keywords.length > 0"
                    :keywords="recipe.keywords"
                    class="inline-block ml-3"
                />
                <span v-else class="ml-3 italic">
                    {{ t('cookbook', 'No keyword assigned') }}</span
                >
            </div>
        </section>

        <section v-if="isMetadataShown" class="metadata">
            <h3 class="mt-0">{{ t('cookbook', 'Metadata') }}</h3>
            <div class="section-content mb-4">
                <RecipeDates
                    :date-created="recipe.dateCreated"
                    :date-modified="recipe.dateModified"
                />
            </div>
        </section>

        <section v-if="showNutritionData">
            <div class="nutrition p-4 rounded">
                <h3 class="mt-0">
                    {{ t('cookbook', 'Nutrition Information') }}
                </h3>
                <RecipeNutritionInformation :nutrition="recipe.nutrition" />
            </div>
        </section>
    </NcAppSidebarTab>
</template>

<script setup>
import { computed } from 'vue';
import NcAppSidebarTab from '@nextcloud/vue/dist/Components/NcAppSidebarTab.js';
import DetailsIcon from 'icons/InformationOutline.vue';
import RecipeNutritionInformation from 'cookbook/components/RecipeView/NutritionInformation/RecipeNutritionInformation.vue';
import { useRouter } from 'vue-router/composables';
import { useStore } from 'cookbook/store';
import RecipeCategory from 'cookbook/components/RecipeView/RecipeCategory.vue';
import RecipeDates from 'cookbook/components/RecipeView/RecipeDates.vue';
import RecipeKeywords from 'cookbook/components/RecipeView/RecipeKeywords.vue';

const router = useRouter();
const store = useStore();

// ===================
// Computed properties
// ===================
const visibleInfoBlocks = computed(
    () => store.state.config?.visibleInfoBlocks ?? {},
);

const recipe = computed(() => store.state.recipe);

const isMetadataShown = computed(
    () => recipe.value?.dateCreated || recipe.value?.dateModified,
);

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

// ===================
// Methods
// ===================
/**
 * Callback for click on a category.
 * @param category Category that has been clicked.
 */
function categoryClicked(category) {
    if (category) {
        router.push(`/category/${category}`);
    }
}
</script>

<style lang="scss">
section {
    padding-right: 10px;
    padding-left: 10px;
    margin-bottom: 1.5rem;

    h3 {
        margin-bottom: 6px;
        font-weight: bolder;
    }

    .section-content {
        margin-left: 1rem;
    }
}

.label {
    color: var(--color-text-lighter);
}

.nutrition {
    max-width: 380px;

    background-color: var(--color-background-hover);
}
</style>
