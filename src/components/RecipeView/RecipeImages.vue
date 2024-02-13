<template>
    <div
        class="image-container"
        :class="{
            printable: isPrinted,
        }"
    >
        <img
            :alt="t('cookbook', 'Recipe image')"
            :src="image"
            @click="toggleCollapsed()"
            class="absolute-md"
        />
    </div>
</template>

<script setup>

const props = defineProps({
    /** Main recipe image.
     * @type {string}
     */
    image: {
        type: String,
        default: '',
    },
    /** If the image should be visible when printed.
     * @type {boolean}
     */
    isPrinted: {
        type: Boolean,
        default: true,
    },
});
</script>

<script>
export default {
    name: 'RecipeImages',
};
</script>

<style scoped>
.image-container {
    img {
        width: 100%;
        height: 100%;
        max-width: 100%;
        max-height: 100%;
        display: block;

        @media (min-width: 767px) {
            position: absolute;
        }
        top: 0;
        left: 0;
        object-fit: cover;
    }
}
@media print {
    .image-container:not(.printable) {
        display: none !important;
    }
}
</style>
