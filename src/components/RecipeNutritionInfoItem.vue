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

<script>
export default {
    name: 'RecipeNutritionInfoItem',
    props: {
        title: {
            type: String,
            required: true
        },
        data: {
            type: String,
            required: true
        }
    },

}
</script>

<style scoped>

li {
    margin-left: 1.25em;
    margin-bottom: .5em;
}

li .title {
    margin-bottom: 0em;
    color: var(--color-main-text);
    font-weight: bolder;
}

</style>
