<template>
    <recipe-list :recipes="recipes"/>
</template>

<script>
import axios from '@nextcloud/axios'

import RecipeList from './RecipeList'

export default {
    name: 'Index',
    components: {
        RecipeList
    },
    data () {
        return {
            // The known recipes in the cookbook
            recipes: []
        }
    },
    computed: {
        /**
         * Is the Cookbook recipe directory currently being changed?
         */
        updatingRecipeDirectory () {
            return this.$store.state.updatingRecipeDirectory
        }
    },
    watch: {
        /**
         * If the Cookbook recipe directory currently was changed, reload
         * the recipes in the index component.
         */
        updatingRecipeDirectory(newVal, oldVal) {
            if (newVal == false && newVal != oldVal) {
                this.loadAll()
            }
        }
    },
    methods: {
        /**
         * Load all recipes from the database
         */
        loadAll: function () {
            var $this = this
            axios.get(this.$window.baseUrl + '/api/recipes')
                .then(function (response) {
                    $this.recipes = response.data

                    // Always set page name last
                    $this.$store.dispatch('setPage', { page: 'index' })
                })
                .catch(function (e) {
                    // Always set page name last
                    $this.$store.dispatch('setPage', { page: 'index' })
                })
        },
    },
    mounted () {
        this.loadAll()
    }
}
</script>
