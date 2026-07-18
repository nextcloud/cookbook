<template>
    <div class="checkbox">
        <label :for="id" class="checkbox__label">
            <component :is="selectedComponent" v-bind="selectedProps" />
        </label>
        <input :id="id" v-model="value" type="checkbox" />
    </div>
</template>

<script setup>
import { computed } from 'vue';

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
});

const value = defineModel({
    type: Boolean,
    required: true,
});

const selectedComponent = computed(() =>
    value.value ? props.checkedIcon : props.uncheckedIcon,
);
const selectedProps = computed(() =>
    value.value
        ? { ...props.iconProps, ...props.checkedIconProps }
        : { ...props.iconProps, ...props.uncheckedIconProps },
);
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
