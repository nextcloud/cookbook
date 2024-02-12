<template>
    <div class="yield">
        <span class="print-only">
            {{ yieldBuffer }}
        </span>
        <div class="print-hidden yield-controls">
            <button :disabled="yieldBuffer <= 1" @click="changeRecipeYield(-1)">
                <span class="icon-view-previous" />
            </button>
            <input
                v-model.number="yieldBuffer"
                type="number"
                min="0"
                class="recipe-yield-input"
                :class="yieldBuffer === originalYield ? '' : 'changed'"
                @keyup="handleKeyUp"
                @focusout="emitCurrentValue"
            />
            <button @click="changeRecipeYield(1)">
                <span class="icon-view-next" />
            </button>
            <button
                style="background: none; border: none"
                v-if="yieldBuffer !== originalYield"
                @click="restoreOriginalRecipeYield"
            >
                <span class="icon-history" />
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { adjustToInteger } from 'cookbook/js/helper';

const props = defineProps({
    /** Recipe yield
     * @type {number}
     */
    value: {
        type: Number,
        default: 1,
    },
    /** Original recipe yield
     * @type {number}
     */
    originalYield: {
        type: Number,
        default: 1,
    },
});

const yieldBuffer = ref(props.value);

const emit = defineEmits(['input']);

function changeRecipeYield(step = 1) {
    emit('input', adjustToInteger(yieldBuffer.value, step));
}

const restoreOriginalRecipeYield = () => {
    emit('input', props.originalYield);
};

/** Emits the current buffer value. */
function emitCurrentValue() {
    emit('input', yieldBuffer.value);
}

/** Handles the keyUp event on the input element. */
function handleKeyUp(evt) {
    if (evt.key === 'Enter') {
        emitCurrentValue();
    }
}

// ===================
// Watchers
// ===================

watch(
    () => props.value,
    (val) => {
        yieldBuffer.value = val;
    },
);

watch(
    () => yieldBuffer.value,
    () => {
        if (yieldBuffer.value < 0) {
            restoreOriginalRecipeYield();
        }
    },
);
</script>

<style scoped lang="scss">
.yield {
    display: flex;
    align-items: center;

    .yield-controls {
        display: inline-flex;
        align-items: center;

        button {
            display: inline-flex;
            padding: 0.25em 0.1em;
            height: 32px;
            min-height: 32px;
            width: 32px;
            vertical-align: middle;
            justify-content: center;
        }

        input.recipe-yield-input {
            width: 47px;
            padding: 0 6px;
            text-align: center;

            /* Chrome, Safari, Edge */
            &::-webkit-inner-spin-button,
            &::-webkit-outer-spin-button {
                margin: 0;
                -webkit-appearance: none;
            }

            /* Firefox */
            -moz-appearance: textfield;

            &.changed {
                border-color: var(--color-primary-element);
            }
        }
    }
}
</style>
