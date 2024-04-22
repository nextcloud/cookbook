<template>
    <picture
        ref="pictureElement"
        class="lazy-img"
        style="min-height: 1rem"
        :data-alt="alt"
        :style="style"
    >
        <span
            v-if="isPreviewLoading && !hasPlaceholder"
            class="loading-indicator icon-loading"
        />
        <img
            v-if="blurredPreviewSrc"
            ref="previewImage"
            class="low-resolution blurred"
            :class="{ 'preview-loaded': !isPreviewLoading }"
            :width="width ?? ''"
            :height="height ?? ''"
            :style="[objectFit]"
        />
        <img
            ref="fullImage"
            class="full-resolution"
            :class="{ 'image-loaded': !isLoading }"
            :width="width ?? ''"
            :height="height ?? ''"
            :style="[objectFit]"
            @click="emit('click')"
        />
        <div
            v-if="isLoading && (isPreviewLoading || !blurredPreviewSrc)"
            class="absolute w-full h-full placeholder-wrapper"
        >
            <slot />
        </div>
    </picture>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref, useSlots, watch } from 'vue';
import lozad from 'lozad';

const slots = useSlots();

const props = defineProps({
    alt: {
        type: String,
        default: null,
    },
    blurredPreviewSrc: {
        type: String,
        default: undefined,
    },
    // Resizes the background image to cover the entire container.
    cover: {
        type: Boolean,
        default: false,
    },
    lazySrc: {
        type: String,
        default: undefined,
        required: true,
    },
    width: {
        type: String,
        default: null,
    },
    height: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['click', 'loadingComplete']);

/** @type {import('vue').Ref<UnwrapRef<boolean>>} */
const isPreviewLoading = ref(true);
/** @type {import('vue').Ref<UnwrapRef<boolean>>} */
const isLoading = ref(true);
/** @type {import('vue').Ref<UnwrapRef<HTMLElement|null>>} */
const pictureElement = ref(null);
/** @type {import('vue').Ref<UnwrapRef<HTMLElement|null>>} */
const fullImage = ref(null);
/** @type {import('vue').Ref<UnwrapRef<HTMLElement|null>>} */
const previewImage = ref(null);

/**
 * True if a placeholder element while loading has been defined
 * @type {ComputedRef<boolean>}
 */
const hasPlaceholder = computed(() => !!slots.default);

const objectFit = computed(() => (props.cover ? { objectFit: 'cover' } : {}));

// ========================================
// Methods
// ========================================
/**  Callback for fully-loaded image event */
function onImageFullyLoaded() {
    fullImage.value?.removeEventListener('load', onImageFullyLoaded);
    if (previewImage.value) {
        pictureElement.value?.removeChild(previewImage.value);
    }
    isLoading.value = false;
    emit('loadingComplete');
}

/** Callback for preview-image-loaded event */
function onImagePreviewLoaded() {
    // cleanup event listener on preview
    previewImage.value?.removeEventListener('load', onImagePreviewLoaded);
    // add event listener for full-resolution image
    if (fullImage.value) {
        fullImage.value.addEventListener('load', onImageFullyLoaded);
        fullImage.value.src = props.lazySrc;
    }
    isPreviewLoading.value = false;
}

/**
 * Initializes lazy loading preview and full image if available.
 */
function init() {
    // init lozad
    const observer = lozad(pictureElement.value, {
        enableAutoReload: true,
        load() {
            isPreviewLoading.value = !!props.blurredPreviewSrc;

            // Listen to preview-loaded event if preview src is set
            if (props.blurredPreviewSrc) {
                previewImage.value?.addEventListener(
                    'load',
                    onImagePreviewLoaded,
                );
                previewImage.value.src = props.blurredPreviewSrc;
            }
            // Listen to full-image-loaded event if no preview src is set
            else if (fullImage.value) {
                fullImage.value.addEventListener('load', onImageFullyLoaded);
                fullImage.value.src = props.lazySrc;
            }
        },
    });
    observer.observe();
}

/**
 * Resets the event listeners and loading state.
 */
function reset() {
    isLoading.value = true;
    isPreviewLoading.value = true;
    fullImage.value.src = undefined;
    pictureElement.value?.setAttribute('data-loaded', false);
    removeEventListeners();
}

/**
 * Removes the event listeners.
 */
function removeEventListeners() {
    if (previewImage.value !== 'undefined' && previewImage.value != null) {
        previewImage.value.removeEventListener('load', onImagePreviewLoaded);
    }
    if (fullImage.value !== 'undefined' && fullImage.value != null) {
        fullImage.value.removeEventListener('load', onImageFullyLoaded);
    }
}

// ========================================
// Computed properties
// ========================================
const style = computed(() => {
    const tmpStyle = {};
    if (props.width) {
        tmpStyle.width = props.width;
    }
    if (isLoading.value && props.height && !props.blurredPreviewSrc) {
        tmpStyle.height = 0;
        tmpStyle.paddingTop = props.height;
    }
    return tmpStyle;
});

// ========================================
// Watchers
// ========================================
/**
 * Reload image when source has changed. This may be the case when the component is reused for a different image.
 */
watch(
    () => props.lazySrc,
    () => {
        reset();
        init();
    },
);

// ========================================
// Vue lifecycle
// ========================================
onMounted(() => {
    init();
});

onUnmounted(() => {
    removeEventListeners();
});
</script>

<script>
export default {
    name: 'LazyPicture',
};
</script>

<style scoped>
.lazy-img {
    overflow: hidden;
    max-width: 100%;
    max-height: 100%;
    vertical-align: middle;
}

picture {
    .placeholder-wrapper > * {
        height: 100%;
    }
    .loading-indicator {
        display: contents;
        align-content: center;
    }
    .blurred {
        filter: blur(0.5rem);
    }
    .low-resolution.preview-loaded {
        display: inline;
        animation: fadeIn 1s linear 0s;
    }
    .full-resolution {
        display: none;
    }
    .full-resolution.image-loaded {
        display: inline;
        animation: unblur 1s linear 0s;
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }

    to {
        opacity: 1;
    }
}

@keyframes unblur {
    from {
        filter: blur(0.5rem);
    }

    to {
        filter: blur(0);
    }
}
</style>
