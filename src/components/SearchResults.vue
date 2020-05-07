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
            if (this.query === 'name') {
                // Search by name
                console.log("Recipe name search for "+this.$route.params.value)
            }
            if (this.query === 'tag') {
                // Search by tag
                console.log("Tag search for "+this.$route.params.value)
            }
            if (this.query === 'cat') {
                // Search by category
                console.log("Category search for "+this.$route.params.value)
                let $this = this
                let cat = this.$route.params.value
                $.get(this.$window.baseUrl + '/category/'+cat).done(function(json) {
                    $this.results = json
                }).fail(function (jqXHR, textStatus, errorThrown) {
                    $this.results = []
                    alert($this.t('Failed to load category '+cat+' recipes'))
                    if (e && e instanceof Error) {
                        throw e
                    }
                })
            } else {
                // Something else?
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
