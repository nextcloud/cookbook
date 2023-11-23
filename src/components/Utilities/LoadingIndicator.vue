<template>
    <LoadingIcon
        v-bind="$attrs"
        class="animation-rotate opacity-transition"
        :class="delayedDisplay.isVisible.value ? '' : 'hidden'"
    />
</template>

<script setup>
import LoadingIcon from 'icons/Loading.vue';
import useDelayedDisplay from '../../composables/useDelayedDisplay';

const props = defineProps({
    /** Delay in milliseconds before the component is displayed */
    delay: {
        type: Number,
        default: 0,
    },
});

const delayedDisplay = useDelayedDisplay(props.delay);
</script>
<script>
export default {
    name: 'LoadingIndicator',
};
</script>

<style scoped>
.opacity-transition {
    transition: opacity 0.25s ease-in-out;
}

.animation-rotate {
    animation: rotate var(--animation-duration, 0.8s) linear infinite;
}

.hidden {
    opacity: 0;
}
</style>
