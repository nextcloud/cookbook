<template>
    <!-- This component should ideally not have a conflicting name with AppNavigation from the nextcloud/vue package -->
    <AppNavigation>
        <router-link :to="'/recipe/create'">
            <AppNavigationNew
                class="create"
                :text="t('cookbook', 'Create recipe')"
                button-id="cookbook_new_cookbook"
                :button-class="['create', 'icon-add']"
            />
        </router-link>

        <template slot="list">
            <ActionInput
                class="download"
                :disabled="downloading ? 'disabled' : null"
                :icon="downloading ? 'icon-loading-small' : 'icon-download'"
                @submit="downloadRecipe"
            >
                {{ t("cookbook", "Download recipe from URL") }}
            </ActionInput>

            <AppNavigationItem
                :title="t('cookbook', 'All recipes')"
                icon="icon-category-organization"
                :to="'/'"
            >
                <AppNavigationCounter slot="counter">{{
                    totalRecipeCount
                }}</AppNavigationCounter>
            </AppNavigationItem>

            <AppNavigationItem
                :title="t('cookbook', 'Uncategorized recipes')"
                icon="icon-category-organization"
                :to="'/category/_/'"
            >
                <AppNavigationCounter slot="counter">{{
                    uncatRecipes
                }}</AppNavigationCounter>
            </AppNavigationItem>

            <AppNavigationCaption
                v-if="loading.categories || categories.length > 0"
                :title="t('cookbook', 'Categories')"
                :loading="loading.categories"
            >
                <template slot="actions">
                    <ActionButton
                        icon="icon-rename"
                        @click="toggleCategoryRenaming"
                    >
                        {{ t("cookbook", "Toggle editing") }}
                    </ActionButton>
                </template>
            </AppNavigationCaption>

            <AppNavigationItem
                v-for="(cat, idx) in categories"
                :key="cat + idx"
                :ref="'app-navi-cat-' + idx"
                :title="cat.name"
                :icon="categoryUpdating[idx] ? '' : 'icon-category-files'"
                :loading="categoryUpdating[idx]"
                :allow-collapse="true"
                :to="'/category/' + cat.name"
                :editable="catRenamingEnabled"
                :edit-label="t('cookbook', 'Rename')"
                :edit-placeholder="t('cookbook', 'Enter new category name')"
                @update:open="categoryOpen(idx)"
                @update:title="
                    (val) => {
                        categoryUpdateName(idx, val)
                    }
                "
            >
                <AppNavigationCounter
                    v-if="!catRenamingEnabled"
                    slot="counter"
                    >{{ cat.recipeCount }}</AppNavigationCounter
                >
                <template>
                    <AppNavigationItem
                        v-for="(rec, idy) in cat.recipes"
                        :key="idx + '-' + idy"
                        class="recipe"
                        :title="rec.name"
                        :to="'/recipe/' + rec.recipe_id"
                        :icon="
                            $store.state.loadingRecipe ===
                                parseInt(rec.recipe_id) || !rec.recipe_id
                                ? 'icon-loading-small'
                                : 'icon-file'
                        "
                    />
                </template>
            </AppNavigationItem>
        </template>

        <template slot="footer">
            <AppSettings
                :scanning-library="scanningLibrary"
                @reindex="reindex"
            />
        </template>
    </AppNavigation>
</template>

<script>
import axios from "@nextcloud/axios"
import ActionButton from "@nextcloud/vue/dist/Components/ActionButton"
import ActionInput from "@nextcloud/vue/dist/Components/ActionInput"
import AppNavigation from "@nextcloud/vue/dist/Components/AppNavigation"
import AppNavigationCounter from "@nextcloud/vue/dist/Components/AppNavigationCounter"
import AppNavigationItem from "@nextcloud/vue/dist/Components/AppNavigationItem"
import AppNavigationNew from "@nextcloud/vue/dist/Components/AppNavigationNew"
import Vue from "vue"
import AppSettings from "./AppSettings.vue"
import AppNavigationCaption from "./AppNavigationCaption.vue"

export default {
    name: "AppNavi",
    components: {
        ActionButton,
        ActionInput,
        AppNavigation,
        AppNavigationCounter,
        AppNavigationItem,
        AppNavigationNew,
        AppSettings,
        AppNavigationCaption,
    },
    data() {
        return {
            catRenamingEnabled: false,
            categories: [],
            downloading: false,
            isCategoryUpdating: [],
            loading: { categories: true },
            scanningLibrary: false,
            uncatRecipes: 0,
        }
    },
    computed: {
        totalRecipeCount() {
            let total = this.uncatRecipes
            for (let i = 0; i < this.categories.length; i++) {
                total += this.categories[i].recipeCount
            }
            return total
        },
        // Computed property to watch the Vuex state. If there are more in the
        // future, consider using the Vue mapState helper
        refreshRequired() {
            return this.$store.state.appNavigation.refreshRequired
        },
        categoryUpdating() {
            return this.isCategoryUpdating
        },
    },
    watch: {
        // Register a method hook for navigation refreshing
        refreshRequired(newVal, oldVal) {
            if (newVal !== oldVal && newVal === true) {
                this.getCategories()
            }
        },
    },
    mounted() {
        this.getCategories()
    },
    methods: {
        /**
         * Enable renaming of categories.
         */
        toggleCategoryRenaming() {
            this.catRenamingEnabled = !this.catRenamingEnabled
        },

        /**
         * Opens a category
         */
        categoryOpen(idx) {
            if (
                !this.categories[idx].recipes.length ||
                this.categories[idx].recipes[0].id
            ) {
                // Recipes have already been loaded
                return
            }
            const cat = this.categories[idx]
            const $this = this
            Vue.set(this.isCategoryUpdating, idx, true)

            axios
                .get(`${this.$window.baseUrl}/api/category/${cat.name}`)
                .then((response) => {
                    cat.recipes = response.data
                })
                .catch((e) => {
                    cat.recipes = []
                    // eslint-disable-next-line no-alert
                    alert(
                        // prettier-ignore
                        t("cookbook","Failed to load category {category} recipes",
                            {
                                category: cat.name,
                            }
                        )
                    )
                    if (e && e instanceof Error) {
                        throw e
                    }
                })
                .then(() => {
                    // finally
                    Vue.set($this.isCategoryUpdating, idx, false)
                })
        },

        /**
         * Updates the name of a category
         */
        categoryUpdateName(idx, newName) {
            if (!this.categories[idx]) {
                return
            }
            Vue.set(this.isCategoryUpdating, idx, true)
            const oldName = this.categories[idx].name
            const $this = this

            this.$store
                .dispatch("updateCategoryName", {
                    categoryNames: [oldName, newName],
                })
                .then(() => {
                    $this.categories[idx].name = newName
                    $this.$root.$emit("categoryRenamed", [newName, oldName])
                })
                .catch((e) => {
                    // eslint-disable-next-line no-alert
                    alert(
                        // prettier-ignore
                        t("cookbook",'Failed to update name of category "{category}"',
                            {
                                category: oldName,
                            }
                        )
                    )
                    if (e && e instanceof Error) {
                        throw e
                    }
                })
                .then(() => {
                    // finally
                    Vue.set($this.isCategoryUpdating, idx, false)
                })
        },

        /**
         * Download and import the recipe at given URL
         */
        downloadRecipe(e) {
            this.downloading = true
            const $this = this
            axios({
                url: `${this.$window.baseUrl}/import`,
                method: "POST",
                data: `url=${e.target[1].value}`,
            })
                .then((response) => {
                    const recipe = response.data
                    $this.downloading = false
                    $this.$window.goTo(`/recipe/${recipe.id}`)
                    // Refresh left navigation pane to display changes
                    $this.$store.dispatch("setAppNavigationRefreshRequired", {
                        isRequired: true,
                    })
                })
                .catch((e2) => {
                    $this.downloading = false

                    if (e2.response) {
                        if (
                            e2.response.status >= 400 &&
                            e2.response.status < 500
                        ) {
                            if (e2.response.status == 409) {
                                // There was a recipe found with the same name
                                
                                // eslint-disable-next-line no-alert
                                alert(e2.response.data.msg)
                            } else {
                                // eslint-disable-next-line no-alert
                                alert(e2.response.data)
                            }
                        } else {
                            console.error(e2)
                            alert(
                                // prettier-ignore
                                t("cookbook","The server reported an error. Please check.")
                            )
                        }
                    } else {
                        console.error(e2)
                        alert(
                            // prettier-ignore
                            t("cookbook", "Could not query the server. This might be a network problem.")
                        )
                    }
                })
        },

        /**
         * Fetch and display recipe categories
         */
        getCategories() {
            const $this = this
            this.loading.categories = true
            axios
                .get(`${this.$window.baseUrl}/categories`)
                .then((response) => {
                    const json = response.data || []
                    // Reset the old values
                    $this.uncatRecipes = 0
                    $this.categories = []
                    $this.isCategoryUpdating = []

                    for (let i = 0; i < json.length; i++) {
                        if (json[i].name === "*") {
                            $this.uncatRecipes = parseInt(
                                json[i].recipe_count,
                                10
                            )
                        } else {
                            $this.categories.push({
                                name: json[i].name,
                                recipeCount: parseInt(json[i].recipe_count, 10),
                                recipes: [
                                    {
                                        id: 0,
                                        // prettier-ignore
                                        name: t("cookbook","Loading category recipes …"),
                                    },
                                ],
                            })
                            $this.isCategoryUpdating.push(false)
                        }
                    }
                    $this.$nextTick(() => {
                        for (let i = 0; i < $this.categories.length; i++) {
                            // Reload recipes in open categories
                            if (!$this.$refs[`app-navi-cat-${i}`]) {
                                // eslint-disable-next-line no-continue
                                continue
                            }
                            if ($this.$refs[`app-navi-cat-${i}`][0].opened) {
                                // eslint-disable-next-line no-console
                                console.log(
                                    `Reloading recipes in ${
                                        $this.$refs[`app-navi-cat-${i}`][0]
                                            .title
                                    }`
                                )
                                $this.categoryOpen(i)
                            }
                        }
                        // Refreshing component data has been finished
                        $this.$store.dispatch(
                            "setAppNavigationRefreshRequired",
                            { isRequired: false }
                        )
                    })
                })
                .catch((e) => {
                    // eslint-disable-next-line no-alert
                    alert(t("cookbook", "Failed to fetch categories"))
                    if (e && e instanceof Error) {
                        throw e
                    }
                })
                .then(() => {
                    // finally
                    $this.loading.categories = false
                })
        },

        /**
         * Reindex all recipes
         */
        reindex() {
            const $this = this
            if (this.scanningLibrary) {
                // No repeat clicks until we're done
                return
            }
            this.scanningLibrary = true
            axios({
                url: `${this.$window.baseUrl}/reindex`,
                method: "POST",
            })
                .then(() => {
                    $this.scanningLibrary = false
                    // eslint-disable-next-line no-console
                    console.log("Library reindexing complete")
                    $this.getCategories()
                    if (
                        ["index", "search"].indexOf(this.$store.state.page) > -1
                    ) {
                        // This refreshes the current router view in case items in it changed during reindex
                        $this.$router.go()
                    }
                })
                .catch(() => {
                    $this.scanningLibrary = false
                    // eslint-disable-next-line no-console
                    console.log("Library reindexing failed!")
                })
        },

        /**
         * Set loading recipe index to show the loading icon
         */
        setLoadingRecipe(id) {
            this.$store.dispatch("setLoadingRecipe", { recipe: id })
        },

        /**
         * Toggle the left navigation pane
         */
        toggleNavigation() {
            this.$store.dispatch("setAppNavigationVisible", {
                isVisible: !this.$store.state.appNavigation.visible,
            })
        },
    },
}
</script>

<style scoped>
>>> .app-navigation-new button {
    min-height: 44px;
    background-image: var(--icon-add-000);
    background-repeat: no-repeat;
}

>>> .app-navigation-entry.recipe {
    /* Let's not waste space in front of the recipe if we're only using the icon to show loading */
    padding-left: 0;
}

/* stylelint-disable selector-class-pattern */
>>> .app-navigation-entry
    .app-navigation-entry__children
    .app-navigation-entry {
    /* Let's not waste space in front of the recipe if we're only using the icon to show loading */
    padding-left: 0;
}
/* stylelint-enable selector-class-pattern */

.app-navigation-entry:hover .recipe {
    box-shadow: inset 4px 0 rgba(255, 255, 255, 1);
}

>>> .app-navigation-entry.recipe:hover,
>>> .app-navigation-entry.router-link-exact-active {
    box-shadow: inset 4px 0 var(--color-primary);
    opacity: 1;
}

#hide-navigation {
    display: none;
    height: 44px;
}
#hide-navigation .action-button {
    padding-right: 0 !important;
}

@media only screen and (max-width: 1024px) {
    #hide-navigation {
        display: block;
    }
}
@media print {
    * {
        display: none !important;
    }
}
</style>

<style>
@media (min-width: 1024px) {
    .app-navigation-toggle {
        display: none;
    }
}
</style>
