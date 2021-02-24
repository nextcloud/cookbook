<template>
    <div>
        <recipe-list :recipes="results" />
    </div>
</template>

<script>
import axios from "@nextcloud/axios"

import RecipeList from "./RecipeList.vue"

export default {
    name: "Search",
    components: {
        RecipeList,
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
        $route(to, from) {
            this.keywordFilter = []
        },
    },
    mounted() {
        this.setup()
        this.$root.$off("categoryRenamed")
        this.$root.$on("categoryRenamed", (val) => {
            if (
                !this._inactive &&
                this.query === "cat" &&
                this.$route.params.value === val[1]
            ) {
                this.$window.goTo("/category/" + val[0])
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
                axios
                    .get(this.$window.baseUrl + "/api/tags/" + tags)
                    .then((response) => {
                        $this.results = response.data
                    })
                    .catch((e) => {
                        $this.results = []
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
                axios
                    .get(this.$window.baseUrl + "/api/category/" + cat)
                    .then((response) => {
                        $this.results = response.data
                    })
                    .catch((e) => {
                        $this.results = []
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
                axios
                    .get(
                        this.$window.baseUrl +
                            "/api/search/" +
                            this.$route.params.value
                    )
                    .then((response) => {
                        $this.results = response.data
                    })
                    .catch((e) => {
                        $this.results = []
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
    beforeRouteUpdate(to, from, next) {
        // Move to next route as expected
        next()
        // Reload view
        this.setup()
    },
}
</script>
