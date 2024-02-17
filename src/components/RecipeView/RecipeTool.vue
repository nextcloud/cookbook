<template>
    <li class="tool">
        <span v-if="tool.requiredQuantity"
            ><RecipeQuantity :quantity="tool.requiredQuantity" />&nbsp;</span
        ><VueShowdown
            v-if="normalizedName"
            :markdown="normalizedName"
            tag="span"
            class="markdown-tool inline-block"
        /><span v-if="normalizedDescription"
            >,
            <VueShowdown
                :markdown="normalizedDescription"
                tag="span"
                class="markdown-tool__description inline-block"
        /></span>
    </li>
</template>

<script setup>
import { computedAsync } from '@vueuse/core';
import normalizeMarkdown from 'cookbook/js/title-rename';
import { getCurrentInstance } from 'vue';
import RecipeQuantity from 'cookbook/components/RecipeView/RecipeQuantity.vue';

const log = getCurrentInstance().proxy.$log;

const props = defineProps({
    /** @type {HowToTool|null} */
    tool: {
        type: Object,
        default: () => null,
    },
});

// ===================
// Computed properties
// ===================

/** Normalized description property with recipe-reference links. */
const normalizedDescription = computedAsync(
    async () => {
        try {
            return await normalizeMarkdown(props.tool.description);
        } catch (e) {
            log.warn(
                `Could not normalize Markdown. Error Message: ${e.message}`,
            );
        }
        return '';
    },
    t('cookbook', 'Loading…'),
);

/** Normalized name property with recipe-reference links. */
const normalizedName = computedAsync(
    async () => {
        try {
            return await normalizeMarkdown(props.tool.name);
        } catch (e) {
            log.warn(
                `Could not normalize Markdown. Error Message: ${e.message}`,
            );
        }
        return '';
    },
    t('cookbook', 'Loading…'),
);
</script>

<script>
export default {
    name: 'RecipeTool',
};
</script>

<style scoped>
.markdown-tool {
    &:deep(a) {
        text-decoration: underline;
    }
}

.markdown-tool__description {
    font-size: 0.8em;
}
</style>
