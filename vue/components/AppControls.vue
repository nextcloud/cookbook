<template>

    <div class="wrapper">
        <!-- Use $store.state.page for page matching to make sure everything else has been set beforehand! -->
        <Breadcrumbs class="breadcrumbs" rootIcon="icon-category-organization">
            <Breadcrumb :title="$t('Home')" :to="'/'" />
            <!-- INDEX PAGE -->
            <Breadcrumb v-if="isIndex" class="active" :title="$t('All recipes')"></Breadcrumb>
            <Breadcrumb v-if="isIndex" class="no-arrow" title="">
                <ActionButton icon="icon-search" class="action-button" :ariaLabel="$t('Search')" @click="$window.goTo('/search')" />
            </Breadcrumb>
            <!-- SEARCH PAGE -->
            <Breadcrumb v-if="isSearch" :title="searchTitle" />
            <Breadcrumb v-if="isSearch" class="active" :title="$route.params.value" />
            <!-- RECIPE PAGES -->
            <!-- Edit recipe -->
            <Breadcrumb v-if="isEdit" :title="$t('Edit recipe')" />
            <Breadcrumb v-if="isEdit" class="active" :title="$store.state.recipe.name" />
            <!-- Create new recipe -->
            <Breadcrumb v-else-if="isCreate" class="active" :title="$t('New recipe')" />
            <Breadcrumb v-if="isEdit || isCreate" class="no-arrow" title="">
                <ActionButton :icon="$store.state.savingRecipe ? 'icon-loading-small' : 'icon-checkmark'" class="action-button" :ariaLabel="$t('Save changes')" @click="saveChanges()" />
            </Breadcrumb>
            <!-- View recipe -->
            <Breadcrumb v-if="isRecipe" class="active" :title="$store.state.recipe.name" :to="'/recipe/'+$store.state.recipe.id" />
            <Breadcrumb v-if="isRecipe" class="no-arrow" title="">
                <ActionButton icon="icon-rename" class="action-button" :ariaLabel="$t('Edit recipe')" @click="$window.goTo('/recipe/'+$store.state.recipe.id+'/edit')" />
            </Breadcrumb>
            <Breadcrumb v-if="isRecipe" class="no-arrow" title="">
                <ActionButton icon="icon-category-office" class="action-button" :ariaLabel="$t('Print recipe')" @click="printRecipe()" />
            </Breadcrumb>
            <Breadcrumb v-if="isRecipe" class="no-arrow" title="">
                <ActionButton icon="icon-delete" class="action-button" :ariaLabel="$t('Delete recipe')" @click="deleteRecipe()" />
            </Breadcrumb>
            <!-- Is the page loading? -->
            <Breadcrumb v-else-if="isLoading" class="active no-arrow" :title="$t('Page is loading')">
                <ActionButton icon="icon-loading-small" :ariaLabel="$t('Loading...')" />
            </Breadcrumb>
            <!-- No recipe found -->
            <Breadcrumb v-else-if="isNotFound" class="active" :title="$t('Recipe not found')" />
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
        },
        isEdit () {
            // Editing requires that a recipe was found
            if (this.$store.state.page === 'edit' && this.$store.state.recipe) {
                return true
            }
        },
        isIndex () {
            if (this.$store.state.page === 'index') {
                return true
            }
        },
        isLoading () {
            //  A recipe is in the process of loading
            if (this.$store.state.page === null) {
                return true
            }
        },
        isNotFound () {
            // Editing or viewing recipe was attempted, but no recipe was found
            if ((this.$store.state.page === 'edit' || this.$store.state.page === 'recipe')
                && !this.$store.state.recipe) {
                return true
            }
        },
        isRecipe () {
            // Viewing recipe requires that one was found
            if (this.$store.state.page === 'recipe' && this.$store.state.recipe) {
                return true
            }
        },
        isSearch () {
            if (this.$store.state.page === 'search') {
                return true
            }
        },
        searchTitle () {
            if (this.$route.name === 'search-category') {
                return this.$i18n.t('Category')
            } else if (this.$route.name === 'search-name') {
                return this.$i18n.t('Recipe name')
            } else if (this.$route.name === 'search-tag') {
                return this.$i18n.t('Tag')
            } else {
                return this.$i18n.t('Search for recipes')
            }
        }
    },
    methods: {
        deleteRecipe: function() {
            // Confirm delete
            if (!confirm(this.$t('Confirm delete'))) {
                return
            }
            let id = this.$store.state.recipe.id
            let $this = this
            $.ajax({
                url: window.baseUrl + '/api/recipes/' + id,
                method: 'DELETE',
            })
            .done(function(reply) {
                window.goTo('/')
            })
            .fail(function(e) {
                alert($this.$t('Delete failed'))
                if (e && e instanceof Error) {
                    throw e
                }
            })
        },
        printRecipe: function() {
            window.print()
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
