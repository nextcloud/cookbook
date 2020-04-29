<template>
    <li>{{ tool }}</li>
</template>

<script>
export default {
    name: 'RecipeTool',
    props: ['tool'],

}
</script>

<style scoped>

li {
    margin-left: 1.25em;
}

</style>
