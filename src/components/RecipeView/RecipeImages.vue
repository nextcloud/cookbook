<template>
    <div
        class="image-container"
        :class="{
            printable: isPrinted,
        }"
    >
        <img
            :alt="t('cookbook', 'Recipe image')"
            :src="image[0]"
            class="inline-img absolute-md"
            @click="openFullImage()"
        />
        <NcModal
            :show.sync="isImageModalVisible"
            size="large"
            :name="t('Recipe image', 'cookbook')"
            :out-transition="true"
            class="print-hidden"
            @close="closeImageModal"
        >
            <div class="modal__content">
                <img
                    class="full-img"
                    :alt="t('cookbook', 'Recipe image')"
                    :src="image[0]"
                />
            </div>
        </NcModal>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import NcModal from '@nextcloud/vue/dist/Components/NcModal.js';

defineProps({
    /** Main recipe image.
     * @type {string[]}
     */
    image: {
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
});

const isImageModalVisible = ref(false);

function openFullImage() {
    isImageModalVisible.value = true;
}
function closeImageModal() {
    isImageModalVisible.value = false;
}
</script>

<script>
export default {
    name: 'RecipeImages',
};
</script>

<style scoped>
.image-container {
    img.inline-img {
        top: 0;
        left: 0;
        display: block;
        width: 100%;
        max-width: 100%;
        height: 100%;
        max-height: 100%;
        cursor: pointer;
        object-fit: cover;

        @media (min-width: 767px) {
            position: absolute;
        }
    }
}
@media print {
    .image-container:not(.printable) {
        display: none !important;
    }
}

.modal__content {
    height: 100%;

    img.full-img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
}
</style>
