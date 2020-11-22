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
            <li v-for="(result, index) in results" :key="result.recipe_id" v-show="recipeVisible(index)">
                <router-link :to="'/recipe/'+result.recipe_id">
                     <lazy-picture v-if="result.imageUrl"
                        class="recipe-thumbnail"
                        :lazy-src="result.imageUrl"
                        :blurred-preview-src="result.imagePlaceholderUrl"
                        :width="105" :height="105"/>
                    <span>{{ result.name }}</span>
                </router-link>
            </li>
        </ul>
    </div>
</template>

<script>
import LazyPicture from './LazyPicture'
import RecipeKeyword from './RecipeKeyword'

export default {
    name: "Search",
    components: {
        LazyPicture,
        RecipeKeyword
    },
    props: ['query'],
    data () {
        return {
            results: [],
            keywords: [],
            keywordFilter: [],
        }
    },
    computed: {
    },
    watch: {

        $route(to, from) {
            this.keywordFilter = [];
        },
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
            for (let i=0; i<this.results.length; ++i) {
                if (this.recipeVisible(i) 
                    && this.results[i].keywords
                    && this.results[i].keywords.split(',').includes(keyword.name)) {
                    return true
                }
            }
            return false
        },
        /**
         * Check if recipe should be displayed, depending on selected keyword filter.
         * Returns true if recipe contains all selected keywords.
         */
        recipeVisible: function(index) {
            if (this.keywordFilter.length == 0) {
                return true
            } else {
                if (!this.results[index].keywords) {
                    return false
                }
                let kw_array = this.results[index].keywords.split(',')
                return this.keywordFilter.every(kw => kw_array.includes(kw))
            }
        },
        /**
         * Extract and set list of keywords from the returned recipes.
         */
        setKeywords: function(results) {
            this.keywords = []
            if ((results.length) > 0) {
                results.forEach(recipe => {
                    if(recipe['keywords']) {
                        recipe['keywords'].split(',').forEach(kw => {
                            const idx = this.keywords.findIndex(el => el.name == kw);
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
        setup: function() {
            
            // TODO: This is a mess of different implementation styles, needs cleanup
            if (this.query === 'name') {
                // Search by name
            }
            else if (this.query === 'tags') {
                // Search by tags
                let $this = this
                let tags = this.$route.params.value
                $.get(this.$window.baseUrl + '/api/tags/'+tags).done(function(json) {
                    $this.results = json
                }).fail(function (jqXHR, textStatus, errorThrown) {
                    $this.results = []
                    alert(t('cookbook', 'Failed to load recipes with keywords: ' + tags))
                    if (errorThrown && errorThrown instanceof Error) {
                        throw errorThrown
                    }
                })
            }
            else if (this.query === 'cat') {
                // Search by category
                let $this = this
                let cat = this.$route.params.value
                $.get(this.$window.baseUrl + '/api/category/'+cat).done(function(json) {
                    $this.results = json
                    $this.setKeywords($this.results)
                }).fail(function (jqXHR, textStatus, errorThrown) {
                    $this.results = []
                    alert(t('cookbook', 'Failed to load category '+cat+' recipes'))
                    if (e && e instanceof Error) {
                        throw e
                    }
                })
            } else {
                // General search
                let $this = this
                let deferred = $.Deferred()
                $.get(this.$window.baseUrl + '/api/search/'+this.$route.params.value).done((recipes) => {
                    $this.results = recipes
                    $this.setKeywords($this.results)
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

        /**
         * Sort keywords.
         */
        sortKeywords: function() {
            // Sort by number recipes
            this.keywords = this.keywords.sort((k1, k2) => k2.count - k1.count)

            // Move unselectable keywords to the end
            let tmp = []
            for (let i=this.keywords.length-1; i>=0; --i) {
                if (!this.keywordContainedInVisibleRecipes(this.keywords[i])) {
                    let elm = this.keywords.splice(i, 1)[0]
                    if (elm) {
                        tmp.push(elm)
                    }
                }
            }
            this.keywords = this.keywords.concat(tmp)
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
