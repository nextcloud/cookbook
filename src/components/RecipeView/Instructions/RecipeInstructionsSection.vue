<template>
    <li class="instructions-section-root">
        <fieldset v-if="section" class="instructions-section">
            <legend v-if="section.name" class="instructions-section__title">
                {{ section.name }}
            </legend>
            <ul v-if="collectedTools.length > 0" class="tools mb-4">
                <RecipeInstructionsTool
                    v-for="(tool, idx) in collectedTools"
                    :key="`${parentId}_section-${section.name}_tool-${idx}`"
                    :tool="tool"
                />
            </ul>
            <ul
                v-if="collectedSupplies.length > 0"
                class="supplies flex flex-row flex-wrap gap-x-2 mb-4 px-3 py-2 border-2 rounded"
            >
                <RecipeInstructionsIngredient
                    v-for="(supply, idx) in collectedSupplies"
                    :key="`${parentId}_section-${section.name}_supply-${idx}`"
                    :ingredient="supply"
                    :add-comma-separator="idx < collectedSupplies.length - 1"
                />
            </ul>
            <div v-if="section.description" class="mb-4">
                <VueShowdown
                    :markdown="normalizedDescription"
                    class="markdown-instruction"
                />
            </div>
            <!--        TODO Add support for missing properties -->
            <!--        <div>{{ section.timeRequired }}</div>-->
            <!--        <div>{{ section.image }}</div>-->
            <ol
                v-if="
                    section.itemListElement &&
                    section.itemListElement.length > 0
                "
            >
                <component
                    :is="childComponentType(item)"
                    v-for="(item, idx) in section.itemListElement"
                    :key="`${parentId}_section-${item.position ?? ''}-${item['name'] ?? ''}_item-${idx}`"
                    v-bind="childComponentProps(item, idx)"
                />
            </ol>
        </fieldset>
    </li>
</template>

<script setup>
import { computedAsync } from '@vueuse/core';
import normalizeMarkdown from 'cookbook/js/title-rename';
import { computed, getCurrentInstance } from 'vue';
import SupplyCollector from 'cookbook/js/Visitors/SchemaOrg/SupplyCollector';
import ToolsCollector from 'cookbook/js/Visitors/SchemaOrg/ToolsCollector';
import RecipeInstructionsDirection from './RecipeInstructionsDirection.vue';
import RecipeInstructionsIngredient from './RecipeInstructionsIngredient.vue';
import RecipeInstructionsStep from './RecipeInstructionsStep.vue';
import RecipeInstructionsTip from './RecipeInstructionsTip.vue';
import RecipeInstructionsTool from './RecipeInstructionsTool.vue';

const log = getCurrentInstance().proxy.$log;

const props = defineProps({
    /** @type {HowToSection|null} */
    section: {
        type: Object,
        default: null,
    },
    /** @type {string} Identifier of the parent to be used as key. */
    parentId: {
        type: String,
        default: '',
    },
    /** @type {bool} The parent is marked as completed */
    parentIsDone: {
        type: Boolean,
        default: false,
    },
});

// ===================
// Computed properties
// ===================

/** Normalized description property with recipe-reference links. */
const normalizedDescription = computedAsync(
    async () => {
        try {
            return await normalizeMarkdown(props.section.description);
        } catch (e) {
            log.warn(
                `Could not normalize Markdown. Error Message: ${e.message}`,
            );
        }
        return '';
    },
    t('cookbook', 'Loading…'),
);

/** List of all supply items defined in this section's subitems */
const collectedSupplies = computed(() => {
    const collector = new SupplyCollector();
    collector.visitHowToSection(props.section);
    return collector.getSupplies();
});

/** List of all tool items defined in this section's subitems */
const collectedTools = computed(() => {
    const collector = new ToolsCollector();
    collector.visitHowToSection(props.section);
    return collector.getTools();
});

// ===================
// Methods
// ===================

/**
 * Determines the type of component to render as the child list item.
 * @param {HowToDirection|HowToStep|HowToTip} item List item to render.
 */
function childComponentType(item) {
    switch (item['@type']) {
        case 'HowToDirection':
            return RecipeInstructionsDirection;
        case 'HowToStep':
            return RecipeInstructionsStep;
        case 'HowToTip':
            return RecipeInstructionsTip;
        default:
            return '';
    }
}

/**
 * Determines the props to pass for the type of component to render as the child list item.
 * @param {HowToDirection|HowToStep|HowToTip} item List item to render.
 * @param {number} index Index of the item in the sections array.
 */
function childComponentProps(item, index) {
    switch (item['@type']) {
        case 'HowToDirection':
            return { direction: item, parentIsDone: false };
        case 'HowToStep':
            return {
                step: item,
                parentIsDone: false,
                parentId: `${props.parentId.value}_section-${item.position ?? ''}-${item.name ?? ''}_item-${index}`,
            };

        case 'HowToTip':
            return { tip: item, parentIsDone: false };
        default:
            return '';
    }
}
</script>

<style scoped lang="scss">
li.instructions-section-root {
    list-style-type: none;
}

.instructions-section {
    padding: 1.5em 1.8em 2em;
    border: solid 1px var(--color-border);
    border-radius: 5px;
    margin: 1em 0 3em;

    .instructions-section__title {
        padding: 0 0.5em;
        font-size: 1.3em;
    }

    :deep(ol > li:last-child) {
        margin-bottom: 0;
    }
}

ol {
    list-style-type: none;
}

.supplies {
    background-color: var(--color-border);
}
</style>
