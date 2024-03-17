<template>
    <li>
        <span class="title"> {{ title }} </span>
        <span class="data">
            {{ data }}
        </span>
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
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    padding: 6px 0;
    span {
        width: 50%;
    }

    & + li {
        border-top: 1px solid var(--color-border-dark);
    }
}

li .title {
    margin-bottom: 0;
    color: var(--color-main-text);
    font-weight: 250;
}

li .data {
    font-weight: 400;
    text-align: right;
}
</style>
