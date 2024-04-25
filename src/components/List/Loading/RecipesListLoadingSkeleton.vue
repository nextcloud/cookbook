<template>
    <div :class="delayedDisplay.isVisible.value ? '' : 'hidden'">
        <RecipesListItemLoadingSkeleton />
        <RecipesListItemLoadingSkeleton />
        <RecipesListItemLoadingSkeleton />
        <RecipesListItemLoadingSkeleton />
    </div>
</template>
<script setup>
import RecipesListItemLoadingSkeleton from 'cookbook/components/List/Loading/RecipesListItemLoadingSkeleton.vue';
import useDelayedDisplay from 'cookbook/composables/useDelayedDisplay';

const props = defineProps({
    /** Delay in milliseconds before the component is displayed */
    delay: {
        type: Number,
        default: 0,
    },
});

const delayedDisplay = useDelayedDisplay(props.delay);
</script>
<style scoped lang="scss"></style>
