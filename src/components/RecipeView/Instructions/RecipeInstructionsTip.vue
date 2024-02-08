<template>
    <div class="instructions-tip">
        <div class="icon-container">
            <InfoIcon :size="30" class="mr-1" />
        </div>
        <!--        TODO Add support for missing properties -->
        <!--        <div>{{ tip.timeRequired }}</div>-->
        <!--        <div>{{ tip.image }}</div>-->
        <div class="pl-3">{{ tip.text }}</div>
    </div>
</template>

<script setup>
import InfoIcon from 'icons/InformationVariant.vue';

defineProps({
    /** @type {HowToTip|null} */
    tip: {
        type: Object,
        default: () => null,
    },
    /** @type {bool} The parent is marked as completed */
    parentIsDone: {
        type: Boolean,
        default: false,
    },
});
</script>

<style scoped lang="scss">
.mr-1 {
    margin-right: 0.25rem;
}

.pl-3 {
    padding-left: 0.75rem;
}

.instructions-tip {
    position: relative;
    display: flex;
    flex-direction: row;

    margin-top: 0.5rem;

    /* Adjusted to have the same alignment as directions, steps, etc. due to item numbering labels. */
    padding-left: 6px;

    clear: both;
    white-space: pre-line;

    .icon-container {
        flex-shrink: 0;
        border-right: solid 1px var(--color-border-dark);
    }
}
</style>
