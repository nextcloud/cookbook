<template>
    <NcEmptyContent
        v-if="!isCategorySelected"
        class="empty-recipe-list opacity-transition"
        :class="delayedDisplay.isVisible.value ? '' : 'hidden'"
    >
        <template #description>
            <div class="center p-4">
                <div>
                    {{ t('cookbook', 'No recipes created or imported.') }}
                </div>
                <div>
                    {{
                        // prettier-ignore
                        t('cookbook', 'To get started, you may use the text box in the left navigation bar to import a new recipe. Click below to create a recipe from scratch.',)
                    }}
                </div>
            </div>
        </template>
        <template #icon>
            <RecipeIcon />
        </template>
        <template #name>
            <h1 class="empty-content__name">
                {{ t('cookbook', 'No recipes') }}
            </h1>
        </template>
        <template #action>
            <router-link :to="'/recipe/create'">
                <NcButton type="primary">{{
                    t('cookbook', 'Create new recipe!')
                }}</NcButton>
            </router-link>
        </template>
    </NcEmptyContent>
</template>

<script setup>
import { computed } from 'vue';
import RecipeIcon from 'vue-material-design-icons/ChefHat.vue';
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js';
import NcEmptyContent from '@nextcloud/vue/dist/Components/NcEmptyContent.js';
import { useRoute } from 'vue-router/composables';
import useDelayedDisplay, {
    DelayedDisplayProps,
} from '../../composables/useDelayedDisplay';

const route = useRoute();

const props = defineProps({
    ...DelayedDisplayProps,
});

const delayedDisplay = useDelayedDisplay(props.delay);

// Computed properties
/**
 * True, if the recipe list is shown for a selected category or the 'undefined' category and not "All recipes".
 */
const isCategorySelected = computed(
    () => route.name.substring(1, 9) === 'category',
);
</script>
<script>
export default {
    name: 'RecipeList',
};
</script>

<style lang="scss" scoped>
.opacity-transition {
    transition: opacity 0.25s ease-in-out;
}

.empty-recipe-list {
    padding: 20px;

    :deep(.empty-content__action) {
        margin-top: 24px;
    }
}

.hidden {
    opacity: 0;
}
</style>
