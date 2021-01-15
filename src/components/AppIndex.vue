<template>
    <div>
        <div class="kw">
            <transition-group v-if="keywords.length" class="keywords" name="keyword-list" tag="ul">
                <RecipeKeyword v-for="keywordObj in selectedKeywords"
                    :key="keywordObj.name"
                    :name="keywordObj.name"
                    :count="keywordObj.count"
                    :title="t('cookbook','Toggle keyword')"
                    @keyword-clicked="keywordClicked(keywordObj)"
                    class="keyword active"
                    />
                <RecipeKeyword v-for="keywordObj in selectableKeywords"
                    :key="keywordObj.name"
                    :name="keywordObj.name"
                    :count="keywordObj.count"
                    :title="t('cookbook','Toggle keyword')"
                    @keyword-clicked="keywordClicked(keywordObj)"
                    class="keyword"
                    />
                <RecipeKeyword v-for="keywordObj in unavailableKeywords"
                    :key="keywordObj.name"
                    :name="keywordObj.name"
                    :count="keywordObj.count"
                    :title="t('cookbook','Keyword not contained in visible recipes')"
                    @keyword-clicked="keywordClicked(keywordObj)"
                    class="keyword disabled"
                    />
                
            </transition-group>
        </div>
        <ul class="recipes">
            <li
                v-for="(recipeObj, index) in recipeObjects" 
                :key="recipeObj.recipe.recipe_id"
                v-show="recipeObj.show"
                >
                <router-link :to="'/recipe/' + recipeObj.recipe.recipe_id">
                     <lazy-picture v-if="recipeObj.recipe.imageUrl"
                        class="recipe-thumbnail"
                        :lazy-src="recipeObj.recipe.imageUrl"
                        :blurred-preview-src="recipeObj.recipe.imagePlaceholderUrl"
                        :width="105" :height="105"/>
                    <span>{{ recipeObj.recipe.name }}</span>
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
        rawKeywords() {
            var keywordArray = this.recipes.map(function(r){
                if(! 'keywords' in r) {
                    return []
                }
                if(r.keywords != null){
                    return r.keywords.split(',')
                } else {
                    return []
                }
            })
            return [].concat(... keywordArray)
        },
        uniqKeywords() {
            function uniqFilter(value, index, self) {
                return self.indexOf(value) === index
            }
            return this.rawKeywords.sort().filter(uniqFilter)
        },
        keywordsWithCount() {
            let $this = this
            return this.uniqKeywords.map(function (kw){
                return {
                    'name': kw,
                    'count': $this.rawKeywords.filter((kw2) => kw === kw2).length
                }
            }).sort((k1, k2) => k2.count - k1.count)
        },
        selectedKeywords() {
            return this.keywordsWithCount.filter((kw) => this.keywordFilter.includes(kw.name))
        },
        unselectedKeywords() {
            return this.keywordsWithCount.filter((kw) => ! this.selectedKeywords.includes(kw))
        },
        recipesFilteredByKeywords() {
            let $this = this
            return this.recipes.filter(function (r) {
                if($this.keywordFilter.length == 0) {
                    // No filtering, keep all
                    return true
                }
                
                if(r.keywords === null){
                    return false
                }
                
                function keywordInRecipePresent(kw,r) {
                    if(! r.keywords) {
                        return false;
                    }
                    
                    let keywords = r.keywords.split(',')
                    return keywords.includes(kw.name)
                }
                
                return $this.selectedKeywords.map((kw) => keywordInRecipePresent(kw, r)).reduce((l,r) => l && r)
            })
        },
        filteredRecipes () {
            let ret = this.recipesFilteredByKeywords
            let $this = this
            
            if(this.filters){
                ret = ret.filter(function (r) {
                    return r.name.toLowerCase().includes($this.filters.toLowerCase())
                })
            }
            
            return ret
        },
        selectableKeywords() {
            let $this = this
            return this.unselectedKeywords.filter(function (kw) {
                return $this.filteredRecipes.map(function (r) {
                    return r.keywords && r.keywords.split(',').includes(kw.name)
                }).reduce((l,r) => l || r)
            })
        },
        unavailableKeywords() {
            return this.unselectedKeywords.filter((kw) => ! this.selectableKeywords.includes(kw))
        },
        keywordsForButtons() {
            return this.selectedKeywords.map((kw) => (
                {
                    'keywordObject': kw,
                    'selected': true,
                    'selectable': true
                }
            )).concat(
                this.selectableKeywords.map((kw) => ({
                    'keywordObject': kw,
                    'selected': false,
                    'selectable': true
                })),
                this.unavailableKeywords.map((kw) => ({
                    'keywordObject': kw,
                    'selected': false,
                    'selectable': false
                })),
            )
        },
        recipeObjects() {
            let filtered = this.filteredRecipes
            return this.recipes.map(function (r) {
                return {
                    'recipe': r,
                    'show': filtered.includes(r)
                }
            })
        },
    },
    watch: {
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
            console.log('keywordContainedInVisibleRecipes called.')
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
            //debugger
            if ((recipes.length) > 0) {
                recipes.forEach(recipe => {
                    if(recipe['keywords']) {
                        recipe['keywords'].split(',').forEach(kw => {
                            const idx = this.keywords.findIndex(el => el.name == kw)
                            if (idx > -1) {
                                this.keywords[idx].count++
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
            // Sort by number of recipes containing keyword
            this.keywords = this.keywords.sort((k1, k2) => k2.count - k1.count)

            // Move selected keywords to the front and unselectable to the end
            let selected_kw = [], selectable_kw = [], unavailable_kw = []
            this.keywords.forEach(kw => {
                if (this.keywordFilter.includes(kw.name)) {
                    selected_kw.push(kw)
                }
                else if (this.keywordContainedInVisibleRecipes(kw)) {
                    selectable_kw.push(kw)
                }
                else {
                    unavailable_kw.push(kw)
                }
            })
            this.keywords = selected_kw.concat(selectable_kw.concat(unavailable_kw))
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
    padding: .5rem 1rem .5rem;
}

.keyword {
  display: inline-block;
}

.keyword-list-move {
  transition: transform .5s;
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
