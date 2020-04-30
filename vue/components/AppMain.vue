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
}
</script>

<style>

</style>
