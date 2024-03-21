<template>
    <div
        class="image-container"
        :class="{
            printable: isPrinted,
        }"
    >
        <img
            :alt="t('cookbook', 'Main recipe image')"
            :src="images[0]"
            class="inline-img"
            style="object-fit: contain"
            @click="openFullImage()"
        />
        <div v-if="imagePreviews.length > 0" class="image-previews">
            <img
                v-for="(prevImage, index) in imagePreviews"
                :key="'img-prev-' + index"
                :alt="
                    t('cookbook', 'Recipe image {number} preview', {
                        number: index + 2,
                    })
                "
                :src="prevImage"
                class="preview-image"
                :class="index === 2 && images.length > 4 ? 'faded' : ''"
                @click="openFullImage(index + 1)"
            />
        </div>
        <RecipeImagesViewer
            :show.sync="isImageModalVisible"
            :images="images"
            :displayed-image-index="currentFullImageIndex"
            :recipe-name="recipeName"
            class="print-hidden"
        />
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import RecipeImagesViewer from 'cookbook/components/RecipeView/Images/RecipeImagesViewer.vue';

const props = defineProps({
    /** Main recipe image.
     * @type {string[]}
     */
    images: {
        type: Array,
        default: () => [],
    },
    /** Main recipe thumbnail images.
     * @type {string[]}
     */
    thumbnails: {
        type: Array,
        default: () => [],
    },
    /** If the image should be visible when printed.
     * @type {boolean}
     */
    isPrinted: {
        type: Boolean,
        default: true,
    },
    /** The name of the recipe for which the images are shown.
     * @type {string}
     */
    recipeName: {
        type: String,
        default: t('cookbook', 'Recipe images'),
    },
});

const isImageModalVisible = ref(false);
const currentFullImageIndex = ref(0);

/**
 * List of URLs for images previews.
 * @type {import('vue').ComputedRef<string[]>}
 */
const imagePreviews = computed(() => props.thumbnails.slice(1, 4));

function openFullImage(index = 0) {
    currentFullImageIndex.value = index;
    isImageModalVisible.value = true;
}
</script>

<script>
export default {
    name: 'RecipeImages',
};
</script>

<style lang="scss" scoped>
.image-container {
    position: relative;
    overflow: hidden;
    width: 100%;
    height: 33vh;
    margin: 0;

    img.inline-img {
        position: absolute;
        top: 50%;
        max-width: 100%;
        cursor: pointer;
        transform: translateY(-50%);
    }

    .image-previews {
        position: absolute;
        right: 15px;
        bottom: 15px;
        display: flex;
        gap: 10px;

        /* Move images closer to the edge if last one is partially faded out */
        &:has(:last-child.faded) {
            margin-right: -8px;

            /* Fade last image if there are more images than can be shown */
            img.preview-image.faded {
                mask-image: linear-gradient(
                    to right,
                    rgba(0, 0, 0, 1),
                    rgba(0, 0, 0, 1) 25%,
                    rgba(0, 0, 0, 0.5) 50%,
                    rgba(0, 0, 0, 0) 75%
                );
            }
        }

        img.preview-image {
            width: 40px;
            height: 40px;
            cursor: pointer;
            object-fit: cover;
        }
    }
}
@media print {
    .image-container:not(.printable) {
        display: none !important;
    }
}
</style>
