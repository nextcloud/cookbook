<template>
    <NcModal
        v-bind="passedProps"
        size="large"
        :name="currentImage.title"
        :out-transition="true"
        :enable-slideshow="hasNext || hasPrevious"
        :has-next="hasNext"
        :has-previous="hasPrevious"
        class="print-hidden"
        :show="show"
        @previous="previous"
        @next="next"
        @update:show="updateShow"
        v-on="$listeners"
    >
        <div class="modal__content">
            <img
                class="full-img"
                :alt="t('cookbook', 'Recipe image {number}', { number: 1 })"
                :src="currentImage.src"
            />
        </div>
    </NcModal>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import NcModal from '@nextcloud/vue/dist/Components/NcModal.js';

const emit = defineEmits(['close', 'update:show']);
const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    /** Main recipe images.
     * @type {string[]}
     */
    images: {
        type: Array,
        default: () => [],
    },
    /** The index of the currently displayed image.
     * @type {number}
     */
    displayedImageIndex: {
        type: Number,
        default: 0,
    },
    /** The name of the recipe for which the images are shown.
     * @type {string}
     */
    recipeName: {
        type: String,
        default: t('cookbook', 'Recipe images'),
    },
});

/** The index of the currently shown image in the list of images. */
const currentIndex = ref(props.displayedImageIndex);

watch(
    () => props.displayedImageIndex,
    (newIdx) => {
        currentIndex.value = newIdx;
    },
);

const passedProps = computed(() => ({ show: props.show }));

/** Data of the currently displayed image. */
const currentImage = computed(() => ({
    src: props.images[currentIndex.value],
    title: t('cookbook', '{recipeName} {imageNumber}', {
        recipeName: props.recipeName,
        imageNumber: currentIndex.value + 1,
    }),
}));

/** If there is a next image available in the carousel. */
const hasNext = computed(() => currentIndex.value < props.images.length - 1);

/** If there is a previous image available in the carousel. */
const hasPrevious = computed(() => currentIndex.value > 0);

/** Emit update of the show property. */
function updateShow(newValue) {
    emit('update:show', newValue);
}

/** Go to next image in the carousel. */
function next() {
    if (hasNext.value) {
        currentIndex.value += 1;
    }
}
/** Go to previous image in the carousel. */
function previous() {
    if (hasPrevious.value) {
        currentIndex.value -= 1;
    }
}
</script>

<style scoped lang="scss">
.modal__content {
    height: 100%;

    img.full-img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
}
</style>
