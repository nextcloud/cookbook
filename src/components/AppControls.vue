<template>
    <div class="wrapper">
        <!-- Use $store.state.page for page matching to make sure everything else has been set beforehand! -->
        <ActionButton id="show-navigation" icon="icon-menu" class="action-button" :ariaLabel="t('cookbook', 'Open navigation')" @click="toggleNavigation()" />
        <Breadcrumbs class="breadcrumbs" rootIcon="icon-category-organization">
            <Breadcrumb :title="t('cookbook', 'Home')" :to="'/'" :disableDrop="true" />
            <!-- INDEX PAGE -->
            <Breadcrumb v-if="isIndex" class="active no-arrow" :title="t('cookbook', 'All recipes')" :disableDrop="true" />
            <Breadcrumb v-if="isIndex" class="no-arrow" title="" :disableDrop="true">
                <!-- This is clumsy design but the component cannot display just one input element on the breadcrumbs bar -->
                <ActionInput icon="icon-quota" @update:value="updateFilters" :value="filterValue">{{ t('cookbook', 'Filter') }}</ActionInput>
                <ActionInput icon="icon-search" @submit="search">{{ t('cookbook', 'Search') }}</ActionInput>
            </Breadcrumb>
            <!-- SEARCH PAGE -->
            <Breadcrumb v-if="isSearch" class="not-link" :title="searchTitle" :disableDrop="true" />
            <Breadcrumb v-if="isSearch && $route.params.value" class="active" :title="$route.params.value=='_'?'None':decodeURIComponent($route.params.value)" :disableDrop="true" />
            <!-- RECIPE PAGES -->
            <!-- Edit recipe -->
            <Breadcrumb v-if="isEdit" class="not-link" :title="t('cookbook', 'Edit recipe')" :disableDrop="true" />
            <Breadcrumb v-if="isEdit" class="active" :title="$store.state.recipe.name" :disableDrop="true">
                <ActionButton
                    :icon="$store.state.reloadingRecipe===parseInt($route.params.id) ? 'icon-loading-small' : 'icon-history'"
                    class="action-button"
                    :ariaLabel="t('cookbook', 'Reload recipe')"
                    @click="reloadRecipeEdit()"
                >{{ t('cookbook', 'Reload recipe') }}</ActionButton>
            </Breadcrumb>
            <!-- Create new recipe -->
            <Breadcrumb v-else-if="isCreate" class="active" :title="t('cookbook', 'New recipe')" :disableDrop="true" />
            <Breadcrumb v-if="isEdit || isCreate" class="no-arrow" title="" :disableDrop="true">
                <ActionButton
                    :icon="$store.state.savingRecipe ? 'icon-loading-small' : 'icon-checkmark'"
                    class="action-button"
                    :ariaLabel="t('cookbook', 'Save changes')"
                    @click="saveChanges()"
                >{{ t('cookbook', 'Save changes') }}</ActionButton>
            </Breadcrumb>
            <!-- View recipe -->
            <Breadcrumb v-if="isRecipe" class="active" :title="$store.state.recipe.name" :disableDrop="true">
                <ActionButton
                    :icon="$store.state.reloadingRecipe===parseInt($route.params.id) ? 'icon-loading-small' : 'icon-history'"
                    class="action-button"
                    :ariaLabel="t('cookbook', 'Reload recipe')"
                    @click="reloadRecipeView()"
                >{{ t('cookbook', 'Reload recipe') }}</ActionButton>
            </Breadcrumb>
            <Breadcrumb v-if="isRecipe" class="no-arrow" title="" :disableDrop="true">
                <ActionButton
                    icon="icon-rename"
                    class="action-button"
                    :ariaLabel="t('cookbook', 'Edit recipe')"
                    @click="$window.goTo('/recipe/'+$store.state.recipe.id+'/edit')"
                >{{ t('cookbook', 'Edit recipe') }}</ActionButton>
            </Breadcrumb>
            <Breadcrumb v-if="isRecipe" class="no-arrow" title="" :disableDrop="true">
                <ActionButton
                    icon="icon-category-office"
                    class="action-button"
                    :ariaLabel="t('cookbook', 'Print recipe')"
                    @click="printRecipe()"
                >{{ t('cookbook', 'Print recipe') }}</ActionButton>
            </Breadcrumb>
            <Breadcrumb v-if="isRecipe" class="no-arrow" title="" :disableDrop="true">
                <ActionButton
                    icon="icon-delete"
                    class="action-button"
                    :ariaLabel="t('cookbook', 'Delete recipe')"
                    @click="deleteRecipe()"
                >{{ t('cookbook', 'Delete recipe') }}</ActionButton>
            </Breadcrumb>
            <!-- Is the app loading? -->
            <Breadcrumb v-if="isLoading" class="active no-arrow" :title="t('cookbook', 'App is loading')" :disableDrop="true">
                <ActionButton icon="icon-loading-small" :ariaLabel="t('cookbook', 'Loading…')" />
            </Breadcrumb>
            <!-- Is a recipe loading? -->
            <Breadcrumb v-else-if="isLoadingRecipe" class="active no-arrow" :title="t('cookbook', 'Loading recipe')" :disableDrop="true">
                <ActionButton icon="icon-loading-small" :ariaLabel="t('cookbook', 'Loading…')" />
            </Breadcrumb>
            <!-- No recipe found -->
            <Breadcrumb v-else-if="recipeNotFound" class="active no-arrow" :title="t('cookbook', 'Recipe not found')" :disableDrop="true" />
            <!-- No page found -->
            <Breadcrumb v-else-if="pageNotFound" class="active no-arrow" :title="t('cookbook', 'Page not found')" :disableDrop="true" />
        </Breadcrumbs>
    </div>

</template>

<script>
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'
import ActionInput from '@nextcloud/vue/dist/Components/ActionInput'
import axios from '@nextcloud/axios'
import Breadcrumbs from '@nextcloud/vue/dist/Components/Breadcrumbs'
import Breadcrumb from '@nextcloud/vue/dist/Components/Breadcrumb'

export default {
    name: 'AppControls',
    components: {
        ActionButton, ActionInput, Breadcrumbs, Breadcrumb
    },
    data () {
        return {
            filterValue: '',
        }
    },
    computed: {
        isCreate () {
            if (this.$store.state.page === 'create') {
                return true
            }
            return false
        },
        isEdit () {
            if (this.isLoadingRecipe) {
                return false // Do not show both at the same time
            }
            // Editing requires that a recipe was found
            if (this.$store.state.page === 'edit' && this.$store.state.recipe) {
                return true
            }
            return false
        },
        isIndex () {
            if (this.isLoadingRecipe) {
                return false // Do not show both at the same time
            }
            if (this.$store.state.page === 'index') {
                return true
            }
            return false
        },
        isLoading () {
            //  The page is being loaded
            if (this.$store.state.page === null) {
                return true
            }
            return false
        },
        isLoadingRecipe () {
            //  A recipe is being loaded
            if (this.$store.state.loadingRecipe) {
                return true
            }
            return false
        },
        isRecipe () {
            if (this.isLoadingRecipe) {
                return false // Do not show both at the same time
            }
            // Viewing recipe requires that one was found
            if (this.$store.state.page === 'recipe' && this.$store.state.recipe) {
                return true
            }
            return false
        },
        isSearch () {
            if (this.isLoadingRecipe) {
                return false // Do not show both at the same time
            }
            if (this.$store.state.page === 'search') {
                return true
            }
            return false
        },
        pageNotFound () {
            if (this.$store.state.page === 'notfound') {
                return true
            }
            return false
        },
        recipeNotFound () {
            // Editing or viewing recipe was attempted, but no recipe was found
            if (['edit', 'recipe'].indexOf(this.$store.state.page) !== -1
                && !this.$store.state.recipe) {
                return true
            }
            return false
        },
        searchTitle () {
            if (this.$route.name === 'search-category') {
                return t('cookbook', 'Category')
            } else if (this.$route.name === 'search-name') {
                return t('cookbook', 'Recipe name')
            } else if (this.$route.name === 'search-tags') {
                return t('cookbook', 'Tags')
            } else {
                return t('cookbook', 'Search for recipes')
            }
        }
    },
    methods: {
        deleteRecipe: function() {
            // Confirm delete
            if (!confirm(t('cookbook', 'Are you sure you want to delete this recipe?'))) {
                return
            }
            let id = this.$store.state.recipe.id
            let $this = this

            axios.delete(window.baseUrl + '/api/recipes/' + id)
                .then(function(response) {
                    $this.$window.goTo('/')
                    $this.$root.$emit('refreshNavigation')
                })
                .catch(function(e) {
                    alert(t('cookbook', 'Delete failed'))
                    if (e && e instanceof Error) {
                        throw e
                    }
                })
        },
        printRecipe: function() {
            window.print()
        },
        reloadRecipeEdit: function() {
            this.$root.$emit('reloadRecipeEdit')
        },
        reloadRecipeView: function() {
            this.$root.$emit('reloadRecipeView')
        },
        saveChanges: function() {
            this.$root.$emit('saveRecipe')
        },
        search: function(e) {
            this.$window.goTo('/search/'+e.target[1].value)
        },
        toggleNavigation: function() {
            document.getElementById("app-navigation").classList.toggle("show-navigation")
        },
        updateFilters: function(e) {
            this.filterValue = e
            this.$root.$emit('applyRecipeFilter', e)
        },
    },
    mounted () {
    },
}

</script>

<style scoped>

.wrapper {
    width: 100%;
    padding-left: 4px;
}

.active {
    font-weight: bold;
    cursor: default !important;
}

.breadcrumbs {
    width: calc(100% - 60px);
    flex-basis: 100%;
}

.no-arrow::before {
    content: '' !important;
}

#show-navigation {
    width: 60px;
    height: 44px;
    padding: 0;
    display: none;
    float: left;
}
    #show-navigation .action-button {
        padding-right: 0 !important;
    }

@media only screen and (max-width: 1024px) {
    #show-navigation {
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

@media print {
	
	.vue-tooltip {
		display: none !important;
	}
	
}

</style>

