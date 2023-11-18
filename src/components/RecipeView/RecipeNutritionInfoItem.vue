<template>
    <li>
        <div class="title">
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
li {
    margin-bottom: 0.5em;
    margin-left: 1.25em;
}

li .title {
    margin-bottom: 0;
    color: var(--color-main-text);
    font-weight: bolder;
}
</style>
