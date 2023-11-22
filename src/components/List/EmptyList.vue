<template>
    <NcEmptyContent
        v-if="!isCategorySelected"
        class="empty-recipe-list animated"
        :class="isVisible ? '' : 'hidden'"
    >
        <template #description>
            <div class="center p-4">
                <div>
                    {{ t('cookbook', 'No recipes created or imported.') }}
                </div>
                <div>
                    {{
                        t(
                            'cookbook',
                            'To get started, you may use the text box in the left navigation bar to import a new recipe. Click below to create a recipe from scratch.',
                        )
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
                <NcButton type="primary"> Create new recipe! </NcButton>
            </router-link>
        </template>
    </NcEmptyContent>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import RecipeIcon from 'vue-material-design-icons/ChefHat.vue';
import NcButton from '@nextcloud/vue/dist/Components/NcButton';
import NcEmptyContent from '@nextcloud/vue/dist/Components/NcEmptyContent';
import { useRoute } from 'vue-router/composables';

const route = useRoute();

const props = defineProps({
    /** Delay in milliseconds before the component is displayed */
    delay: {
        type: Number,
        default: 0,
    },
});

const isVisible = ref(false);

// Computed properties
/**
 * True, if the recipe list is shown for a selected category or the 'undefined' category and not "All recipes".
 */
const isCategorySelected = computed(
    () => route.name.substring(1, 9) === 'category',
);

// Vue lifecycle
onMounted(() => {
    window.setTimeout(() => {
        isVisible.value = true;
    }, props.delay);
});
</script>
<script>
export default {
    name: 'RecipeList',
};
</script>

<style scoped>
.animated {
    transition: opacity 0.25s ease-in-out;
}

.empty-recipe-list :deep(.empty-content__action) {
    margin-top: 24px;
}

.hidden {
    opacity: 0;
}
</style>
