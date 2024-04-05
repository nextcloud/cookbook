<template>
    <div
        v-if="loading"
        class="loading-skeleton"
        :style="{
            width,
            height,
            minWidth,
            maxWidth,
            minHeight,
            maxHeight,
        }"
        :class="type"
    >
        <div class="loading-skeleton__content" />
        <LoadingSkeleton
            v-if="type === SkeletonType.Paragraph"
            :type="SkeletonType.Text"
        />
        <LoadingSkeleton
            v-if="type === SkeletonType.Paragraph"
            :type="SkeletonType.Text"
        />
        <LoadingSkeleton
            v-if="type === SkeletonType.Paragraph"
            :type="SkeletonType.Text"
        />
        <LoadingSkeleton
            v-if="type === SkeletonType.ListItem"
            :type="SkeletonType.Text"
        />
    </div>
    <div v-else>
        <slot />
    </div>
</template>

<script setup>
import { defineProps } from 'vue';
import SkeletonType from './SkeletonType';

/**
 * LoadingSkeleton component props
 * @typedef {Object} LoadingSkeletonProps
 * @property {string} width - The width of the skeleton element (default: undefined)
 * @property {string} height - The height of the skeleton element (default: undefined)
 * @property {string} minWidth - The minimum width of the skeleton element (default: undefined)
 * @property {string} maxWidth - The maximum width of the skeleton element (default: undefined)
 * @property {string} minHeight - The minimum height of the skeleton element (default: undefined)
 * @property {string} maxHeight - The maximum height of the skeleton element (default: undefined)
 * @property {SkeletonType} type - The type of skeleton element (default: SkeletonType.Text)
 * @property {boolean} loading - Indicates whether the content is being loaded (default: true)
 */

/**
 * LoadingSkeleton component
 * @param {LoadingSkeletonProps} props - Component props
 */
defineProps({
    width: {
        type: String,
        default: undefined,
    },
    height: {
        type: String,
        default: undefined,
    },
    minWidth: {
        type: String,
        default: undefined,
    },
    maxWidth: {
        type: String,
        default: undefined,
    },
    minHeight: {
        type: String,
        default: undefined,
    },
    maxHeight: {
        type: String,
        default: undefined,
    },
    type: {
        type: String,
        default: SkeletonType.Text,
        validator: (value) => Object.values(SkeletonType).includes(value),
    },
    loading: {
        type: Boolean,
        default: true,
    },
});
</script>

<style lang="scss" scoped>
$loading-skeleton-heading-height: 24px !default;
$loading-skeleton-image-height: 150px !default;
$loading-skeleton-text-height: 12px !default;

@keyframes loading {
    to {
        transform: translateX(100%);
    }
}

.loading-skeleton {
    position: relative;
    overflow: hidden;
    flex: 1 1 100%;

    align-items: center;
    border-radius: 4px;
    display: flex;
    flex-wrap: wrap;
    vertical-align: top;

    &__content {
        position: relative;
        top: 0;
        display: flex;
        overflow: hidden;
        width: 100%;
        height: 100%;
        flex: 1 1 100%;
        flex-wrap: wrap;
        align-items: center;
        border-radius: inherit;
        background-color: rgba(128, 128, 128, 0.12);

        &::after {
            position: absolute;
            z-index: 1;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            animation: loading 1.5s infinite;
            background: linear-gradient(
                90deg,
                rgba(128, 128, 128, 0),
                rgba(128, 128, 128, 0.3),
                rgba(128, 128, 128, 0)
            );
            content: '';
            transform: translateX(-100%);
        }
    }

    &.avatar {
        border-radius: 50%;
        flex: 0 1 auto;
        margin: 8px 16px;
        max-height: 48px;
        min-height: 48px;
        height: 48px;
        max-width: 48px;
        min-width: 48px;
        width: 48px;
    }

    &.chip {
        border-radius: var(--border-radius-pill);
        margin-right: 12px;
        margin-bottom: 12px;
        height: 24px;
        max-width: 96px;
    }

    &.heading {
        height: $loading-skeleton-heading-height;
        max-width: 100%;
    }

    &.image {
        height: $loading-skeleton-image-height;
    }

    &.list-item {
        margin: 16px;
    }

    &.text {
        height: $loading-skeleton-text-height;
        margin-bottom: 8px;

        + .text {
            max-width: 50%;
        }

        + .text + .text {
            max-width: 70%;
        }
    }
}
</style>
