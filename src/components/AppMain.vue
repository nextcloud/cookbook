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
        /* This is left here as an example in case the routes need to be debugged again
        '$route' (to, from) {
            console.log(this.$window.isSameBaseRoute(from.fullPath, to.fullPath))
        },
        */
    },
}
</script>

<style>

#app-content {
    min-width: calc(100% - 300px);
}

#app-content-wrapper {
    flex-wrap: wrap;
}

@media print {
    #app-content-wrapper {
        display: block !important;
        padding: 0 !important;
        overflow: visible !important;
    }
    #app-content {
        margin-left: 0 !important;
    }
}

</style>
