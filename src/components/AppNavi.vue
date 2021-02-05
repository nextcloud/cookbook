<template>
<!-- This component should ideally not have a conflicting name with AppNavigation from the nextcloud/vue package -->
    <AppNavigation>
        <ActionButton
            id="hide-navigation"
            icon="icon-menu"
            class="action-button"
            :ariaLabel="t('cookbook', 'Open navigation')"
            @click="toggleNavigation()"
        >{{ t('cookbook', 'Hide navigation') }}</ActionButton>
        <router-link :to="'/recipe/create'">
            <AppNavigationNew class="create" :text="t('cookbook', 'Create recipe')" />
        </router-link>
        <ul>
            <ActionInput
                class="download"
                @submit="downloadRecipe"
                :disabled="downloading ? 'disabled' : null"
                :icon="downloading ? 'icon-loading-small' : 'icon-download'">
                    {{ t('cookbook', 'Download recipe from URL') }}
            </ActionInput>

            <AppNavigationItem :title="t('cookbook', 'All recipes')" icon="icon-category-organization" :to="'/'">
                <AppNavigationCounter slot="counter">{{ totalRecipeCount }}</AppNavigationCounter>
            </AppNavigationItem>
            <AppNavigationItem :title="t('cookbook', 'Uncategorized recipes')" icon="icon-category-organization" :to="'/category/_/'">
                <AppNavigationCounter slot="counter">{{ uncatRecipes }}</AppNavigationCounter>
            </AppNavigationItem>
            <AppNavigationCaption :title="t('cookbook', 'Categories')" >
                <template slot="actions">
                    <ActionButton icon="icon-rename" @click="toggleCategoryRenaming">
                        {{ t('cookbook', 'Toggle editing') }}
                    </ActionButton>
                </template>
            </AppNavigationCaption>
            <AppNavigationItem v-for="(cat,idx) in categories"
                :key="cat+idx"
                :ref="'app-navi-cat-'+idx"
                :title="cat.name"
                :icon="categoryUpdating[idx] ? 'icon-loading-small': 'icon-category-files'"
                :allowCollapse="true"
                :to="'/category/'+cat.name"
                @update:open="categoryOpen(idx)"
                :editable="catRenamingEnabled"
                :editPlaceholder="t('cookbook','Enter new category name')"
                @update:title="(val) => { categoryUpdateName(idx,val) }"
            >
                <AppNavigationCounter slot="counter">{{ cat.recipeCount }}</AppNavigationCounter>
                <template>
                    <AppNavigationItem class="recipe" v-for="(rec,idy) in cat.recipes"
                        :key="idx+'-'+idy"
                        :title="rec.name"
                        :to="'/recipe/'+rec.recipe_id"
                        :icon="$store.state.loadingRecipe===parseInt(rec.recipe_id) || !rec.recipe_id ? 'icon-loading-small' : 'icon-file'"
                    />
                </template>
            </AppNavigationItem>
        </ul>
        <AppNavigationSettings :open="true">
            <div id="app-settings">
                <fieldset>
                    <ul>
                        <li>
                            <ActionButton
                                class="button"
                                :icon="scanningLibrary ? 'icon-loading-small' : 'icon-history'"
                                @click="reindex()"
                                :title="t('cookbook', 'Rescan library')"
                            />
                        </li>
                        <li>
                            <label class="settings-input">{{ t('cookbook', 'Recipe folder') }}</label>
                            <input type="text" :value="recipeFolder" @click="pickRecipeFolder" :placeholder="t('cookbook', 'Please pick a folder')">
                        </li>
                        <li>
                            <label class="settings-input">
                                {{ t('cookbook', 'Update interval in minutes') }}
                            </label>
                            <input type="number" class="input settings-input" v-model="updateInterval" placeholder="0">
                        </li>
                        <li>
                            <input type="checkbox" class="checkbox" v-model="printImage" id="recipe-print-image">
                            <label for="recipe-print-image">
                                {{ t('cookbook', 'Print image with recipe') }}
                            </label>
                        </li>
                    </ul>
                </fieldset>
            </div>
        </AppNavigationSettings>
    </AppNavigation>
</template>

<script>
import axios from '@nextcloud/axios'
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'
import ActionInput from '@nextcloud/vue/dist/Components/ActionInput'
import AppNavigation from '@nextcloud/vue/dist/Components/AppNavigation'
import AppNavigationCounter from '@nextcloud/vue/dist/Components/AppNavigationCounter'
import AppNavigationItem from '@nextcloud/vue/dist/Components/AppNavigationItem'
import AppNavigationNew from '@nextcloud/vue/dist/Components/AppNavigationNew'
import AppNavigationSettings from '@nextcloud/vue/dist/Components/AppNavigationSettings'
import AppNavigationSpacer from '@nextcloud/vue/dist/Components/AppNavigationSpacer'
import Vue from 'vue'

import AppNavigationCaption from './AppNavigationCaption'

export default {
    name: 'AppNavi',
    components: {
        ActionButton,
        ActionInput,
        AppNavigation,
        AppNavigationCaption,
        AppNavigationCounter,
        AppNavigationItem,
        AppNavigationNew,
        AppNavigationSettings,
        AppNavigationSpacer,
    },
    data () {
        return {
            catRenamingEnabled: false,
            categories: [],
            downloading: false,
            isCategoryUpdating: [],
            printImage: false,
            recipeFolder: "",
            scanningLibrary: false,
            uncatRecipes: 0,
            // By setting the reset value initially to true, it will skip one watch event
            // (the one when config is loaded at page load)
            resetInterval: true,
            resetPrintImage: true,
            updateInterval: 0,
        }
    },
    computed: {
        totalRecipeCount () {
            let total = this.uncatRecipes
            for (let i=0; i<this.categories.length; i++) {
                total += this.categories[i].recipeCount
            }
            return total
        },
        // Computed property to watch the Vuex state. If there are more in the
        // future, consider using the Vue mapState helper
        refreshRequired () {
            return this.$store.state.appNavigation.refreshRequired
        },
        categoryUpdating () {
            return this.isCategoryUpdating
        }
    },
    watch: {
        printImage: function(newVal, oldVal) {
            // Avoid infinite loop on page load and when reseting value after failed submit
            if (this.resetPrintImage) {
                this.resetPrintImage = false
                return
            }
            axios({
                url: this.$window.baseUrl + '/config',
                method: 'POST',
                data: { 'print_image': newVal ? 1 : 0 }
            })
                .then((response) => {
                        // Should this check the response of the query? To catch some errors that redirect the page
                })
                .catch((e) => {
                    alert(t('cookbook', 'Could not set preference for image printing'));
                    this.resetPrintImage = true
                    this.printImage = oldVal
                })
        },
        // Register a method hook for navigation refreshing
        refreshRequired: function(newVal, oldVal) {
            if (newVal != oldVal && newVal == true) {
                this.getCategories()
            }
        },
        updateInterval: function(newVal, oldVal) {
            // Avoid infinite loop on page load and when reseting value after failed submit
            if (this.resetInterval) {
                this.resetInterval = false
                return
            }
            axios({
                url: this.$window.baseUrl + '/config',
                method: 'POST',
                data: { 'update_interval': newVal }
                })
                .then((response) => {
                    // Should this check the response of the query? To catch some errors that redirect the page
                })
                .catch((e) => {
                    alert(t('cookbook', 'Could not set recipe update interval to {interval}', { interval: newVal }))
                    this.resetInterval = true
                    this.updateInterval = oldVal
                })
        }
    },
    methods: {
        /**
         * Enable renaming of categories.
         */
        toggleCategoryRenaming: function() {
            this.catRenamingEnabled = !this.catRenamingEnabled
        },
        /**
         * Initial setup
         */
        setup: function() {
            axios({
                url: this.$window.baseUrl + '/config',
                method: 'GET',
                data: null,
                })
                .then((response) => {
                    let config = response.data
                    this.resetPrintImage = false;
                    if(config) {
                        this.printImage = config['print_image'];
                        this.updateInterval = config['update_interval'];
                        this.recipeFolder = config['folder'];
                    } else {
                        alert(t('cookbook', 'Loading config failed'))
                    }
                })
                .catch((e) => {
                    alert(t('cookbook', 'Loading config failed'))
                })
        },

        /**
         * Opens a category
         */
        categoryOpen: function(idx) {
            if (!this.categories[idx].recipes.length || this.categories[idx].recipes[0].id) {
                // Recipes have already been loaded
                return
            }
            let cat = this.categories[idx]
            let $this = this
            Vue.set(this.isCategoryUpdating, idx, true)

            axios.get(this.$window.baseUrl + '/api/category/'+cat.name)
                .then(function(response) {
                    cat.recipes = response.data
                })
                .catch(function(e) {
                    cat.recipes = []
                    alert(t('cookbook', 'Failed to load category {category} recipes', {"category": cat.name}))
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
        categoryUpdateName: function(idx, newName) {
            if (!this.categories[idx]) {
                return
            }
            Vue.set(this.isCategoryUpdating, idx, true)
            let oldName = this.categories[idx].name
            let $this = this

            this.$store.dispatch('updateCategoryName', { categoryNames: [oldName, newName] })
                .then(function (response) {
                    $this.categories[idx].name = newName
                    $this.$root.$emit('categoryRenamed', [newName, oldName])
                })
                .catch(function(e) {
                    alert(t('cookbook', 'Failed to update name of category \"{category}\"', {"category": oldName}))
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
        downloadRecipe: function(e) {
            this.downloading = true
            let $this = this
            axios({
                url: this.$window.baseUrl + '/import',
                method: 'POST',
                data: 'url=' + e.target[1].value
            })
                .then(function(response) {
                    let recipe = response.data
                    $this.downloading = false
                    $this.$window.goTo('/recipe/' + recipe.id)
                    e.target[1].value = ''
                    // Refresh left navigation pane to display changes
                    $this.$store.dispatch('setAppNavigationRefreshRequired', { isRequired: true })
                })
                .catch(function(e) {
                    $this.downloading = false
                    alert(t('cookbook', e.request.responseJSON))
                })
        },

        /**
         * Fetch and display recipe categories
         */
        getCategories: function() {
            let $this = this
            axios.get(this.$window.baseUrl + '/categories')
                .then(function(response) {
                    let json = response.data || []
                    // Reset the old values
                    $this.uncatRecipes = 0
                    $this.categories = []
                    $this.isCategoryUpdating = []

                    for (let i=0; i<json.length; i++) {
                        if (json[i].name === '*') {
                            $this.uncatRecipes = parseInt(json[i].recipe_count)
                        } else {
                            $this.categories.push({
                                name: json[i].name,
                                recipeCount: parseInt(json[i].recipe_count),
                                recipes: [{ id: 0, name: t('cookbook', 'Loading category recipes …') }],
                            })
                            $this.isCategoryUpdating.push(false)
                        }
                    }
                    $this.$nextTick(() => {
                        for (let i=0; i<$this.categories.length; i++) {
                            // Reload recipes in open categories
                            if (!$this.$refs['app-navi-cat-'+i]) {
                                continue
                            }
                            if ($this.$refs['app-navi-cat-'+i][0].opened) {
                                console.log("Reloading recipes in "+$this.$refs['app-navi-cat-'+i][0].title)
                                $this.categoryOpen(i)
                            }
                        }
                        // Refreshing component data has been finished
                        $this.$store.dispatch('setAppNavigationRefreshRequired', { isRequired: false })
                    })
                })
                .catch(function(e) {
                    alert(t('cookbook', 'Failed to fetch categories'))
                    if (e && e instanceof Error) {
                        throw e
                    }
                })
        },

        /**
         * Select a recipe folder using the Nextcloud file picker
         */
        pickRecipeFolder: function(e) {
            OC.dialogs.filepicker(
                t('cookbook', 'Path to your recipe collection'),
                (path) => {
                    let $this = this
                    this.$store.dispatch('updateRecipeDirectory', { dir: path })
                        .then(() => {
                            $this.recipeFolder = path
                            if($this.$route.path != '/') {
                                $this.$router.push('/')
                            }
                        })
                        .catch((e) => {
                            alert(t('cookbook', 'Could not set recipe folder to {path}', { path: path }))
                        })
                },
                false,
                'httpd/unix-directory',
                true
            )
        },

        /**
         * Reindex all recipes
         */
        reindex: function () {
            let $this = this
            if (this.scanningLibrary) {
                // No repeat clicks until we're done
                return
            }
            this.scanningLibrary = true
            axios({
                url: this.$window.baseUrl + '/reindex',
                method: 'POST'
            })
                .then(() => {
                    $this.scanningLibrary = false
                    console.log("Library reindexing complete")
                    $this.getCategories()
                    if (['index', 'search'].indexOf(this.$store.state.page) > -1) {
                        // This refreshes the current router view in case items in it changed during reindex
                        $this.$router.go()
                    }
                })
                .catch(function(e) {
                    $this.scanningLibrary = false
                    console.log("Library reindexing failed!")
                })
        },

        /**
         * Set loading recipe index to show the loading icon
         */
        setLoadingRecipe: function(id) {
            this.$store.dispatch('setLoadingRecipe', { recipe: id })
        },

        /**
         * Toggle the left navigation pane
         */
        toggleNavigation: function() {
            this.$store.dispatch('setAppNavigationVisible', { isVisible: !this.$store.state.appNavigation.visible })
        },
    },
    mounted () {
        this.setup()
        this.getCategories()
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
    padding-left: 0px;
}

>>> .app-navigation-entry .app-navigation-entry__children .app-navigation-entry
{
    /* Let's not waste space in front of the recipe if we're only using the icon to show loading */
    padding-left: 0px;
}

.app-navigation-entry:hover li.recipe {
        box-shadow: inset 4px 0 rgba(255, 255, 255, 1);
    }

>>> .app-navigation-entry.recipe:hover,
>>> .app-navigation-entry.router-link-exact-active {
    opacity: 1;
    box-shadow: inset 4px 0 var(--color-primary);
}

#app-settings .button {
    padding: 0;
    height: 44px;
    border-radius: var(--border-radius);
    z-index: 2;
}

#app-settings input[type="text"],
#app-settings input[type="number"],
#app-settings .button {
    width: 100%;
    display: block;
}

#hide-navigation {
    height: 44px;
    display: none;
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
