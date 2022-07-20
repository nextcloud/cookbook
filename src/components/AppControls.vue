<template>
    <div class="wrapper">
        <!-- Use $store.state.page for page matching to make sure everything else has been set beforehand! -->
        <Breadcrumbs class="breadcrumbs" root-icon="icon-category-organization">
            <Breadcrumb
                :title="t('cookbook', 'Home')"
                :to="'/'"
                :disable-drop="true"
            />
            <!-- INDEX PAGE -->
            <Breadcrumb
                v-if="isIndex"
                class="active no-arrow"
                :title="t('cookbook', 'All recipes')"
                :disable-drop="true"
            />
            <Breadcrumb
                v-if="isIndex"
                class="no-arrow"
                title=""
                :disable-drop="true"
            >
                <!-- This is clumsy design but the component cannot display just one input element on the breadcrumbs bar -->
                <ActionInput
                    icon="icon-quota"
                    :value="filterValue"
                    @update:value="updateFilters"
                    >{{ t("cookbook", "Filter") }}</ActionInput
                >
                <ActionInput icon="icon-search" @submit="search">{{
                    t("cookbook", "Search")
                }}</ActionInput>
            </Breadcrumb>
            <!-- SEARCH PAGE -->
            <Breadcrumb
                v-if="isSearch"
                class="not-link"
                :title="searchTitle"
                :disable-drop="true"
            />
            <Breadcrumb
                v-if="isSearch && $route.params.value"
                class="active"
                :title="
                    $route.params.value === '_'
                        ? 'None'
                        : decodeURIComponent($route.params.value)
                "
                :disable-drop="true"
            />
            <!-- RECIPE PAGES -->
            <!-- Edit recipe -->
            <Breadcrumb
                v-if="isEdit"
                class="not-link"
                :title="t('cookbook', 'Edit recipe')"
                :disable-drop="true"
            />
            <Breadcrumb
                v-if="isEdit"
                class="active"
                :title="$store.state.recipe.name"
                :disable-drop="true"
            >
            </Breadcrumb>
            <!-- Create new recipe -->
            <Breadcrumb
                v-else-if="isCreate"
                class="active"
                :title="t('cookbook', 'New recipe')"
                :disable-drop="true"
            />
            <!-- View recipe -->
            <Breadcrumb
                v-if="isRecipe"
                class="active"
                :title="$store.state.recipe.name"
                :disable-drop="true"
            >
            </Breadcrumb>
            <!-- Is the app loading? -->
            <Breadcrumb
                v-if="isLoading"
                class="active no-arrow"
                title=""
                :disable-drop="true"
            >
                <ActionButton
                    icon="icon-loading-small"
                    :aria-label="t('cookbook', 'Loading app')"
                />
            </Breadcrumb>
            <!-- Is a recipe loading? -->
            <Breadcrumb
                v-else-if="isLoadingRecipe"
                class="active no-arrow"
                title=""
                :disable-drop="true"
            >
                <ActionButton
                    icon="icon-loading-small"
                    :aria-label="t('cookbook', 'Loading recipe')"
                />
            </Breadcrumb>
            <!-- No recipe found -->
            <Breadcrumb
                v-else-if="recipeNotFound"
                class="active no-arrow"
                :title="t('cookbook', 'Recipe not found')"
                :disable-drop="true"
            />
            <!-- No page found -->
            <Breadcrumb
                v-else-if="pageNotFound"
                class="active no-arrow"
                :title="t('cookbook', 'Page not found')"
                :disable-drop="true"
            />
        </Breadcrumbs>
        <Actions>
            <ActionButton
                v-if="isRecipe"
                key="reloadRecipeView"
                :icon="
                    $store.state.reloadingRecipe === parseInt($route.params.id)
                        ? 'icon-loading-small'
                        : 'icon-history'
                "
                :aria-label="t('cookbook', 'Reload recipe')"
                @click="reloadRecipeView()"
                >{{ t("cookbook", "Reload recipe") }}</ActionButton
            >
            <ActionButton
                v-if="isEdit"
                key="reloadRecipeEdit"
                :icon="
                    $store.state.reloadingRecipe === parseInt($route.params.id)
                        ? 'icon-loading-small'
                        : 'icon-history'
                "
                :aria-label="t('cookbook', 'Reload recipe')"
                @click="reloadRecipeEdit()"
                >{{ t("cookbook", "Reload recipe") }}</ActionButton
            >
            <ActionButton
                v-if="isRecipe"
                key="editRecipe"
                icon="icon-rename"
                :aria-label="t('cookbook', 'Edit recipe')"
                @click="goToRecipe($store.state.recipe.id)"
            >
                {{ t("cookbook", "Edit recipe") }}
            </ActionButton>
            <ActionButton
                v-if="isRecipe"
                key="printRecipe"
                icon="icon-category-office"
                :aria-label="t('cookbook', 'Print recipe')"
                @click="printRecipe()"
                >{{ t("cookbook", "Print recipe") }}</ActionButton
            >
            <ActionButton
                v-if="isRecipe"
                key="deleteRecipe"
                icon="icon-delete"
                :aria-label="t('cookbook', 'Delete recipe')"
                @click="deleteRecipe()"
                >{{ t("cookbook", "Delete recipe") }}</ActionButton
            >
            <ActionButton
                v-if="isEdit || isCreate"
                key="save"
                :icon="
                    $store.state.savingRecipe
                        ? 'icon-loading-small'
                        : 'icon-checkmark'
                "
                :aria-label="t('cookbook', 'Save changes')"
                :title="t('cookbook', 'Save changes')"
                class="has-tooltip"
                @click="saveChanges()"
            />
        </Actions>
    </div>
</template>

<script>
import helpers from "cookbook/js/helper"

import ActionButton from "@nextcloud/vue/dist/Components/ActionButton"
/* import Actions from "@nextcloud/vue/dist/Components/Actions" */
import ActionInput from "@nextcloud/vue/dist/Components/ActionInput"
import Breadcrumbs from "@nextcloud/vue/dist/Components/Breadcrumbs"
// import Breadcrumbs from "./Breadcrumbs"
import Breadcrumb from "@nextcloud/vue/dist/Components/Breadcrumb"
import Actions from "./ResponsiveActions.vue"

export default {
    name: "AppControls",
    components: {
        ActionButton,
        ActionInput,
        Breadcrumbs,
        Breadcrumb,
        Actions,
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
    /* Sticky is better than fixed because fixed takes the element out of flow,
     which breaks the height, putting elements underneath */
    position: sticky;

    /* This is competing with the recipe instructions which have z-index: 1 */
    z-index: 2;

    /* The height of the nextcloud header */
    top: var(--header-height);
    display: flex;

    /*width: calc(100% - 40px);*/
    width: 100%;
    flex-direction: row;
    padding-left: 4px;
    padding-left: 40px;
    border-bottom: 1px solid var(--color-border);
    background-color: var(--color-main-background);
}

.active {
    cursor: default !important;
    font-weight: bold;
}

.breadcrumbs {
    /* flex-basis: 100%; */
    width: auto;
    flex-grow: 0;
    overflow-x: hidden;
}

.button-container {
    display: flex;
    flex-direction: row;
    /*& /deep/ button.action-button {
        padding-right: 0;
    }*/
}

.no-arrow::before {
    content: "" !important;
}

@media print {
    * {
        display: none !important;
    }
}
</style>

<style>
.wrapper .breadcrumbs .breadcrumb__crumbs {
    min-width: unset;
}
@media print {
    .vue-tooltip {
        display: none !important;
    }
}
</style>
