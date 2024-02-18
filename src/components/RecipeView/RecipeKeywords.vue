<template>
    <ul>
        <RecipeKeyword
            v-for="(keyword, idx) in keywords"
            :key="`keyword-${idx}`"
            :name="keyword"
            :title="
                // prettier-ignore
                t('cookbook','Search recipes with this keyword')
            "
            @keyword-clicked="keywordClicked(keyword)"
        />
    </ul>
</template>

<script setup>
import RecipeKeyword from 'cookbook/components/RecipeKeyword.vue';
import { useRouter } from 'vue-router/composables';

const router = useRouter();

defineProps({
    keywords: {
        type: Array,
        default: () => [],
    },
});

// ===================
// Methods
// ===================

/**
 * Callback for click on a keyword.
 * @param keyword Keyword that has been clicked.
 */
function keywordClicked(keyword) {
    if (keyword) {
        router.push(`/tags/${keyword}`);
    }
}
</script>

<style scoped lang="scss"></style>
