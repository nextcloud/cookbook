<template>
    <picture
        ref="pictureElement"
        class="lazy-img"
        style="min-height: 1rem"
        :data-alt="alt"
        :style="style"
    >
        <span v-if="isPreviewLoading" class="loading-indicator icon-loading" />
        <img
            ref="previewImage"
            class="low-resolution blurred"
            :class="{ 'preview-loaded': !isPreviewLoading }"
            :width="width ? width + 'px' : ''"
            :height="height ? height + 'px' : ''"
        />
        <img
            ref="fullImage"
            class="full-resolution"
            :class="{ 'image-loaded': !isLoading }"
            :width="width ? width + 'px' : ''"
            :height="height ? height + 'px' : ''"
        />
    </picture>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import lozad from 'lozad';

const props = defineProps({
    alt: {
        type: String,
        default: null,
    },
    blurredPreviewSrc: {
        type: String,
        default: null,
    },
    lazySrc: {
        type: String,
        default: null,
    },
    width: {
        type: Number,
        default: null,
    },
    height: {
        type: Number,
        default: null,
    },
});

/** @type {Ref<UnwrapRef<boolean>>} */
const isPreviewLoading = ref(true);
/** @type {Ref<UnwrapRef<boolean>>} */
const isLoading = ref(true);
/** @type {HTMLElement|null} */
const pictureElement = ref(null);
/** @type {HTMLElement|null} */
const fullImage = ref(null);
/** @type {HTMLElement|null} */
const previewImage = ref(null);

// Methods
// callback for fully-loaded image event
const onThumbnailFullyLoaded = () => {
    fullImage.value.removeEventListener('load', onThumbnailFullyLoaded);
    pictureElement.value.removeChild(previewImage.value);
    isLoading.value = false;
};

// callback for preview-image-loaded event
const onThumbnailPreviewLoaded = () => {
    // cleanup event listener on preview
    previewImage.value.removeEventListener('load', onThumbnailPreviewLoaded);
    // add event listener for full-resolution image
    fullImage.value.addEventListener('load', onThumbnailFullyLoaded);
    fullImage.value.src = props.lazySrc;
    isPreviewLoading.value = false;
};

// Computed properties
const style = computed(() => {
    const tmpStyle = {};
    if (props.width) {
        tmpStyle.width = `${props.width}px`;
    }
    if (isLoading.value && props.height && !props.blurredPreviewSrc) {
        tmpStyle.height = 0;
        tmpStyle.paddingTop = `${props.height}px`;
    }
    return tmpStyle;
});

// Vue lifecycle
onMounted(() => {
    // init lozad
    const observer = lozad(pictureElement.value, {
        enableAutoReload: true,
        load() {
            previewImage.value.addEventListener(
                'load',
                onThumbnailPreviewLoaded,
            );
            previewImage.value.src = props.blurredPreviewSrc;
        },
    });
    observer.observe();
});

onUnmounted(() => {
    if (previewImage.value !== 'undefined' && previewImage.value != null) {
        previewImage.value.removeEventListener(
            'load',
            onThumbnailPreviewLoaded,
        );
    }
    if (fullImage.value !== 'undefined' && fullImage.value != null) {
        fullImage.value.removeEventListener('load', onThumbnailFullyLoaded);
    }
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

picture .loading-indicator {
    display: contents;
    align-content: center;
}

picture .blurred {
    filter: blur(0.5rem);
}

picture .low-resolution.preview-loaded {
    display: inline;
    animation: fadeIn 1s linear 0s;
}

picture .full-resolution {
    display: none;
}

picture .full-resolution.image-loaded {
    display: inline;
    animation: unblur 1s linear 0s;
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
