<template>
    <recipe-list :recipes="recipes" />
</template>

<script>
import api from "cookbook/js/api-interface"

import RecipeList from "./RecipeList.vue"

export default {
    name: "AppIndex",
    components: {
        RecipeList,
    },
    data() {
        return {
            // The known recipes in the cookbook
            recipes: [],
        }
    },
    computed: {
        /**
         * Is the Cookbook recipe directory currently being changed?
         */
        updatingRecipeDirectory() {
            return this.$store.state.updatingRecipeDirectory
        },
    },
    watch: {
        /**
         * If the Cookbook recipe directory currently was changed, reload
         * the recipes in the index component.
         */
        updatingRecipeDirectory(newVal, oldVal) {
            if (newVal === false && newVal !== oldVal) {
                this.loadAll()
            }
        },
    },
    mounted() {
        this.$log.info('AppIndex mounted')
        this.loadAll()
    },
    methods: {
        /**
         * Load all recipes from the database
         */
        loadAll() {
            const $this = this
            api.recipes
                .getAll()
                .then((response) => {
                    $this.recipes = response.data

                    // Always set page name last
                    $this.$store.dispatch("setPage", { page: "index" })
                })
                .catch(() => {
                    // Always set page name last
                    $this.$store.dispatch("setPage", { page: "index" })
                })
        },
    },
}
</script>
