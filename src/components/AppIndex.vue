<template>
    <recipe-list :recipes="recipes" />
</template>

<script>
import axios from "@nextcloud/axios"

import RecipeList from "./RecipeList.vue"

export default {
    name: "Index",
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
        this.loadAll()
    },
    methods: {
        /**
         * Load all recipes from the database
         */
        loadAll() {
            const $this = this
            axios
                .get(this.$window.baseUrl + "/api/recipes")
                .then((response) => {
                    $this.recipes = response.data

                    // Always set page name last
                    $this.$store.dispatch("setPage", { page: "index" })
                })
                .catch((e) => {
                    // Always set page name last
                    $this.$store.dispatch("setPage", { page: "index" })
                })
        },
    },
}
</script>
