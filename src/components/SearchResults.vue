<template>
    <div>
        <recipe-list :recipes="results" />
    </div>
</template>

<script>
import api from "cookbook/js/api-interface"
import helpers from "cookbook/js/helper"

import RecipeList from "./RecipeList.vue"

export default {
    name: "SearchResult",
    components: {
        RecipeList,
    },
    beforeRouteUpdate(to, from, next) {
        // Move to next route as expected
        next()
        // Reload view
        this.setup()
    },
    props: {
        query: {
            type: String,
            default: "",
        },
    },
    data() {
        return {
            results: [],
        }
    },
    watch: {
        // eslint-disable-next-line no-unused-vars
        $route(to, from) {
            this.keywordFilter = []
        },
    },
    mounted() {
        this.setup()
        this.$root.$off("categoryRenamed")
        this.$root.$on("categoryRenamed", (val) => {
            if (
                // eslint-disable-next-line no-underscore-dangle
                !this._inactive &&
                this.query === "cat" &&
                this.$route.params.value === val[1]
            ) {
                helpers.goTo(`/category/${val[0]}`)
            }
        })
    },
    methods: {
        setup() {
            // TODO: This is a mess of different implementation styles, needs cleanup
            if (this.query === "name") {
                // Search by name
                // TODO
            } else if (this.query === "tags") {
                // Search by tags
                const $this = this
                const tags = this.$route.params.value
                api.recipes
                    .allWithTag(tags)
                    .then((response) => {
                        $this.results = response.data
                    })
                    .catch((e) => {
                        $this.results = []
                        // eslint-disable-next-line no-alert
                        alert(
                            // prettier-ignore
                            t("cookbook","Failed to load recipes with keywords: {tags}",
                                {
                                    tags
                                }
                            )
                        )
                        if (e && e instanceof Error) {
                            throw e
                        }
                    })
            } else if (this.query === "cat") {
                // Search by category
                const $this = this
                const cat = this.$route.params.value
                api.recipes
                    .allInCategory(cat)
                    .then((response) => {
                        $this.results = response.data
                    })
                    .catch((e) => {
                        $this.results = []
                        // eslint-disable-next-line no-alert
                        alert(
                            // prettier-ignore
                            t("cookbook","Failed to load category {category} recipes",
                                {
                                    category: cat,
                                }
                            )
                        )
                        if (e && e instanceof Error) {
                            throw e
                        }
                    })
            } else {
                // General search
                const $this = this
                api.recipes
                    .search($this.$route.params.value)
                    .then((response) => {
                        $this.results = response.data
                    })
                    .catch((e) => {
                        $this.results = []
                        // eslint-disable-next-line no-alert
                        alert(t("cookbook", "Failed to load search results"))
                        if (e && e instanceof Error) {
                            throw e
                        }
                    })
                this.$store.dispatch("setPage", { page: "search" })
            }
            this.$store.dispatch("setPage", { page: "search" })
        },
    },
}
</script>
