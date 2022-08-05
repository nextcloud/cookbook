<template>
    <div class="wrapper">
        <!-- Use $store.state.page for page matching to make sure everything else has been set beforehand! -->
        <div class="status-header">
            <ModeIndicator v-if="isSearch" :title="searchTitle" />
            <ModeIndicator
                v-else-if="isEdit"
                :title="t('cookbook', 'Editing recipe')"
            />
            <ModeIndicator
                v-else-if="isRecipe"
                :title="t('cookbook', 'Viewing recipe')"
            />
            <!-- INDEX PAGE -->
            <Location v-if="isIndex" :title="t('cookbook', 'All recipes')" />
            <Location
                v-else-if="isSearch && $route.params.value"
                :title="
                    $route.params.value === '_'
                        ? 'None'
                        : decodeURIComponent($route.params.value)
                "
            />
            <!-- Recipe view / edit -->
            <Location
                v-else-if="isEdit || isRecipe"
                :title="$store.state.recipe.name"
            />
            <!-- Is app loading? -->
            <Location
                v-else-if="isLoading"
                :title="t('cookbook', 'Loading app')"
            />
            <!-- Is a recipe loading? -->
            <Location
                v-else-if="isLoadingRecipe"
                :title="t('cookbook', 'Loading recipe')"
            />
            <!-- No recipe found -->
            <Location
                v-else-if="recipeNotFound"
                :title="t('cookbook', 'Recipe not found')"
            />
            <!-- No page found -->
            <Location
                v-else-if="pageNotFound"
                :title="t('cookbook', 'Page not found')"
            />
            <!-- Create new recipe -->
            <Location
                v-else-if="isCreate"
                :title="t('cookbook', 'Creating new recipe')"
            />
        </div>
        {{/* Primary buttons */}}
        <SimpleButton
            v-if="isRecipe"
            type="primary"
            :aria-label="t('cookbook', 'Edit')"
            @click="goToRecipe($store.state.recipe.id)"
        >
            <template #icon>
                <PencilIcon :size="20" />
            </template>
            {{ t("cookbook", "Edit") }}
        </SimpleButton>
        <SimpleButton
            v-if="isEdit || isCreate"
            type="primary"
            :aria-label="t('cookbook', 'Save')"
            @click="saveChanges()"
        >
            <template #icon>
                <LoadingIcon
                    v-if="$store.state.savingRecipe"
                    :size="20"
                    class="animation-rotate"
                />
                <CheckmarkIcon v-else :size="20" />
            </template>
            {{ t("cookbook", "Save") }}
        </SimpleButton>
        <!-- This is clumsy design but the component cannot display just one input element on the breadcrumbs bar -->
        <Actions
            v-if="isIndex"
            default-icon="icon-search-white"
            :menu-title="t('cookbook', 'Search')"
            :primary="true"
        >
            <ActionInput
                icon="icon-quota"
                :value="filterValue"
                @update:value="updateFilters"
            >
                {{ t("cookbook", "Filter") }}
            </ActionInput>
            <ActionInput icon="icon-search" @submit="search">
                {{ t("cookbook", "Search") }}
            </ActionInput>
        </Actions>
        {{/* Overflow buttons (3-dot menu) */}}
        <Actions
            v-if="isRecipe || isEdit"
            :force-menu="true"
            class="overflow-menu"
        >
            <ActionButton
                v-if="isEdit"
                :icon="
                    $store.state.reloadingRecipe === parseInt($route.params.id)
                        ? 'icon-loading-small'
                        : 'icon-history'
                "
                class="action-button"
                :aria-label="t('cookbook', 'Reload recipe')"
                @click="reloadRecipeEdit()"
            >
                {{ t("cookbook", "Reload recipe") }}
            </ActionButton>
            <ActionButton
                v-if="isRecipe"
                :icon="
                    $store.state.reloadingRecipe === parseInt($route.params.id)
                        ? 'icon-loading-small'
                        : 'icon-history'
                "
                class="action-button"
                :aria-label="t('cookbook', 'Reload recipe')"
                @click="reloadRecipeView()"
            >
                {{ t("cookbook", "Reload recipe") }}
            </ActionButton>
            <ActionButton
                v-if="isRecipe"
                class="action-button"
                :aria-label="t('cookbook', 'Print recipe')"
                @click="printRecipe()"
            >
                <template #icon=""><printer-icon :size="20" /></template>
                {{ t("cookbook", "Print recipe") }}
            </ActionButton>
            <ActionButton
                v-if="isRecipe"
                icon="icon-delete"
                class="action-button"
                :aria-label="t('cookbook', 'Delete recipe')"
                @click="deleteRecipe()"
            >
                {{ t("cookbook", "Delete recipe") }}
            </ActionButton>
        </Actions>
    </div>
</template>

<script>
import helpers from "cookbook/js/helper"

import Actions from "@nextcloud/vue/dist/Components/Actions"
import ActionButton from "@nextcloud/vue/dist/Components/ActionButton"
// Cannot use `Button` else get `vue/no-reserved-component-names` eslint errors
import SimpleButton from "@nextcloud/vue/dist/Components/Button"
import ActionInput from "@nextcloud/vue/dist/Components/ActionInput"

import PencilIcon from "icons/Pencil.vue"
import LoadingIcon from "icons/Loading.vue"
import CheckmarkIcon from "icons/Check.vue"
import PrinterIcon from "icons/Printer.vue"

import Location from "./Location.vue"
import ModeIndicator from "./ModeIndicator.vue"

export default {
    name: "AppControls",
    components: {
        Actions,
        ActionButton,
        ActionInput,
        PrinterIcon,
        SimpleButton,
        PencilIcon,
        LoadingIcon,
        CheckmarkIcon,
        ModeIndicator,
        Location,
    },
    data() {
        return {
            filterValue: "",
        }
    },
    computed: {
        isCreate() {
            return this.$store.state.page === "create"
        },
        isEdit() {
            if (this.isLoadingRecipe) {
                return false // Do not show both at the same time
            }
            // Editing requires that a recipe was found
            return !!(
                this.$store.state.page === "edit" && this.$store.state.recipe
            )
        },
        isIndex() {
            if (this.isLoadingRecipe) {
                return false // Do not show both at the same time
            }
            return this.$store.state.page === "index"
        },
        isLoading() {
            //  The page is being loaded
            return this.$store.state.page === null
        },
        isLoadingRecipe() {
            //  A recipe is being loaded
            return !!this.$store.state.loadingRecipe
        },
        isRecipe() {
            if (this.isLoadingRecipe) {
                return false // Do not show both at the same time
            }
            // Viewing recipe requires that one was found
            return !!(
                this.$store.state.page === "recipe" && this.$store.state.recipe
            )
        },
        isSearch() {
            if (this.isLoadingRecipe) {
                return false // Do not show both at the same time
            }
            return this.$store.state.page === "search"
        },
        pageNotFound() {
            return this.$store.state.page === "notfound"
        },
        recipeNotFound() {
            // Editing or viewing recipe was attempted, but no recipe was found
            return (
                ["edit", "recipe"].indexOf(this.$store.state.page) !== -1 &&
                !this.$store.state.recipe
            )
        },
        searchTitle() {
            if (this.$route.name === "search-category") {
                return t("cookbook", "Category")
            }
            if (this.$route.name === "search-name") {
                return t("cookbook", "Recipe name")
            }
            if (this.$route.name === "search-tags") {
                return t("cookbook", "Tags")
            }
            return t("cookbook", "Search for recipes")
        },
    },
    methods: {
        deleteRecipe() {
            // Confirm delete
            if (
                // eslint-disable-next-line no-alert
                !window.confirm(
                    // prettier-ignore
                    t("cookbook","Are you sure you want to delete this recipe?")
                )
            ) {
                return
            }
            this.$store
                .dispatch("deleteRecipe", { id: this.$store.state.recipe.id })
                .then(() => {
                    helpers.goTo("/")
                })
                .catch((e) => {
                    // eslint-disable-next-line no-alert
                    alert(t("cookbook", "Delete failed"))
                    if (e && e instanceof Error) {
                        throw e
                    }
                })
        },
        printRecipe() {
            window.print()
        },
        reloadRecipeEdit() {
            this.$root.$emit("reloadRecipeEdit")
        },
        reloadRecipeView() {
            this.$root.$emit("reloadRecipeView")
        },
        saveChanges() {
            this.$root.$emit("saveRecipe")
        },
        search(e) {
            helpers.goTo(`/search/${e.target[1].value}`)
        },
        updateFilters(e) {
            this.filterValue = e
            this.$root.$emit("applyRecipeFilter", e)
        },
        goToRecipe(id) {
            helpers.goTo(`/recipe/${id}/edit`)
        },
    },
}
</script>

<style scoped>
.wrapper {
    /* 44px is the height of nextcloud/vue button (not exposed as a variable :[ ) */
    --nc-button-size: 44px;

    --vertical-padding: 8px;

    /* Sticky is better than fixed because fixed takes the element out of flow,
     which breaks the height, putting elements underneath */
    position: sticky;

    /* This is competing with the recipe instructions which have z-index: 1 */
    z-index: 2;

    /* The height of the nextcloud header */
    top: var(--header-height);
    display: flex;
    width: 100%;
    /* Make sure the wrapper is always at least as tall as the tallest element
     * we expect (primary button) to prevent flickering when loading, etc. */
    min-height: calc(44px + 2 * var(--vertical-padding));
    flex-direction: row;

    padding: var(--vertical-padding) 1rem var(--vertical-padding)
        calc(44px + 2 * var(--vertical-padding));
    border-bottom: 1px solid var(--color-border);
    background-color: var(--color-main-background);
    gap: 8px;
}

.status-header {
    display: flex;
    /* Allow the title to shrink*/
    min-width: 0;
    flex-basis: 0;
    flex-direction: column;
    flex-grow: 1;
    flex-shrink: 1;
    align-items: flex-start;
    justify-content: space-around;
}

.mode-indicator {
    font-size: 0.9em;
    line-height: 0.9em;
}

.location-wrapper {
    width: 100%;
}

.location-wrapper:only-child {
    display: flex;
    flex: 1;
    flex-direction: column;
    justify-content: center;
}

/* The .status-header is justify-content: space-around. If there is no
 * .mode-indicator, this will put the .location at the top. Override to place in
 * the center */
.status-header /deep/ .location {
    /* Don't let the location go wider than the space available */
    width: 100%;
    margin: 0;
    font-size: 1.2em;
    line-height: 1em;
    /* overflow-x: hidden breaks overflow-y: visible
    https://stackoverflow.com/questions/6421966/css-overflow-x-visible-and-overflow-y-hidden-causing-scrollbar-issue */
    overflow-x: clip;
    overflow-y: visible;
    /* Show ... when overflowing */
    text-overflow: ellipsis;
    white-space: nowrap;
}

.animation-rotate {
    animation: rotate var(--animation-duration, 0.8s) linear infinite;
}

@media print {
    * {
        display: none !important;
    }
}
</style>

<style>
@media print {
    .vue-tooltip {
        display: none !important;
    }
}
</style>
