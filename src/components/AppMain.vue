<template>
    <Content app-name="cookbook">
        <AppNavi class="app-navigation" />
        <AppContent>
            <div>
                <AppControls />
                <router-view></router-view>
            </div>
        </AppContent>
    </Content>
</template>

<script>
import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import AppControls from './AppControls'
import AppNavi from './AppNavi'
import Content from '@nextcloud/vue/dist/Components/Content'
export default {
    name: 'Main',
    components: {
        AppContent,
        AppControls,
        AppNavi,
        Content
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

@media print {
    #app-content-vue {
        display: block !important;
        padding: 0 !important;
        overflow: visible !important;
        margin-left: 0 !important;
    }
    #app-navigation-vue {
        display: none !important;
    }
    #header {
        display: none !important;
    }
    a:link:after,
    a:visited:after {
        content:" [" attr(href) "] ";
    }
}

</style>
