<template>
    <div
        class="image-container"
        :class="{
            printable: isPrinted,
        }"
    >
        <img
            :alt="t('cookbook', 'Main recipe image')"
            :src="image[0]"
            class="inline-img"
            style="object-fit: contain"
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
                    :alt="t('cookbook', 'Recipe image {number}', { number: 1 })"
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
