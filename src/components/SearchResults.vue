<template>
    <ul>
        <li v-for="result in results" :key="result.recipe_id">
            <router-link :to="'/recipe/'+result.recipe_id">
                <img v-if="result.imageUrl" :src="result.imageUrl">
                <span>{{ result.name }}</span>
            </router-link>
        </li>
    </ul>
</template>

<script>
export default {
    name: "Search",
    props: ['query'],
    data () {
        return {
            results: [],
        }
    },
    methods: {
        setup: function() {
            // TODO: This is a mess of different implementation styles, needs cleanup
            if (this.query === 'name') {
                // Search by name
            }
            if (this.query === 'tag') {
                // Search by tag
            }
            if (this.query === 'cat') {
                // Search by category
                let $this = this
                let cat = this.$route.params.value
                $.get(this.$window.baseUrl + '/api/category/'+cat).done(function(json) {
                    $this.results = json
                }).fail(function (jqXHR, textStatus, errorThrown) {
                    $this.results = []
                    alert(t('cookbook', 'Failed to load category '+cat+' recipes'))
                    if (e && e instanceof Error) {
                        throw e
                    }
                })
            } else {
                // General search
                let deferred = $.Deferred()
                $.get(this.$window.baseUrl + '/api/search/'+this.$route.params.value).done((recipes) => {
                    this.results = recipes
                    deferred.resolve()
                }).fail((jqXHR, textStatus, errorThrown) => {
                    this.results = []
                    deferred.reject(new Error(jqXHR.responseText))
                    alert(t('cookbook', 'Failed to load search results'))
                    if (e && e instanceof Error) {
                        throw e
                    }
                })
                this.$store.dispatch('setPage', { page: 'search' })
                return deferred.promise()
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

<style scoped>

ul {
    display: flex;
    flex-wrap: wrap;
    flex-direction: row;
    width: 100%;
}

    ul li {
        width: 300px;
        max-width: 100%;
        margin: 0.5rem 1rem 1rem;
    }
        ul li a {
            display: block;
            height: 105px;
            box-shadow: 0 0 3px #AAA;
            border-radius: 3px;
        }
        ul li a:hover {
            box-shadow: 0 0 5px #888;
        }

        ul li img {
            float: left;
            height: 105px;
            border-radius: 3px 0 0 3px;
        }

        ul li span {
            display: block;
            padding: 0.5rem 0.5em 0.5rem calc(105px + 0.5rem);
        }

</style>
