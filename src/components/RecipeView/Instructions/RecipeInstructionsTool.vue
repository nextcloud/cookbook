<template>
    <li class="tool">
        <span v-if="tool.requiredQuantity"
            ><RecipeQuantity :quantity="tool.requiredQuantity" />&nbsp;</span
        ><VueShowdown
            v-if="normalizedName"
            :markdown="normalizedName"
            tag="span"
            class="markdown-tool inline-block"
        />
        <NcPopover
            v-if="hasDescription"
            :focus-trap="false"
            class="popover inline-flex align-items-center ml-1"
        >
            <template #trigger>
                <span
                    v-if="hasDescription"
                    class="icon-container inline-flex align-items-center"
                >
                    <InfoIcon
                        :size="16"
                        class="icon inline-flex align-self-center hover:cursor-pointer"
                    />
                </span>
            </template>
            <template #default>
                <VueShowdown
                    :markdown="normalizedDescription"
                    tag="span"
                    class="markdown-tool__description inline-block px-3 py-2"
                />
            </template>
        </NcPopover>

        <!--        <span v-if="normalizedDescription"-->
        <!--            >,-->
        <!--            <VueShowdown-->
        <!--                :markdown="normalizedDescription"-->
        <!--                tag="span"-->
        <!--                class="markdown-tool__description inline-block"-->
        <!--        /></span>-->
    </li>
</template>

<script setup>
import { computed, getCurrentInstance } from 'vue';
import { computedAsync } from '@vueuse/core';
import normalizeMarkdown from 'cookbook/js/title-rename';
import RecipeQuantity from 'cookbook/components/RecipeView/RecipeQuantity.vue';
import InfoIcon from 'icons/InformationVariant.vue';
import NcPopover from '@nextcloud/vue/dist/Components/NcPopover.js';

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

/**
 * true, if the `HowToTool` description has a non-null, -empty, or undefined value.
 */
const hasDescription = computed(
    () => props.tool.description && props.tool.description.trim() !== '',
);

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
li.tool {
    display: inline-flex;
    padding: 0 0.5em;
    border: 1px solid var(--color-border-dark);
    border-radius: var(--border-radius-pill);
    margin-right: 0.3em;
    margin-bottom: 0.3em;

    &:has(.popover) {
        padding-right: 4px;
    }

    /* prevent text selection - doesn't look good */
    user-select: none; /* Standard */
}

.markdown-tool {
    &:deep(a) {
        text-decoration: underline;
    }
}

.icon {
    border-radius: 9999px;
    background-color: var(--color-border-dark);
}

.markdown-tool__description {
    font-size: 0.8em;
}
</style>
