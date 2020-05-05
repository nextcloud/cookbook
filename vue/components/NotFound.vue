<template>
    <h2>{{ t('The page was not found') }}</h2>
</template>

<script>
export default {
    name: 'NotFound',
    mounted () {
        this.$store.dispatch('setPage', { page: 'notfound' })
    }
}
</script>

<style>

h2 {
    margin: 1rem;
}

</style>
