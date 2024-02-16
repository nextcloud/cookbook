<template>
    <li class="ingredient">
        <span v-if="ingredient.requiredQuantity"
            ><RecipeQuantity
                :quantity="ingredient.requiredQuantity"
            />&nbsp;</span
        ><VueShowdown
            v-if="normalizedName"
            :markdown="normalizedName"
            tag="span"
            class="markdown-ingredient inline-block"
        />
        <NcPopover :focus-trap="false" class="inline-flex align-items-center">
            <template #trigger>
                <span
                    v-if="hasDescription"
                    class="icon-container inline-flex align-items-center"
                >
                    <InfoIcon
                        :size="18"
                        class="inline-flex align-self-center hover:cursor-pointer"
                    />
                </span>
            </template>
            <template>
                <VueShowdown
                    :markdown="normalizedDescription"
                    tag="span"
                    class="markdown-ingredient__description inline-block p-2"
                />
            </template>
        </NcPopover>
        <span v-if="addCommaSeparator" :class="hasDescription ? '-ml-1' : ''"
            >,
        </span>

        <!--        <span v-if="normalizedDescription"-->
        <!--            >,-->
        <!--            <VueShowdown-->
        <!--                :markdown="normalizedDescription"-->
        <!--                tag="span"-->
        <!--                class="markdown-ingredient__description inline-block"-->
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
    /** @type {HowToSupply|null} */
    ingredient: {
        type: Object,
        default: () => null,
    },
    /**
     * If a comma and whitespace should be added after the ingredient element.
     * @type {boolean}
     */
    addCommaSeparator: {
        type: Boolean,
        default: false,
    },
});

// ===================
// Computed properties
// ===================

/**
 * true, if the `HowToSupply` has a non-null, -empty, or undefined value.
 */
const hasDescription = computed(
    () =>
        props.ingredient.description &&
        props.ingredient.description.trim() !== '',
);

/** Normalized description property with recipe-reference links. */
const normalizedDescription = computedAsync(
    async () => {
        try {
            return await normalizeMarkdown(props.ingredient.description);
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
            return await normalizeMarkdown(props.ingredient.name);
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
    name: 'RecipeIngredient',
};
</script>

<style scoped>
li.ingredient {
    display: inline-flex;
    align-items: center;
    /* prevent text selection - doesn't look good */
    user-select: none; /* Standard */
}

.markdown-ingredient {
    &:deep(a) {
        text-decoration: underline;
    }
}

.markdown-ingredient__description {
    font-size: 0.8em;
}
</style>
