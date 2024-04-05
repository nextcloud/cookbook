<template>
    <div class="p-4" :class="delayedDisplay.isVisible.value ? '' : 'hidden'">
        <LoadingSkeleton :type="SkeletonType.Heading" class="mb-6" />
        <LoadingSkeleton
            :type="SkeletonType.Heading"
            max-width="10em"
            class="mb-4"
        />
        <div class="d-flex flex-row align-items-center mb-4">
            <LoadingSkeleton
                :type="SkeletonType.Text"
                max-width="8em"
                style="margin-right: 1rem; margin-bottom: 0"
            />
            <LoadingSkeleton
                :type="SkeletonType.Chip"
                style="margin-bottom: 0"
            />
        </div>

        <div class="d-flex flex-row align-items-center mb-6">
            <LoadingSkeleton
                :type="SkeletonType.Text"
                max-width="6em"
                style="margin-right: 1rem; margin-bottom: 0"
            />
            <LoadingSkeleton
                :type="SkeletonType.Chip"
                style="margin-bottom: 0"
            />
            <LoadingSkeleton
                :type="SkeletonType.Chip"
                style="margin-bottom: 0"
            />
            <LoadingSkeleton
                :type="SkeletonType.Chip"
                style="margin-bottom: 0"
            />
        </div>

        <div class="mb-6">
            <LoadingSkeleton
                :type="SkeletonType.Heading"
                max-width="10em"
                class="mb-4"
            />
            <LoadingSkeleton
                :type="SkeletonType.ListItem"
                max-width="10em"
                class="mb-4"
            />
        </div>

        <div class="mb-6">
            <LoadingSkeleton
                :type="SkeletonType.Heading"
                max-width="10em"
                class="mb-4"
            />
            <LoadingSkeleton
                :type="SkeletonType.ListItem"
                max-width="10em"
                class="mb-4"
            />
            <LoadingSkeleton
                :type="SkeletonType.ListItem"
                max-width="10em"
                class="mb-4"
            />
            <LoadingSkeleton
                :type="SkeletonType.ListItem"
                max-width="10em"
                class="mb-4"
            />
        </div>
    </div>
</template>

<script setup>
import LoadingSkeleton from 'cookbook/components/Utilities/LoadingSkeleton/LoadingSkeleton.vue';
import SkeletonType from 'cookbook/components/Utilities/LoadingSkeleton/SkeletonType';
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

<script>
export default {
    name: 'RecipeViewSidebarLoadingSkeleton',
};
</script>
