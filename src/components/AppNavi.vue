<template>
    <!-- This component should ideally not have a conflicting name with AppNavigation from the nextcloud/vue package -->
    <NcAppNavigation>
        <router-link :to="'/recipe/create'">
            <NcAppNavigationNew
                class="create"
                :text="t('cookbook', 'Create recipe')"
            >
                <template #icon><plus-icon :size="20" /> </template>
            </NcAppNavigationNew>
        </router-link>

        <template slot="list">
            <NcActionInput
                class="download"
                :disabled="downloading ? 'disabled' : null"
                :icon="downloading ? 'icon-loading-small' : 'icon-download'"
                @submit="downloadRecipe"
            >
                {{ t("cookbook", "Download recipe from URL") }}
            </NcActionInput>

            <NcAppNavigationItem
                :title="t('cookbook', 'All recipes')"
                icon="icon-category-organization"
                :to="'/'"
            >
                <template #counter>
                    <nc-counter-bubble>{{
                        totalRecipeCount
                    }}</nc-counter-bubble>
                </template>
            </NcAppNavigationItem>

            <NcAppNavigationItem
                :title="t('cookbook', 'Uncategorized recipes')"
                icon="icon-category-organization"
                :to="'/category/_/'"
            >
                <template #counter>
                    <nc-counter-bubble>{{ uncatRecipes }}</nc-counter-bubble>
                </template>
            </NcAppNavigationItem>

            <AppNavigationCaption
                v-if="loading.categories || categories.length > 0"
                :title="t('cookbook', 'Categories')"
                :loading="loading.categories"
            >
            </AppNavigationCaption>

            <NcAppNavigationItem
                v-for="(cat, idx) in categories"
                :key="cat + idx"
                :ref="'app-navi-cat-' + idx"
                :title="cat.name"
                :icon="'icon-category-files'"
                :to="'/category/' + cat.name"
                :editable="true"
                :edit-label="t('cookbook', 'Rename')"
                :edit-placeholder="t('cookbook', 'Enter new category name')"
                @update:open="categoryOpen(idx)"
                @update:title="
                    (val) => {
                        categoryUpdateName(idx, val)
                    }
                "
            >
                <template #counter>
                    <nc-counter-bubble>{{ cat.recipeCount }}</nc-counter-bubble>
                </template>
            </NcAppNavigationItem>
        </template>

        <template slot="footer">
            <AppSettings
                :scanning-library="scanningLibrary"
                @reindex="reindex"
            />
        </template>
    </NcAppNavigation>
</template>

<script>
import NcActionInput from "@nextcloud/vue/dist/Components/NcActionInput"
import NcAppNavigation from "@nextcloud/vue/dist/Components/NcAppNavigation"
import NcAppNavigationItem from "@nextcloud/vue/dist/Components/NcAppNavigationItem"
import NcAppNavigationNew from "@nextcloud/vue/dist/Components/NcAppNavigationNew"
import NcCounterBubble from "@nextcloud/vue/dist/Components/NcCounterBubble"

import Vue from "vue"

import PlusIcon from "icons/Plus.vue"

import api from "cookbook/js/api-interface"
import helpers from "cookbook/js/helper"
import { showSimpleAlertModal } from "cookbook/js/modals"

import AppSettings from "./AppSettings.vue"
import AppNavigationCaption from "./AppNavigationCaption.vue"

export default {
    name: "AppNavi",
    components: {
        NcActionInput,
        NcAppNavigation,
        NcAppNavigationItem,
        NcAppNavigationNew,
        NcCounterBubble,
        AppSettings,
        AppNavigationCaption,
        PlusIcon,
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
            this.$log.debug("Calling totalRecipeCount")
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
                this.$log.debug("Calling getCategories from refreshRequired")
                this.getCategories()
            }
        },
    },
    mounted() {
        this.$log.info("AppNavi mounted")
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
        async categoryOpen(idx) {
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

            try {
                const response = await api.recipes.allInCategory(cat.name)
                cat.recipes = response.data
            } catch (e) {
                cat.recipes = []
                await showSimpleAlertModal(
                    // prettier-ignore
                    t("cookbook", "Failed to load category {category} recipes",
                        {
                            category: cat.name,
                        }
                    )
                )
                if (e && e instanceof Error) {
                    throw e
                }
            } finally {
                Vue.set($this.isCategoryUpdating, idx, false)
            }
        },

        /**
         * Updates the name of a category
         */
        async categoryUpdateName(idx, newName) {
            if (!this.categories[idx]) {
                return
            }
            Vue.set(this.isCategoryUpdating, idx, true)
            const oldName = this.categories[idx].name
            const $this = this

            try {
                await this.$store.dispatch("updateCategoryName", {
                    categoryNames: [oldName, newName],
                })
                $this.categories[idx].name = newName
                $this.$root.$emit("categoryRenamed", [newName, oldName])
            } catch (e) {
                await showSimpleAlertModal(
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
            } finally {
                Vue.set($this.isCategoryUpdating, idx, false)
            }
        },

        /**
         * Download and import the recipe at given URL
         */
        async downloadRecipe(e) {
            this.downloading = true
            const $this = this
            try {
                const response = await api.recipes.import(e.target[1].value)
                const recipe = response.data
                $this.downloading = false
                helpers.goTo(`/recipe/${recipe.id}`)
                // Refresh left navigation pane to display changes
                $this.$store.dispatch("setAppNavigationRefreshRequired", {
                    isRequired: true,
                })
            } catch (e2) {
                $this.downloading = false

                if (e2.response) {
                    if (e2.response.status >= 400 && e2.response.status < 500) {
                        if (e2.response.status === 409) {
                            // There was a recipe found with the same name

                            await showSimpleAlertModal(e2.response.data.msg)
                        } else {
                            await showSimpleAlertModal(e2.response.data)
                        }
                    } else {
                        // eslint-disable-next-line no-console
                        console.error(e2)
                        await showSimpleAlertModal(
                            // prettier-ignore
                            t("cookbook","The server reported an error. Please check.")
                        )
                    }
                } else {
                    // eslint-disable-next-line no-console
                    console.error(e2)
                    await showSimpleAlertModal(
                        // prettier-ignore
                        t("cookbook", "Could not query the server. This might be a network problem.")
                    )
                }
            }
        },

        /**
         * Fetch and display recipe categories
         */
        async getCategories() {
            this.$log.debug("Calling getCategories")
            const $this = this
            this.loading.categories = true
            try {
                const response = await api.categories.getAll()
                const json = response.data || []
                // Reset the old values
                $this.uncatRecipes = 0
                $this.categories = []
                $this.isCategoryUpdating = []

                for (let i = 0; i < json.length; i++) {
                    if (json[i].name === "*") {
                        $this.uncatRecipes = parseInt(json[i].recipe_count, 10)
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
                                    $this.$refs[`app-navi-cat-${i}`][0].title
                                }`
                            )
                            $this.categoryOpen(i)
                        }
                    }
                    // Refreshing component data has been finished
                    $this.$store.dispatch("setAppNavigationRefreshRequired", {
                        isRequired: false,
                    })
                })
            } catch (e) {
                await showSimpleAlertModal(
                    t("cookbook", "Failed to fetch categories")
                )
                if (e && e instanceof Error) {
                    throw e
                }
            } finally {
                $this.loading.categories = false
            }
        },

        /**
         * Reindex all recipes
         */
        reindex() {
            this.$log.debug("Calling reindex")
            const $this = this
            if (this.scanningLibrary) {
                // No repeat clicks until we're done
                return
            }
            this.scanningLibrary = true
            api.recipes
                .reindex()
                .then(() => {
                    $this.scanningLibrary = false
                    // eslint-disable-next-line no-console
                    console.log("Library reindexing complete")
                    if (
                        ["index", "search"].indexOf(this.$store.state.page) > -1
                    ) {
                        // This refreshes the current router view in case items in it changed during reindex
                        $this.$router.go()
                    } else {
                        this.$log.debug("Calling getCategories from reindex")
                        $this.getCategories()
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
@media print {
    * {
        display: none !important;
    }
}
</style>
