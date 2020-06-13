<template>
<!-- This component should ideally not have a conflicting name with AppNavigation from the nextcloud/vue package -->
    <AppNavigation>
        <router-link :to="'/recipe/create'">
            <AppNavigationNew class="create" :text="t('cookbook', 'Create recipe')" />
        </router-link>
        <ul>
            <ActionInput
                class="download"
                @submit="downloadRecipe"
                :disabled="downloading ? 'disabled' : null"
                :icon="downloading ? 'icon-loading-small' : 'icon-download'">
                    {{ t('cookbook', 'Recipe URL') }}
            </ActionInput>
            <AppNavigationItem :title="t('cookbook', 'All recipes')" icon="icon-category-organization" :to="'/'">
                <AppNavigationCounter slot="counter">{{ totalRecipeCount }}</AppNavigationCounter>
            </AppNavigationItem>
            <AppNavigationItem v-for="(cat,idx) in categories"
                :key="cat+idx"
                :ref="'app-navi-cat-'+idx"
                :title="cat.name"
                icon="icon-category-files"
                :allowCollapse="true"
                :to="'/category/'+cat.name"
                @update:open="categoryOpen(idx)"
            >
                <AppNavigationCounter slot="counter">{{ cat.recipeCount }}</AppNavigationCounter>
                <template>
                    <AppNavigationItem class="recipe" v-for="(rec,idy) in cat.recipes"
                        :key="idx+'-'+idy"
                        :title="rec.name"
                        :to="'/recipe/'+rec.recipe_id"
                        :icon="$store.state.loadingRecipe===parseInt(rec.recipe_id) || !rec.recipe_id ? 'icon-loading-small' : null"
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
                            <div class="update">
                                <input type="number" class="input settings-input" v-model="updateInterval" placeholder="0">
                                <button class="icon-info" disabled="disabled" :title="t('cookbook', 'Last update: ')"></button>
                            </div>
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

import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'
import ActionInput from '@nextcloud/vue/dist/Components/ActionInput'
import AppNavigation from '@nextcloud/vue/dist/Components/AppNavigation'
import AppNavigationCaption from '@nextcloud/vue/dist/Components/AppNavigationCaption'
import AppNavigationCounter from '@nextcloud/vue/dist/Components/AppNavigationCounter'
import AppNavigationItem from '@nextcloud/vue/dist/Components/AppNavigationItem'
import AppNavigationNew from '@nextcloud/vue/dist/Components/AppNavigationNew'
import AppNavigationSettings from '@nextcloud/vue/dist/Components/AppNavigationSettings'
import AppNavigationSpacer from '@nextcloud/vue/dist/Components/AppNavigationSpacer'

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
            categories: [],
            downloading: false,
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
        }
    },
    watch: {
        printImage: function(newVal, oldVal) {
            // Avoid infinite loop on page load and when reseting value after failed submit
            if (this.resetPrintImage) {
                this.resetPrintImage = false
                return
            }
            var $this = this
            $.ajax({
                url: this.$window.baseUrl + '/config',
                method: 'POST',
                data: { 'print_image': newVal ? 1 : 0 }
            }).done(function (response) {
                // Should this check the response of the query? To catch some errors that redirect the page
            }).fail(function(e) {
                alert($this.t('cookbook', 'Could not set preference for image printing'));
                $this.resetPrintImage = true
                $this.printImage = oldVal
            })
        },
        updateInterval: function(newVal, oldVal) {
            // Avoid infinite loop on page load and when reseting value after failed submit
            if (this.resetInterval) {
                this.resetInterval = false
                return
            }
            var $this = this
            $.ajax({
                url: $this.$window.baseUrl + '/config',
                method: 'POST',
                data: { 'update_interval': newVal }
            }).done(function (response) {
                // Should this check the response of the query? To catch some errors that redirect the page
            }).fail(function(e) {
                alert($this.t('cookbook', 'Could not set recipe update interval to {interval}', { interval: newVal }))
                $this.resetInterval = true
                $this.updateInterval = oldVal
            })
        },
    },
    methods: {
        /**
         * Initial setup
         */
        setup: function() {
            let $this = this

            $.ajax({
                url: this.$window.baseUrl + '/config',
                method: 'GET',
                data: null,
            }).done(function (config) {
                $this.resetPrintImage = false;
                $this.printImage = config['print_image'];
                $this.updateInterval = config['update_interval'];
                $this.recipeFolder = config['folder'];

            }).fail(function(e) {
                alert($this.t('cookbook', 'Loading config failed'))
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
            $.get(this.$window.baseUrl + '/category/'+cat.name).done(function(json) {
                cat.recipes = json
            }).fail(function (jqXHR, textStatus, errorThrown) {
                cat.recipes = []
                alert($this.t('cookbook', 'Failed to load category '+cat.name+' recipes'))
                if (e && e instanceof Error) {
                    throw e
                }
            })
        },

        /**
         * Download and import the recipe at given URL
         */
        downloadRecipe: function(e) {
            let deferred = $.Deferred()
            let $this = this
            this.downloading = true
            $.ajax({
                url: this.$window.baseUrl + '/import',
                method: 'POST',
                data: 'url=' + e.target[1].value
            }).done(function (recipe) {
                $this.downloading = false
                $this.$window.goTo('/recipe/' + recipe.id)
                e.target[1].value = ''
                deferred.resolve()
            }).fail(function (jqXHR, textStatus, errorThrown) {
                $this.downloading = false
                deferred.reject(new Error(jqXHR.responseText))
                alert($this.t(jqXHR.responseJSON))
            })
            return deferred.promise()
        },

        /**
         * Fetch and display recipe categories
         */
        getCategories: function() {
            let $this = this
            $.get(this.$window.baseUrl + '/categories').done(function(json) {
                json = json || []
                // Reset the old values
                $this.uncatRecipes = 0
                $this.categories = []
                for (let i=0; i<json.length; i++) {
                    if (json[i].name === '*') {
                        $this.uncatRecipes = parseInt(json[i].recipe_count)
                    } else {
                        $this.categories.push({
                            name: json[i].name,
                            recipeCount: parseInt(json[i].recipe_count),
                            recipes: [{ id: 0, name: $this.t('cookbook', 'Loading category recipes...') }],
                        })
                    }
                }
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
            })
            .fail(function(e) {
                alert($this.t('cookbook', 'Failed to fetch categories'))
                if (e && e instanceof Error) {
                    throw e
                }
            })
        },

        /**
         * Select a recipe folder using the Nextcloud file picker
         */
        pickRecipeFolder: function(e) {
            let $this = this
            OC.dialogs.filepicker(
                this.t('cookbook', 'Path to your recipe collection'),
                function (path) {
                    $.ajax({
                        url: $this.$window.baseUrl + '/config',
                        method: 'POST',
                        data: { 'folder': path },
                    }).done(function () {
                        $this.loadAll()
                        .then(function() {
                            $this.$store.dispatch('setRecipe', { recipe: null })
                            $this.$window.goTo('/')
                            $this.recipeFolder = path
                        })
                    }).fail(function(e) {
                        alert($this.t('cookbook', 'Could not set recipe folder to {path}', { path: path }))
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
            if (this.scanningLibrary) {
                // No repeat clicks until we're done
                return
            }
            this.scanningLibrary = true
            var deferred = $.Deferred()
            var $this = this
            $.ajax({
                url: this.$window.baseUrl + '/reindex',
                method: 'POST'
            }).done(function () {
                deferred.resolve()
                $this.scanningLibrary = false
                console.log("Library reindexing complete")
                $this.getCategories()
                if (['index', 'search'].indexOf($this.$store.state.page) > -1) {
                    // This refreshes the current router view in case items in it changed during reindex
                    $this.$router.go()
                }
            }).fail(function (jqXHR, textStatus, errorThrown) {
                deferred.reject(new Error(jqXHR.responseText))
                $this.scanningLibrary = false
                console.log("Library reindexing failed!")
            })
            return deferred.promise()
        },

        /**
         * Set loading recipe index to show the loading icon
         */
        setLoadingRecipe: function(id) {
            this.$store.dispatch('setLoadingRecipe', { recipe: id })
        },
    },
    mounted () {
        this.setup()
        // Register a method hook for navigation refreshing
        // This component should only load once, but better safe than sorry...
        this.$root.$off('refreshNavigation')
        this.$root.$on('refreshNavigation', () => {
            this.getCategories()
        })
        this.getCategories()
    },
}

</script>

<style scoped>

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

.update > input {
    width: calc(100% - 0.5rem - 34px) !important;
    margin-right: 0.5rem;
    float: left;
}
.update > button {
    margin: 3px 0 !important;
    width: 34px !important;
    height: 34px !important;
    float: left;
}

@media print {
    * {
        display: none !important;
    }
}

</style>
