<template>
    <ul>
        <li v-for="recipe in filteredRecipes" :key="recipe.recipe_id">
            <router-link :to="'/recipe/'+recipe.recipe_id">
                <img v-if="recipe.imageUrl" :src="recipe.imageUrl">
                <span>{{ recipe.name }}</span>
            </router-link>
        </li>
    </ul>
</template>

<script>
export default {
    name: 'Index',
    data () {
        return {
            filters: "",
            recipes: [],
        }
    },
    computed: {
        filteredRecipes () {
            if (!this.filters) {
                return this.recipes
            } else if (this.recipes.length) {
                let filtered = []
                for (let i=0; i<this.recipes.length; i++) {
                    if (this.recipes[i].name.toLowerCase().indexOf(this.filters.toLowerCase()) >= 0) {
                        filtered.push(this.recipes[i])
                    }
                }
                return filtered
            }
        },
    },
    methods: {
        /**
         * Load all recipes from the database
         */
        loadAll: function () {
            var deferred = $.Deferred()
            var $this = this
            $.get(this.$window.baseUrl + '/api/recipes').done(function (recipes) {
                $this.recipes = recipes
                deferred.resolve()
                // Always set page name last
                $this.$store.dispatch('setPage', { page: 'index' })
            }).fail(function (jqXHR, textStatus, errorThrown) {
                deferred.reject(new Error(jqXHR.responseText))
                // Always set page name last
                $this.$store.dispatch('setPage', { page: 'index' })
            })
            return deferred.promise()
        },
    },
    mounted () {
        this.$root.$off('applyRecipeFilter')
        this.$root.$on('applyRecipeFilter', (value) => {
            this.filters = value
        })
        this.loadAll()
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
