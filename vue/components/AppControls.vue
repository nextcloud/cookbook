<template>

    <div>
        <!-- INDEX PAGE -->
        <Breadcrumbs class="breadcrumbs" v-if="$store.state.page==='index'" rootIcon="icon-category-organization">
            <Breadcrumb :title="$t('All recipes')"></Breadcrumb>
            <Breadcrumb class="no-arrow" title="">
                <ActionButton icon="icon-search" class="action-button" :ariaLabel="$t('Search')" @click="$window.goTo('/search')" />
            </Breadcrumb>
        </Breadcrumbs>
        <!-- SEARCH PAGE -->
        <Breadcrumbs class="breadcrumbs" v-if="$store.state.page==='search'" rootIcon="icon-category-organization">
            <Breadcrumb
                :title="searchTitle"
            />
            <Breadcrumb class="no-arrow" title="">
            </Breadcrumb>
        </Breadcrumbs>
        <!-- RECIPE PAGES -->
        <!-- Create new recipe -->
        <Breadcrumbs class="breadcrumbs" v-if="$store.state.page==='create'" rootIcon="icon-category-organization">
            <Breadcrumb :title="$t('New recipe')" />
            <Breadcrumb class="no-arrow" title="">
                <ActionButton icon="icon-checkmark" class="action-button" :ariaLabel="$t('Save changes')" @click="saveChanges()" />
            </Breadcrumb>
        </Breadcrumbs>
        <!-- Edit recipe -->
        <Breadcrumbs class="breadcrumbs" v-else-if="$store.state.page==='edit' && $store.state.recipe" rootIcon="icon-category-organization">
            <Breadcrumb :title="$t('Edit recipe')" />
            <Breadcrumb class="no-arrow" title="">
                <ActionButton icon="icon-checkmark" class="action-button" :ariaLabel="$t('Save changes')" @click="saveChanges()" />
            </Breadcrumb>
        </Breadcrumbs>
        <!-- View recipe -->
        <Breadcrumbs class="breadcrumbs" v-else-if="$store.state.page==='recipe' && $store.state.recipe" rootIcon="icon-category-organization">
            <Breadcrumb :title="$t('Home')" :to="'/'" />
            <Breadcrumb :title="$store.state.recipe.name" :to="'/recipe/'+$store.state.recipe.id" />
            <Breadcrumb class="no-arrow" title="">
                <ActionButton icon="icon-rename" class="action-button" :ariaLabel="$t('Edit recipe')" @click="$window.goTo('/recipe/'+$store.state.recipe.id+'/edit')" />
            </Breadcrumb>
            <Breadcrumb class="no-arrow" title="">
                <ActionButton icon="icon-category-office" class="action-button" :ariaLabel="$t('Print recipe')" @click="printRecipe()" />
            </Breadcrumb>
            <Breadcrumb class="no-arrow" title="">
                <ActionButton icon="icon-delete" class="action-button" :ariaLabel="$t('Delete recipe')" @click="deleteRecipe()" />
            </Breadcrumb>
        </Breadcrumbs>
    </div>

</template>

<script>
// Tried loading individual components from dist first, couldn't make it work
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
        searchTitle () {
            if (this.$route.param.query === 'cat') {
                return this.$i18n.t('Category')
            } else if (this.$route.param.query === 'name') {
                return this.$i18n.t('Recipe name')
            } else if (this.$route.param.query === 'tag') {
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
    }
}

</script>

<style scoped>

div {
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
