<template>
    <div
        class="sheet"
        :class="[
            elevation > 0 && `elevation-${elevation}`,
            { 'sheet--rounded': rounded },
        ]"
    >
        <slot />
    </div>
</template>

<script setup>
defineProps({
    elevation: {
        type: Number,
        default: 0,
    },
    rounded: {
        type: Boolean,
        default: false,
    },
});
</script>

<style scoped lang="scss">
.sheet--rounded {
    border-radius: var(--border-radius-large);
}
</style>
