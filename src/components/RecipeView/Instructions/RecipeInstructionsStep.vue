<template>
    <li
        v-if="step"
        class="instructions-step"
        :class="{ completed: isCompleted }"
        @click="toggleCompleted"
    >
        <div style="display: table; min-height: 32px">
            <div style="display: table-cell; vertical-align: middle">
                <div v-if="step.text" class="instructions-step__text">
                    <VueShowdown
                        :markdown="normalizedText"
                        class="markdown-instruction"
                    />
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
                        ref="children"
                        :key="`${parentId}_step-${item.position ?? ''}-${item['name'] ?? ''}_item-${idx}`"
                        v-bind="childComponentProps(item)"
                        @update-completed="handleChildCompletedStateUpdate"
                    >
                    </component>
                </ol>
            </div>
        </div>
    </li>
</template>

<script setup>
import { getCurrentInstance, ref } from 'vue';
import { computedAsync } from '@vueuse/core';
import normalizeMarkdown from 'cookbook/js/title-rename';
import RecipeInstructionsDirection from 'cookbook/components/RecipeView/Instructions/RecipeInstructionsDirection.vue';
import RecipeInstructionsTip from 'cookbook/components/RecipeView/Instructions/RecipeInstructionsTip.vue';
import useCompletable from 'cookbook/composables/useCompleteable';

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
});

const emit = defineEmits(['update-completed']);

/**
 * References of child items.
 */
const children = ref(null);

const { isCompleted, setCompleted, toggleCompleted } = useCompletable(
    emit,
    children,
);

// ===================
// Computed properties
// ===================

/** Normalized text property with recipe-reference links. */
const normalizedText = computedAsync(
    async () => {
        try {
            return await normalizeMarkdown(props.step.text);
        } catch (e) {
            log.warn(
                `Could not normalize Markdown. Error Message: ${e.message}`,
            );
        }
        return '';
    },
    t('cookbook', 'Loading…'),
);

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
            return {
                direction: item,
                // parentIsCompleted: isCompleted.value
            };
        case 'HowToTip':
            return {
                tip: item,
                // parentIsCompleted: isCompleted.value
            };
        default:
            return '';
    }
}

/**
 * Handles update of the complete state in children.
 */
function handleChildCompletedStateUpdate() {
    // return;
    let allChildrenCompleted = true;
    for (const child of children.value) {
        if (child?.isCompleted !== undefined) {
            if (!child.isCompleted.value && isCompleted.value) {
                isCompleted.value = false;
                return;
            }
            if (!child.isCompleted) {
                allChildrenCompleted = false;
            }
        }
    }
    // Only mark as completed if all children are completed
    isCompleted.value = allChildrenCompleted;
}

// ===================
// Expose
// ===================

defineExpose({ isCompleted, setCompleted });
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

    &.completed {
        color: var(--color-text-lighter);
    }

    &.completed::before {
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
