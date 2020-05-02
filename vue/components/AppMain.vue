<template>
    <div id="app">
        <AppNavi id="app-navigation" />
        <div id="app-content">
            <div id="app-content-wrapper">
                <AppControls />
                <router-view></router-view>
            </div>
        </div>
    </div>
</template>

<script>
import AppControls from './AppControls'
import AppNavi from './AppNavi'
export default {
    name: 'Main',
    components: {
        AppControls,
        AppNavi,
    },
    watch: {
        /*
        '$route' (to, from) {
            // Reset page before each route update
            this.$store.dispatch('setPage', { page: null })
        },
        */
    },
}
</script>

<style>

</style>
