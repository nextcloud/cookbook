<template>
    <li
        v-if="step"
        class="instructions-step"
        :class="{ done: isDone }"
        @click="toggleDone"
    >
        <div style="display: table; min-height: 32px">
            <div style="display: table-cell; vertical-align: middle">
                <div v-if="step.text" class="instructions-step__text">
                    {{ normalizedText }}
                </div>
                <!--        TODO Add support for missing properties -->
                <!--        <div>{{ step.timeRequired }}</div>-->
                <!--        <div>{{ step.image }}</div>-->
                <ol
                    v-if="
                        step.itemListElement && step.itemListElement.length > 0
                    "
                    class="step-children"
                >
                    <component
                        :is="childComponentType(item)"
                        v-for="(item, idx) in step.itemListElement"
                        :key="`${parentId}_step-${item.position ?? ''}-${item['name'] ?? ''}_item-${idx}`"
                        v-bind="childComponentProps(item)"
                    >
                    </component>
                </ol>
            </div>
        </div>
    </li>
</template>

<script setup>
import { getCurrentInstance, ref, watch } from 'vue';
import { computedAsync } from '@vueuse/core';
import normalizeMarkdown from 'cookbook/js/title-rename';
import RecipeInstructionsDirection from 'cookbook/components/RecipeView/Instructions/RecipeInstructionsDirection.vue';
import RecipeInstructionsTip from 'cookbook/components/RecipeView/Instructions/RecipeInstructionsTip.vue';

const log = getCurrentInstance().proxy.$log;

const props = defineProps({
    /** @type {HowToStep} */
    step: {
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

/**
 * If this step has been marked as completed.
 * @type {import('vue').Ref<boolean>}
 */
const isDone = ref(false);

// ===================
// Computed properties
// ===================

/** Normalized text property with recipe-reference links. */
const normalizedText = computedAsync(async () => {
    try {
        return await normalizeMarkdown(props.step.text);
    } catch (e) {
        log.warn(`Could not normalize Markdown. Error Message: ${e.message}`);
    }
    return '';
}, '');

/** Toggles the completed state on this step. */
function toggleDone() {
    isDone.value = !isDone.value;
}

/**
 * Determines the type of component to render as the child list item.
 * @param {HowToDirection|HowToStep|HowToTip} item List item to render.
 */
function childComponentType(item) {
    switch (item['@type']) {
        case 'HowToDirection':
            return RecipeInstructionsDirection;
        case 'HowToTip':
            return RecipeInstructionsTip;
        default:
            return '';
    }
}

/**
 * Determines the props to pass for the type of component to render as the child list item.
 * @param {HowToDirection|HowToStep|HowToTip} item List item to render.
 */
function childComponentProps(item) {
    switch (item['@type']) {
        case 'HowToDirection':
            return { direction: item, parentIsDone: isDone.value };
        case 'HowToTip':
            return { tip: item, parentIsDone: isDone.value };
        default:
            return '';
    }
}

// ===================
// Watchers
// ===================

watch(
    () => props.parentIsDone,
    (parentIsDone) => {
        isDone.value = parentIsDone;
    },
);
</script>

<style scoped lang="scss">
ol.step-children {
    list-style-type: none;
}

li.instructions-step {
    position: relative;
    padding-left: calc(36px + 1rem);
    margin-bottom: 2rem;
    clear: both;
    cursor: pointer;

    pointer-events: none;
    white-space: pre-line;

    &::before {
        position: absolute;
        top: 0;
        left: 0;
        width: 30px;
        height: 30px;
        border: 1px solid var(--color-border-dark);
        border-radius: 50%;
        background-color: var(--color-background-dark);
        background-position: center;
        background-repeat: no-repeat;
        line-height: 30px;
        outline: none;

        pointer-events: auto;
        text-align: center;
    }

    /** Color item number when text element is hovered */
    &:has(.instructions-step__text:hover)::before {
        border-color: var(--color-primary-element);
    }

    &.done {
        color: var(--color-text-lighter);
    }

    &.done::before {
        content: '✔';
    }

    .instructions-step__text {
        margin-bottom: 0.5rem;
        pointer-events: auto;
        white-space: normal;
    }

    :deep(.instructions-direction) {
        padding-left: 0;
        margin-bottom: 0.5rem;
    }
}
</style>
