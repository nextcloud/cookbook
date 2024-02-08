<template>
    <li
        v-if="direction"
        class="instructions-direction"
        :class="{ done: isDone }"
        @click="toggleDone"
    >
        <!--        TODO Add support for missing properties -->
        <!--        <div>{{ direction.supply }}</div>-->
        <!--        <div>{{ direction.tool }}</div>-->
        <!--        <div>{{ direction.timeRequired }}</div>-->
        <!--        <div>{{ direction.image }}</div>-->
        <div v-if="direction.text">{{ direction.text }}</div>
    </li>
</template>

<script setup>
import { ref, watch } from 'vue';

defineProps({
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
}
</style>
