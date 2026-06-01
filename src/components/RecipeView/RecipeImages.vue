<template>
    <div
        v-if="legacyStore.recipe.image"
        :class="{
            collapsed: collapsed,
            printable: legacyStore.recipe.printImage,
        }"
    >
        <img
            :alt="t('cookbook', 'Recipe image')"
            :src="legacyStore.recipe.imageUrl"
            @click="toggleCollapsed()"
        />
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useLegacyStore } from 'cookbook/store';

const legacyStore = useLegacyStore();

/**
 * @type {import('vue').Ref<boolean>}
 */
const collapsed = ref(true);

const toggleCollapsed = () => {
    collapsed.value = !collapsed.value;
};
</script>

<script>
export default {
    name: 'RecipeImages',
};
</script>

<style scoped>
div {
    display: block;
    width: 100%;
    margin-bottom: 1rem;
    text-align: center;
}

img {
    width: 100%;
    max-width: 950px;
    background-color: #bebdbd;
    cursor: pointer;
}

.collapsed {
    overflow: hidden;
    height: 40vh;
}

.collapsed img {
    display: block;
    margin: 0 auto;
    margin-top: 20vh;
    transform: translateY(-50%);
}

@media print {
    div:not(.printable) {
        display: none !important;
    }
}
</style>
