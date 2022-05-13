<template>
    <Content app-name="cookbook">
        <AppNavi class="app-navigation" />
        <AppContent>
            <div>
                <AppControls />
                <div class="cookbook-app-content">
                    <router-view></router-view>
                </div>
            </div>
        </AppContent>
    </Content>
</template>

<script>
import AppContent from "@nextcloud/vue/dist/Components/AppContent"
import Content from "@nextcloud/vue/dist/Components/Content"
import AppControls from "./AppControls.vue"
import AppNavi from "./AppNavi.vue"

export default {
    name: "AppMain",
    components: {
        AppContent,
        AppControls,
        AppNavi,
        Content,
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

<style lang="scss" scoped>
.app-navigation {
    z-index: 1;
}

.cookbook-app-content {
    position: relative;
    z-index: 0;
}
</style>

<style>
@media print {
    #app-content-vue {
        display: block !important;
        overflow: visible !important;
        padding: 0 !important;
        margin-left: 0 !important;
    }

    #app-navigation-vue {
        display: none !important;
    }

    #header {
        display: none !important;
    }

    a:link::after,
    a:visited::after {
        content: " [" attr(href) "] ";
    }
}
</style>
