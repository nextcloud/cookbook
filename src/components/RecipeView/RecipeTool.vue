<template>
    <li>
        <VueShowdown :markdown="tool" class="markdown-tool" />
    </li>
</template>

<script setup>
defineProps({
    tool: {
        type: String,
        default: '',
    },
});
</script>

<script>
export default {
    name: 'RecipeTool',
};
</script>

<style scoped>
li {
    margin-left: 1.25em;
}

.markdown-tool:deep(a) {
    text-decoration: underline;
}
</style>
