<template>
    <li>
        <VueShowdown :markdown="tool" class="markdown-tool" />
    </li>
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

.markdown-tool:deep(a) {
    text-decoration: underline;
}
</style>
