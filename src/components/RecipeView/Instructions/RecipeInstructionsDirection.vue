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
                <div v-if="direction.text">{{ direction.text }}</div>
            </div>
        </div>
    </li>
</template>

<script setup>
import { ref, watch } from 'vue';

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

/** Toggles the completed state on this step. */
function toggleDone() {
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

    &:hover::before {
        border-color: var(--color-primary-element);
    }

    &.done {
        color: var(--color-text-lighter);
    }

    &.done::before {
        content: '✔';
    }
}

/* If there is a list and a text, numbers are shown for the substeps - add padding. */
.instructions-step__text ~ .step-children {
    .instructions-direction {
        padding-left: calc(36px + 1rem);
    }
}

/** For top level directions outside a section, show top-level count */
ol.instructions > li.instructions-direction {
    //counter-increment: sectionIndex;

    ::before {
        //content: counter(sectionIndex);
    }
}
</style>
