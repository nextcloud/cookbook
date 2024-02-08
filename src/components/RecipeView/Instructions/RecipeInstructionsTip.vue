<template>
    <div class="instructions-tip">
        <div class="icon-container">
            <InfoIcon :size="30" class="mr-1" />
        </div>
        <!--        TODO Add support for missing properties -->
        <!--        <div>{{ tip.timeRequired }}</div>-->
        <!--        <div>{{ tip.image }}</div>-->
        <div v-if="tip.text" class="pl-3">
            <VueShowdown
                :markdown="normalizedText"
                class="markdown-instruction"
            />
        </div>
    </div>
</template>

<script setup>
import { computedAsync } from '@vueuse/core';
import InfoIcon from 'icons/InformationVariant.vue';
import normalizeMarkdown from 'cookbook/js/title-rename';
import { getCurrentInstance } from 'vue';

const log = getCurrentInstance().proxy.$log;

const props = defineProps({
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

// ===================
// Computed properties
// ===================

/** Normalized text property with recipe-reference links. */
const normalizedText = computedAsync(async () => {
    try {
        return await normalizeMarkdown(props.tip.text);
    } catch (e) {
        log.warn(`Could not normalize Markdown. Error Message: ${e.message}`);
    }
    return '';
}, '');
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

    /* Adjusted to have the same alignment as directions, steps, etc. due to item numbering labels. */
    padding-left: 6px;

    margin-top: 0.5rem;

    clear: both;
    white-space: pre-line;

    .icon-container {
        flex-shrink: 0;
        border-right: solid 1px var(--color-border-dark);
    }
}
</style>
