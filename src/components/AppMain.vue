<template>
    <NcContent app-name="cookbook">
        <AppNavi class="app-navigation" />
        <NcAppContent>
            <div>
                <AppControls />
                <div class="cookbook-app-content">
                    <router-view></router-view>
                </div>
            </div>
            <div
                v-if="isMobile"
                class="navigation-overlay"
                :class="{ 'stay-open': isNavigationOpen }"
                @click="closeNavigation"
            />
        </NcAppContent>
        <dialogs-wrapper></dialogs-wrapper>
        <SettingsDialog />
    </NcContent>
</template>

<script>
import isMobile from "@nextcloud/vue/dist/Mixins/isMobile"
import NcAppContent from "@nextcloud/vue/dist/Components/NcAppContent"
import NcContent from "@nextcloud/vue/dist/Components/NcContent"
import AppControls from "cookbook/components/AppControls/AppControls.vue"
import { emit, subscribe, unsubscribe } from "@nextcloud/event-bus"
import AppNavi from "./AppNavi.vue"
import SettingsDialog from "./Modals/SettingsDialog.vue"

// todo
export default {
    name: "AppMain",
    components: {
        NcAppContent,
        AppControls,
        AppNavi,
        SettingsDialog,
        // eslint-disable-next-line vue/no-reserved-component-names
        NcContent,
    },
    mixins: [isMobile],
    data() {
        return {
            isNavigationOpen: false,
        }
    },
    watch: {
        // This might be handy when routing of Vue components needs fixing.
        // $route(to, from) {
        //     this.$log.debug(
        //         this.$window.isSameBaseRoute(from.fullPath, to.fullPath)
        //     )
        // },
    },
    mounted() {
        this.$log.info("AppMain mounted")
        subscribe("navigation-toggled", this.updateAppNavigationOpen)
    },
    unmounted() {
        unsubscribe("navigation-toggled", this.updateAppNavigationOpen)
    },
    methods: {
        /**
         * Listen for event-bus events about the app navigation opening and closing
         */
        updateAppNavigationOpen({ open }) {
            this.isNavigationOpen = open
        },
        closeNavigation() {
            emit("toggle-navigation", { open: false })
        },
    },
}
</script>

<!--<script>-->
<!--export default {-->
<!--    name: 'AppMain'-->
<!--};-->
<!--</script>-->

<style lang="scss" scoped>
.app-navigation {
    /* Content has z-index 1000 */
    z-index: 2000;
}

.cookbook-app-content {
    position: relative;
    z-index: 0;
}

/**
 * The open event is only emitted when the animation stops
 * In order to match their animation, we need to start fading in the overlay
 * as soon as the .app-navigation--close` class goes away
 * using a sibling selector
 *
 * We still need to listen for events to help us close the overlay.
 * We cannot set `display: none` in an animation.
 * We need to start fading out when the .app-navigation--close` class comes back,
 * and use the close event that fired when the animation stops to reset
 * `display: none`
 */
.navigation-overlay {
    position: absolute;
    /* Top bar has z-index 2 */
    z-index: 3;
    display: none;
    animation: fade-out var(--animation-quick) forwards;
    background: rgba(0, 0, 0, 0.5);
    cursor: pointer;
    inset: 0;
}

.navigation-overlay.stay-open {
    display: block;
}

#app-navigation-vue:not(.app-navigation--close)
    + #app-content-vue
    .navigation-overlay {
    display: block;
    animation: fade-in var(--animation-quick) forwards;
}

@keyframes fade-in {
    0% {
        opacity: 0;
    }
    100% {
        opacity: 1;
    }
}
@keyframes fade-out {
    0% {
        opacity: 1;
    }
    100% {
        opacity: 0;
    }
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

    body {
        position: static;
    }

    #content-vue {
        position: static;
        width: 100%;
        height: initial;
        border-radius: 0;
        margin: 0;
    }
}
</style>
