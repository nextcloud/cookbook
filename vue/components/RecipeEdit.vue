<template>

</template>

<script>
export default {
    name: 'RecipeEdit',
    props: ['id'],
    data () {
        return {

        }
    },
    mounted () {
        if (this.id) {
            this.$store.dispatch('setPage', 'edit')
        } else {
            this.$store.dispatch('setPage', 'create')
        }
    },
}
</script>

<style>

</style>
