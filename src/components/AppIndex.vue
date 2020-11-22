<template>
    <div>
        <div class="kw">
            <ul v-if="keywords.length" class="keywords">
                <RecipeKeyword v-for="(keyword,idx) in keywords"
                    :key="'kw.name'+idx"
                    :name="keyword.name"
                    :count="keyword.count"
                    v-on:keyword-clicked="keywordClicked(keyword)"
                    :class="{active : keywordFilter.includes(keyword.name), disabled : !keywordContainedInVisibleRecipes(keyword)}"
                    />
            </ul>
        </div>
        <ul class="recipes">
            <li v-for="(recipe, index) in filteredRecipes" :key="recipe.recipe_id" v-show="recipeVisible(index)">
                <router-link :to="'/recipe/'+recipe.recipe_id">
                     <lazy-picture v-if="recipe.imageUrl"
                        class="recipe-thumbnail"
                        :lazy-src="recipe.imageUrl"
                        :blurred-preview-src="recipe.imagePlaceholderUrl"
                        :width="105" :height="105"/>
                    <span>{{ recipe.name }}</span>
                </router-link>
            </li>
        </ul>
    </div>
</template>

<script>
import LazyPicture from './LazyPicture'
import RecipeKeyword from './RecipeKeyword'

export default {
    name: 'Index',
    components: {
        LazyPicture,
        RecipeKeyword
    },
    data () {
        return {
            filters: "",
            recipes: [],
            keywords: [],
            keywordFilter: [],
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
    watch: {
        keywordFilter: {
            handler: function() {
                this.sortKeywords()
            },
            deep: true
        }
    },
    methods: {
        /**
         * Callback for click on keyword, add to or remove from list
         */
        keywordClicked: function(keyword) {
            const index = this.keywordFilter.indexOf(keyword.name)
            if (index > -1) {
                this.keywordFilter.splice(index, 1)
            } else {
                this.keywordFilter.push(keyword.name)
            }
        },
        /**
         * Check if a keyword exists in the currently visible recipes.
         */
        keywordContainedInVisibleRecipes: function(keyword) {
            for (let i=0; i<this.recipes.length; ++i) {
                if (this.recipeVisible(i)
                    && this.recipes[i].keywords
                    && this.recipes[i].keywords.split(',').includes(keyword.name)) {
                    return true
                }
            }
            return false
        },
        /**
         * Load all recipes from the database
         */
        loadAll: function () {
            var deferred = $.Deferred()
            var $this = this
            $.get(this.$window.baseUrl + '/api/recipes').done(function (recipes) {
                $this.recipes = recipes
                $this.setKeywords(recipes)
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
        /**
         * Check if recipe should be displayed, depending on selected keyword filter.
         * Returns true if recipe contains all selected keywords.
         */
        recipeVisible: function(index) {
            if (this.keywordFilter.length == 0) {
                return true
            } else {
                if (!this.recipes[index].keywords) {
                    return false
                }
                let kw_array = this.recipes[index].keywords.split(',')
                return this.keywordFilter.every(kw => kw_array.includes(kw))
            }
        },
        /**
         * Extract and set list of keywords from the returned recipes.
         */
        setKeywords: function(recipes) {
            this.keywords = []
            if ((recipes.length) > 0) {
                recipes.forEach(recipe => {
                    if(recipe['keywords']) {
                        recipe['keywords'].split(',').forEach(kw => {
                            const idx = this.keywords.findIndex(el  => el.name == kw);
                            if (idx > -1) {
                                this.keywords[idx].count++;
                            } else {
                                this.keywords.push({name: kw, count: 1})
                            }
                        })
                    }
                })
                this.sortKeywords()
            }
        },
        /**
         * Sort keywords.
         */
        sortKeywords: function() {
            // Sort by number recipes
            this.keywords = this.keywords.sort((k1, k2) => k2.count - k1.count)
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

div.kw {
    width: 100%;
    max-height: 6.7em;
    overflow-x: hidden;
    overflow-y: scroll;
    margin-bottom: 1em;
    padding: .1em;
}

ul.keywords {
    display: flex;
    flex-wrap: wrap;
    flex-direction: row;
    width: 100%;
    margin: .5rem 1rem .5rem;
}

ul.recipes {
    display: flex;
    flex-wrap: wrap;
    flex-direction: row;
    width: 100%;
}

    ul.recipes li {
        width: 300px;
        max-width: 100%;
        margin: 0.5rem 1rem 1rem;
    }
        ul.recipes li a {
            display: block;
            height: 105px;
            box-shadow: 0 0 3px #AAA;
            border-radius: 3px;
        }
        ul.recipes li a:hover {
            box-shadow: 0 0 5px #888;
        }

        ul.recipes li .recipe-thumbnail {
            position: relative;
            float: left;
            height: 105px;
            width: 105px;
            border-radius: 3px 0 0 3px;
            background-color: #bebdbd;
            overflow: hidden;
        }

        ul.recipes li span {
            display: block;
            padding: 0.5rem 0.5em 0.5rem calc(105px + 0.5rem);
        }

</style>
