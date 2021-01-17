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
    },
}
</script>
