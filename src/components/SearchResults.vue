<template>
    <div>
        <recipe-list :recipes="results"/>
    </div>
</template>

<script>
import axios from '@nextcloud/axios'

import RecipeList from './RecipeList'

export default {
    name: "Search",
    components: {
        RecipeList
    },
    props: ['query'],
    data () {
        return {
            results: []
        }
    },
    watch: {
        $route(to, from) {
            this.keywordFilter = [];
        },
    },
    methods: {
        setup: function() {
            
            // TODO: This is a mess of different implementation styles, needs cleanup
            if (this.query === 'name') {
                // Search by name
                // TODO
            }
            else if (this.query === 'tags') {
                // Search by tags
                let $this = this
                let tags = this.$route.params.value
                axios.get(this.$window.baseUrl + '/api/tags/'+tags)
                    .then(function(response) {
                        $this.results = response.data
                    })
                    .catch(function (e) {
                        $this.results = []
                        alert(t('cookbook', 'Failed to load recipes with keywords: ' + tags))
                        if (e && e instanceof Error) {
                            throw e
                        }
                    })
            }
            else if (this.query === 'cat') {
                // Search by category
                let $this = this
                let cat = this.$route.params.value
                axios.get(this.$window.baseUrl + '/api/category/'+cat)
                    .then(function(response) {
                        $this.results = response.data
                    })
                    .catch(function (e) {
                        $this.results = []
                        alert(t('cookbook', 'Failed to load category '+cat+' recipes'))
                        if (e && e instanceof Error) {
                            throw e
                        }
                    })
            }
            else {
                // General search
                let $this = this
                axios.get(this.$window.baseUrl + '/api/search/'+this.$route.params.value)
                    .then(function(response) {
                        $this.results = response.data
                    })
                    .catch((e) => {
                        $this.results = []
                        alert(t('cookbook', 'Failed to load search results'))
                        if (e && e instanceof Error) {
                            throw e
                        }
                    })
                this.$store.dispatch('setPage', { page: 'search' })
            }
            this.$store.dispatch('setPage', { page: 'search' })
        },
    },
    mounted () {
        this.setup()
    },
    beforeRouteUpdate (to, from, next) {
        // Move to next route as expected
        next()
        // Reload view
        this.setup()
    },
}
</script>
