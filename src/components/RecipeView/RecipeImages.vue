<template>
    <div
        v-if="$store.state.recipe.image"
        :class="{
            collapsed: collapsed,
            printable: $store.state.recipe.printImage,
        }"
    >
        <img
            :alt="t('cookbook', 'Recipe image')"
            :src="$store.state.recipe.imageUrl"
            @click="toggleCollapsed()"
        />
    </div>
</template>

<script setup>
import { ref } from 'vue';

/**
 * @type {import('vue').Ref<boolean>}
 */
const collapsed = ref(true);

const toggleCollapsed = () => {
    this.collapsed = !this.collapsed;
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
