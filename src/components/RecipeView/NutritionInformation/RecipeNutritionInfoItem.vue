<template>
    <li class="mb-2">
        <div class="title mb-0">
            {{ title }}
        </div>
        <div class="data">
            {{ data }}
        </div>
    </li>
</template>

<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    data: {
        type: String,
        required: true,
    },
});
</script>

<script>
export default {
    name: 'RecipeNutritionInfoItem',
};
</script>

<style scoped>
li .title {
    color: var(--color-main-text);
    font-weight: bolder;
}
</style>
