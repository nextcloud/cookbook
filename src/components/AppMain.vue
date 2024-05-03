<template>
    <NcContent app-name="cookbook">
        <AppNavi class="app-navigation" />
        <!--            eslint-disable vue/v-on-event-hyphenation -->
        <NcAppContent
            style="overflow-y: auto"
            :show-details="isAppContentDetailsVisible"
            @update:showDetails="onShowDetailsUpdated"
        >
            <template #list>
                <router-view v-if="useRecipesList" name="content-list" />
            </template>

            <NcAppContentDetails>
                <div v-if="isAppContentDetailsVisible">
                    <div>
                        <AppControls
                            :is-sidebar-open="isSidebarOpen"
                            @toggle-sidebar="toggleSidebarVisibility"
                        />

                        <div class="cookbook-app-content">
                            <!-- Main content -->
                            <router-view v-if="!useRecipesList" />
                            <router-view v-else name="main-view__active-list" />
                        </div>
                    </div>
                    <div
                        v-if="isMobile"
                        class="navigation-overlay"
                        :class="{ 'stay-open': isNavigationOpen }"
                        @click="closeNavigation"
                    />
                </div>
                <div v-else>
                    <NcEmptyContent
                        class="p-8"
                        :name="t('cookbook', 'No recipe selected')"
                        :description="
                            t('cookbook', 'Select recipe to show details.')
                        "
                    >
                        <!-- For whatever reason the new slot syntax is not working :S -->
                        <!-- <template #icon>-->
                        <!-- eslint-disable-next-line vue/no-deprecated-slot-attribute-->
                        <template slot="icon">
                            <RecipeIcon />
                        </template>
                    </NcEmptyContent>
                </div>
            </NcAppContentDetails>
        </NcAppContent>
        <dialogs-wrapper />
        <SettingsDialog />

        <!-- Sidebar -->
        <router-view
            name="sidebar"
            :is-open="isSidebarOpen"
            @close="hideSidebar"
        />
    </NcContent>
</template>

<script setup>
import {
    computed,
    getCurrentInstance,
    onMounted,
    onUnmounted,
    provide,
    ref,
    watch,
} from 'vue';
import NcAppContent from '@nextcloud/vue/dist/Components/NcAppContent.js';
import NcAppContentDetails from '@nextcloud/vue/dist/Components/NcAppContentDetails.js';
import NcContent from '@nextcloud/vue/dist/Components/NcContent.js';
import NcEmptyContent from '@nextcloud/vue/dist/Components/NcEmptyContent.js';
import AppNavi from 'cookbook/components/AppNavi.vue';
import AppControls from 'cookbook/components/AppControls/AppControls.vue';
import SettingsDialog from 'cookbook/components/Modals/SettingsDialog.vue';
import RecipeIcon from 'vue-material-design-icons/FormatListChecks.vue';

import { emit as emitNC, subscribe, unsubscribe } from '@nextcloud/event-bus';
import RecipeRepository from 'cookbook/js/Repositories/RecipeRepository';
import { useIsMobile } from 'cookbook/composables/useIsMobile';
import { useRoute } from 'vue-router/composables';
import { useStore } from 'cookbook/store';
import { goToRecipeParent } from 'cookbook/js/utils/navigation';
import ListStyle from 'cookbook/js/Enums/ListStyle';
import RouteName from 'cookbook/js/Enums/RouteName';

const log = getCurrentInstance().proxy.$log;
const isMobile = useIsMobile();

const route = useRoute();
const store = useStore();

// Dependency injection here!
// Provide the recipeRepository as a global property
const recipeRepository = new RecipeRepository();
provide('RecipeRepository', recipeRepository);
provide('Store', store);

/**
 * @type {import('vue').Ref<boolean>}
 */
const isNavigationOpen = ref(false);

/**
 * @type {import('vue').Ref<boolean>}
 */
const isSidebarOpen = ref(false);

/**
 * True if the app should use the list of recipes instead of the grid view.
 * @type {import('vue').ComputedRef<boolean>}
 */
const useRecipesList = computed(
    () => store.state.localSettings.recipesListStyle === ListStyle.List,
);

/**
 * True when an element in the list is selected. Required by `NCAppContent` for showing/hiding list in mobile view.
 * @type {import('vue').Ref<boolean>}
 */
const showRecipe = ref(true);

/**
 * The known recipes in the cookbook.
 * @type {import('vue').Ref<Array>}
 */
const recipes = ref([]);

// previously there was this commented section in this component. I leave it here for reference:
// watch: {
//     // This might be handy when routing of Vue components needs fixing.
//     // $route(to, from) {
//     //     this.$log.debug(
//     //         this.$window.isSameBaseRoute(from.fullPath, to.fullPath)
//     //     )
//     // },
// },

/**
 * If the route is updated, check if the recipe-details should be shown
 */
watch(
    () => route.name,
    (name) => {
        showRecipe.value = [
            RouteName.ShowRecipeInIndex,
            RouteName.ShowRecipeInCategory,
            RouteName.ShowRecipeInNames,
            RouteName.ShowRecipeInTags,
            RouteName.ShowRecipeInGeneralSearch,
            RouteName.EditRecipeInIndex,
            RouteName.EditRecipeInCategory,
            RouteName.EditRecipeInGeneralSearch,
            RouteName.EditRecipeInNames,
            RouteName.EditRecipeInTags,
        ].includes(name);
    },
);

const isAppContentDetailsVisible = computed(() => {
    const val =
        showRecipe.value &&
        [
            RouteName.ShowRecipeInIndex,
            RouteName.ShowRecipeInCategory,
            RouteName.ShowRecipeInNames,
            RouteName.ShowRecipeInTags,
            RouteName.ShowRecipeInGeneralSearch,
            RouteName.EditRecipeInIndex,
            RouteName.EditRecipeInCategory,
            RouteName.EditRecipeInGeneralSearch,
            RouteName.EditRecipeInNames,
            RouteName.EditRecipeInTags,
        ].includes(route.name);
    return !useRecipesList.value || val;
});

// Methods
/**
 * Listen for event-bus events about the app navigation opening and closing
 */
const updateAppNavigationOpen = ({ open }) => {
    isNavigationOpen.value = open;
};

/**
 * Close main app navigation.
 */
const closeNavigation = () => {
    emitNC('toggle-navigation', { open: false });
};

/**
 * Hide the sidebar.
 */
function hideSidebar() {
    isSidebarOpen.value = false;
}

/**
 * Toggle the visibility of the sidebar between visible and hidden.
 */
function toggleSidebarVisibility() {
    isSidebarOpen.value = !isSidebarOpen.value;
    emitNC('toggle-navigation', {
        open: false,
    });
}

// Vue lifecycle
onMounted(() => {
    log.info('AppMain mounted');
    subscribe('navigation-toggled', updateAppNavigationOpen);
    loadAll();
});

onUnmounted(() => {
    unsubscribe('navigation-toggled', updateAppNavigationOpen);
});

/**
 * If the list of recipes is currently being fetched from the server.
 * @type {import('vue').Ref<boolean>}
 */
const isLoadingRecipeList = ref(false);

/**
 * Load all recipes from the database
 */
const loadAll = () => {
    isLoadingRecipeList.value = true;
    recipeRepository
        .getRecipes()
        .then((response) => {
            recipes.value = response;

            // Always set page name last
            store.dispatch('setPage', { page: 'index' });
        })
        .catch(() => {
            // Always set page name last
            store.dispatch('setPage', { page: 'index' });
        })
        .finally(() => {
            isLoadingRecipeList.value = false;
        });
};

/**
 * Handles the requested update of the details' visibility.
 * @param {bool} show - if details are/should be shown.
 */
function onShowDetailsUpdated(show) {
    if (show) return;
    showRecipe.value = false;
    // Navigate to parent route
    goToRecipeParent(route);
}
</script>

<script>
export default {
    name: 'AppMain',
};
</script>

<style lang="scss">
@import '/src/assets/css/main';
</style>

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
        content: ' [' attr(href) '] ';
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

.content-list {
    height: 100%;
    padding: 0 4px;
    overflow-y: auto;
}

.content-list__search {
    position: sticky;
    z-index: 1;
    top: 0;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    padding: 4px;
    background-color: var(--color-main-background-translucent);
    column-gap: 0.5rem;
    padding-inline-start: 50px;

    .search-input {
        min-width: 180px;
        flex: 1 1 auto;
    }
}
</style>
