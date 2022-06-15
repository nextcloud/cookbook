<template>
    <!-- eslint-disable-next-line vue/no-v-html -->
    <li v-html="tool"></li>
</template>

<script>
export default {
    name: "RecipeTool",
    props: {
        tool: {
            type: String,
            default: "",
        },
    },
}
</script>

<style scoped>
li {
    margin-left: 1.25em;
}
</style>
