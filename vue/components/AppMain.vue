<template>
    <div id="app">
        <AppNavi />
        <router-view></router-view>
    </div>
</template>

<script>
import AppNavi from './AppNavi'
export default {
    components: [ AppNavi ]
}
</script>

<style>

</style>
