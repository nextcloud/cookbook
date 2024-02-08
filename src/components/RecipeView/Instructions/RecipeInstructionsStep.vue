<template>
    <li
        v-if="step"
        class="instructions-step"
        :class="{ done: isDone }"
        @click="toggleDone"
    >
        <div v-if="step.text" class="instructions-step__text">
            {{ step.text }}
        </div>
        <!--        TODO Add support for missing properties -->
        <!--        <div>{{ step.timeRequired }}</div>-->
        <!--        <div>{{ step.image }}</div>-->
        <ol v-if="step.itemListElement">
            <component
                :is="childComponentType(item)"
                v-bind="childComponentProps(item)"
                v-for="(item, idx) in step.itemListElement"
                :key="`${parentId}_step-${item.position}-${item['name']}_item-${idx}`"
            >
            </component>
        </ol>
    </li>
</template>

<script setup>
import RecipeInstructionsDirection from 'cookbook/components/RecipeView/Instructions/RecipeInstructionsDirection.vue';
import RecipeInstructionsTip from 'cookbook/components/RecipeView/Instructions/RecipeInstructionsTip.vue';
import { ref, watch } from 'vue';

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
ol {
    counter-reset: item;
    list-style-type: none;
}

li.instructions-step {
    position: relative;
    padding-left: calc(36px + 1rem);
    margin-bottom: 2rem;
    clear: both;
    counter-increment: item;
    cursor: pointer;
    white-space: pre-line;

    &::before {
        position: absolute;
        top: 0;
        left: 0;
        width: 36px;
        height: 36px;
        border: 1px solid var(--color-border-dark);
        border-radius: 50%;
        background-color: var(--color-background-dark);
        background-position: center;
        background-repeat: no-repeat;
        content: counters(item);
        line-height: 36px;
        outline: none;
        text-align: center;
    }

    &:hover::before {
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
        white-space: normal;
    }

    :deep(.instructions-direction) {
        padding-left: 0;
        margin-bottom: 0.5rem;
    }
}
</style>
