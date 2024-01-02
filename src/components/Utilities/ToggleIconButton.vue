<template>
    <div class="checkbox">
        <label :for="id" class="checkbox__label">
            <component
                :is="isChecked ? checkedIcon : uncheckedIcon"
                v-bind="
                    isChecked
                        ? { ...iconProps, ...checkedIconProps }
                        : { ...iconProps, ...uncheckedIconProps }
                "
            />
        </label>
        <input :id="id" v-model="isChecked" type="checkbox" @change="toggle" />
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    /**
     * Unique identifier for the input element.
     */
    id: {
        type: String,
        default: () => Math.random().toString(36).substr(2, 9),
    },
    /**
     * Icon component to be used for the checked state.
     */
    checkedIcon: {
        type: Object,
        default: () => {},
    },
    /**
     * Icon component to be used for the unchecked state.
     */
    uncheckedIcon: {
        type: Object,
        default: () => {},
    },
    /**
     * Properties passed the checked and unchecked icon. Will be merged with the more specific props for each state.
     */
    iconProps: {
        type: Object,
        default: () => {},
    },
    /**
     * Properties passed to the checked icon. Will be merged with `iconProps`.
     */
    checkedIconProps: {
        type: Object,
        default: () => {},
    },
    /**
     * Properties passed to the unchecked icon. Will be merged with `iconProps`.
     */
    uncheckedIconProps: {
        type: Object,
        default: () => {},
    },
    /**
     * Checked state of the input.
     */
    value: {
        type: Boolean,
        default: false,
        required: true,
    },
});

const emit = defineEmits(['input', 'check', 'uncheck', 'update']);

/**
 * Local value of the input.
 * @type {import('vue').Ref<boolean>}
 */
const isChecked = ref(props.value);

// Watch the model value and update local value on external updates.
watch(
    () => props.value,
    (newValue) => {
        isChecked.value = newValue;
    },
);

/**
 * Toggle the value on the input between `true` and `false`.
 */
function toggle() {
    emit('input', isChecked.value);
    emit('update', isChecked.value);
    if (isChecked.value) {
        emit('check');
    } else {
        emit('uncheck');
    }
}
</script>

<style lang="scss" scoped>
.checkbox {
    cursor: pointer;
}

input[type='checkbox'] {
    display: none;
}

.checkbox__label {
    position: relative;
    display: flex;
    overflow: hidden;
    width: fit-content;
    min-width: 44px;
    min-height: 44px;
    align-items: center;
    justify-content: center;
    padding: 0;
    border: 0;
    border-radius: 22px;
    color: var(--color-primary-element-light-text);
    cursor: pointer;
    font-size: var(--default-font-size);
    font-weight: bold;
    transition-duration: 0.1s;
    transition-property: color, border-color, background-color;
    transition-timing-function: linear;

    span {
        cursor: pointer;
    }

    &:hover {
        background-color: var(--color-background-hover);
    }
}
</style>
