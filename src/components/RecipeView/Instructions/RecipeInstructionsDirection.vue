<template>
    <li
        v-if="direction"
        class="instructions-direction"
        :class="{ done: isDone }"
        @click="toggleDone"
    >
        <div style="display: table; min-height: 32px">
            <div style="display: table-cell; vertical-align: middle">
                <!--        TODO Add support for missing properties -->
                <!--        <div>{{ direction.supply }}</div>-->
                <!--        <div>{{ direction.tool }}</div>-->
                <!--        <div>{{ direction.timeRequired }}</div>-->
                <!--        <div>{{ direction.image }}</div>-->
                <div v-if="direction.text" class="instructions-direction__text">
                    <VueShowdown
                        :markdown="normalizedText"
                        class="markdown-instruction"
                    />
                </div>
            </div>
        </div>
    </li>
</template>

<script setup>
import { getCurrentInstance, ref, watch } from 'vue';
import { computedAsync } from '@vueuse/core';
import normalizeMarkdown from 'cookbook/js/title-rename';

const log = getCurrentInstance().proxy.$log;

const props = defineProps({
    /** @type {HowToDirection|null} */
    direction: {
        type: Object,
        default: null,
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
const normalizedText = computedAsync(
    async () => {
        try {
            return await normalizeMarkdown(props.direction.text);
        } catch (e) {
            log.warn(
                `Could not normalize Markdown. Error Message: ${e.message}`,
            );
        }
        return '';
    },
    t('cookbook', 'Loading…'),
);

/** Toggles the completed state on this step. */
function toggleDone(evt) {
    // evt.preventDefault();
    evt.stopPropagation();
    isDone.value = !isDone.value;
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
li.instructions-direction {
    position: relative;
    padding-left: calc(36px + 1rem);
    margin-bottom: 2rem;
    clear: both;
    cursor: pointer;
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
        text-align: center;
    }

    /** If there is only a single direction in the list, do not add a sub-item numbering */
    &:only-child::before {
        content: none;
    }

    /** Color item number when text element is hovered */
    &:has(.instructions-direction__text:hover)::before {
        border-color: var(--color-primary-element);
    }

    &.done {
        color: var(--color-text-lighter);
    }

    &.done::before {
        content: '✔';
    }
}

.instructions-direction__text {
    pointer-events: all;
    white-space: normal;
}

/* If there is a list and a text, numbers are shown for the substeps - add padding. */
.instructions-step__text ~ .step-children {
    .instructions-direction {
        padding-left: calc(36px + 1rem);
    }
}
</style>
