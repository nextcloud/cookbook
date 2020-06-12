<template>
    <div class="wrapper">
        <!-- Use $store.state.page for page matching to make sure everything else has been set beforehand! -->
        <Breadcrumbs class="breadcrumbs" rootIcon="icon-category-organization">
            <Breadcrumb :title="t('Home')" :to="'/'" :disableDrop="true" />
            <!-- INDEX PAGE -->
            <Breadcrumb v-if="isIndex" class="active no-arrow" :title="t('All recipes')" :disableDrop="true"></Breadcrumb>
            <!--
            <Breadcrumb v-if="isIndex" class="no-arrow" title="" :disableDrop="true">
                <ActionButton icon="icon-search" class="action-button" :disabled="true" :ariaLabel="t('Search')" @click="$window.goTo('/search')" />
            </Breadcrumb>
            -->
            <!-- SEARCH PAGE -->
            <Breadcrumb v-if="isSearch" class="not-link" :title="searchTitle" :disableDrop="true" />
            <Breadcrumb v-if="isSearch && $route.params.value" class="active" :title="$route.params.value" :disableDrop="true" />
            <!-- RECIPE PAGES -->
            <!-- Edit recipe -->
            <Breadcrumb v-if="isEdit" class="not-link" :title="t('Edit recipe')" :disableDrop="true" />
            <Breadcrumb v-if="isEdit" class="active" :title="$store.state.recipe.name" :disableDrop="true">
                <ActionButton
                    :icon="$store.state.reloadingRecipe===parseInt($route.params.id) ? 'icon-loading-small' : 'icon-history'"
                    class="action-button"
                    :ariaLabel="t('Reload recipe')"
                    @click="reloadRecipeEdit()"
                />
            </Breadcrumb>
            <!-- Create new recipe -->
            <Breadcrumb v-else-if="isCreate" class="active" :title="t('New recipe')" :disableDrop="true" />
            <Breadcrumb v-if="isEdit || isCreate" class="no-arrow" title="" :disableDrop="true">
                <ActionButton
                    :icon="$store.state.savingRecipe ? 'icon-loading-small' : 'icon-checkmark'"
                    class="action-button"
                    :ariaLabel="t('Save changes')"
                    @click="saveChanges()"
                />
            </Breadcrumb>
            <!-- View recipe -->
            <Breadcrumb v-if="isRecipe" class="active" :title="$store.state.recipe.name" :disableDrop="true">
                <ActionButton
                    :icon="$store.state.reloadingRecipe===parseInt($route.params.id) ? 'icon-loading-small' : 'icon-history'"
                    class="action-button"
                    :ariaLabel="t('Reload recipe')"
                    @click="reloadRecipeView()"
                />
            </Breadcrumb>
            <Breadcrumb v-if="isRecipe" class="no-arrow" title="" :disableDrop="true">
                <ActionButton
                    icon="icon-rename"
                    class="action-button"
                    :ariaLabel="t('Edit recipe')"
                    @click="$window.goTo('/recipe/'+$store.state.recipe.id+'/edit')"
                />
            </Breadcrumb>
            <Breadcrumb v-if="isRecipe" class="no-arrow" title="" :disableDrop="true">
                <ActionButton icon="icon-category-office" class="action-button" :ariaLabel="t('Print recipe')" @click="printRecipe()" />
            </Breadcrumb>
            <Breadcrumb v-if="isRecipe" class="no-arrow" title="" :disableDrop="true">
                <ActionButton icon="icon-delete" class="action-button" :ariaLabel="t('Delete recipe')" @click="deleteRecipe()" />
            </Breadcrumb>
            <!-- Is the app loading? -->
            <Breadcrumb v-if="isLoading" class="active no-arrow" :title="t('App is loading')" :disableDrop="true">
                <ActionButton icon="icon-loading-small" :ariaLabel="t('Loading...')" />
            </Breadcrumb>
            <!-- Is a recipe loading? -->
            <Breadcrumb v-else-if="isLoadingRecipe" class="active no-arrow" :title="t('Loading recipe')" :disableDrop="true">
                <ActionButton icon="icon-loading-small" :ariaLabel="t('Loading...')" />
            </Breadcrumb>
            <!-- No recipe found -->
            <Breadcrumb v-else-if="recipeNotFound" class="active no-arrow" :title="t('Recipe not found')" :disableDrop="true" />
            <!-- No page found -->
            <Breadcrumb v-else-if="pageNotFound" class="active no-arrow" :title="t('Page not found')" :disableDrop="true" />
        </Breadcrumbs>
    </div>

</template>

<script>
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'
import Breadcrumbs from '@nextcloud/vue/dist/Components/Breadcrumbs'
import Breadcrumb from '@nextcloud/vue/dist/Components/Breadcrumb'

export default {
    name: 'AppControls',
    components: {
        ActionButton, Breadcrumbs, Breadcrumb
    },
    data () {
        return {

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
                return this.t('Category')
            } else if (this.$route.name === 'search-name') {
                return this.t('Recipe name')
            } else if (this.$route.name === 'search-tag') {
                return this.t('Tag')
            } else {
                return this.t('Search for recipes')
            }
        }
    },
    methods: {
        deleteRecipe: function() {
            // Confirm delete
            if (!confirm(this.t('Are you sure you want to delete this recipe?'))) {
                return
            }
            let id = this.$store.state.recipe.id
            let $this = this
            $.ajax({
                url: window.baseUrl + '/api/recipes/' + id,
                method: 'DELETE',
            })
            .done(function(reply) {
                $this.$window.goTo('/')
                $this.$root.$emit('refreshNavigation')
            })
            .fail(function(e) {
                alert($this.t('Delete failed'))
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
    },
    mounted () {
    },
}

</script>

<style scoped>

.wrapper {
    width: 100%;
}

.active {
    font-weight: bold;
    cursor: default !important;
}

.breadcrumbs {
    flex-basis: 100%;
}

.no-arrow::before {
    content: '' !important;
}

@media print {
    * {
        display: none !important;
    }
}

</style>
