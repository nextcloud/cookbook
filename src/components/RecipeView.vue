<template>
    <div class="wrapper">
        <div v-if="$store.state.recipe" class='header' :class="{ 'responsive': $store.state.recipe.image }">
	        <div class='image' v-if="$store.state.recipe.image">
	        	<RecipeImages />
	        </div>

	        <div class='meta'>
	            <h2>{{ $store.state.recipe.name }}</h2>

	            <div class="details">
                <p v-if="keywords.length">
                    <ul v-if="keywords.length">
                        <RecipeKeyword v-for="(keyword,idx) in keywords" :key="'keyw'+idx" :name="keyword"  v-on:keyword-clicked="keywordClicked(keyword)" />
                    </ul>
                </p>
                <p class="dates">
                    <span v-if="showCreatedDate" class="date"  :title="t('cookbook', 'Date created')">
                        <span class="icon-calendar-dark date-icon" />
                        <span class="date-text">{{ dateCreated }}</span>
                    </span>
                    <span v-if="showModifiedDate" class="date" :title="t('cookbook', 'Last modified')">
                        <span class="icon-rename date-icon" />
                        <span class="date-text">{{ dateModified }}</span>
                    </span>
                </p>
	                <p class="description">{{ $store.state.recipe.description }}</p>
	                <p v-if="$store.state.recipe.url">
	                    <strong>{{ t('cookbook', 'Source') }}: </strong><a target="_blank" :href="$store.state.recipe.url" class='source-url'>{{ $store.state.recipe.url }}</a>
	                </p>
	                <p><strong>{{ t('cookbook', 'Servings') }}: </strong>{{ $store.state.recipe.recipeYield }}</p>
	            </div>
	            <div class="times">
	                <RecipeTimer v-if="timerPrep" :value="timerPrep" :phase="'prep'" :timer="false" :label="'Preparation time'" />
	                <RecipeTimer v-if="timerCook" :value="timerCook" :phase="'prep'" :timer="true" :label="'Cooking time'" />
	                <RecipeTimer v-if="timerTotal" :value="timerTotal" :phase="'total'" :timer="false" :label="'Total time'" />
	            </div>
            </div>
		</div>

        <div v-if="$store.state.recipe" class="content">
            <section>
                <aside>
                    <section>
                        <h3 v-if="ingredients.length">{{ t('cookbook', 'Ingredients') }}</h3>
                        <ul v-if="ingredients.length">
                            <RecipeIngredient v-for="(ingredient,idx) in ingredients" :key="'ingr'+idx" :ingredient="ingredient" />
                        </ul>
                    </section>

                    <section>
                        <h3 v-if="tools.length">{{ t('cookbook', 'Tools') }}</h3>
                        <ul v-if="tools.length">
                            <RecipeTool v-for="(tool,idx) in tools" :key="'tool'+idx" :tool="tool" />
                        </ul>
                    </section>

                    <section v-if="showNutritions">
                        <h3>{{ t('cookbook', 'Nutrition Information') }}</h3>
                        <ul>
                            <recipe-nutrition-info-item v-if="('servingSize' in nutrition) && !isNullOrEmpty(nutrition['servingSize'])" :title="t('cookbook', 'Serving Size')" :data="nutrition['servingSize']" />
                            <recipe-nutrition-info-item v-if="('calories' in nutrition) && !isNullOrEmpty(nutrition['calories'])" :title="t('cookbook', 'Energy')" :data="nutrition['calories']" />
                            <recipe-nutrition-info-item v-if="('sugarContent' in nutrition) && !isNullOrEmpty(nutrition['sugarContent'])" :title="t('cookbook', 'Sugar')" :data="nutrition['sugarContent']" />
                            <recipe-nutrition-info-item v-if="('carbohydrateContent' in nutrition) && !isNullOrEmpty(nutrition['carbohydrateContent'])" :title="t('cookbook', 'Carbohydrate')" :data="nutrition['carbohydrateContent']" />
                            <recipe-nutrition-info-item v-if="('cholesterolContent' in nutrition) && !isNullOrEmpty(nutrition['cholesterolContent'])" :title="t('cookbook', 'Cholesterol')" :data="nutrition['cholesterolContent']" />
                            <recipe-nutrition-info-item v-if="('fiberContent' in nutrition) && !isNullOrEmpty(nutrition['fiberContent'])" :title="t('cookbook', 'Fiber')" :data="nutrition['fiberContent']" />
                            <recipe-nutrition-info-item v-if="('proteinContent' in nutrition) && !isNullOrEmpty(nutrition['proteinContent'])" :title="t('cookbook', 'Protein')" :data="nutrition['proteinContent']" />
                            <recipe-nutrition-info-item v-if="('sodiumContent' in nutrition) && !isNullOrEmpty(nutrition['sodiumContent'])" :title="t('cookbook', 'Sodium')" :data="nutrition['sodiumContent']" />
                            <recipe-nutrition-info-item v-if="('fatContent' in nutrition) && !isNullOrEmpty(nutrition['fatContent'])" :title="t('cookbook', 'Fat total')" :data="nutrition['fatContent']" />
                            <recipe-nutrition-info-item v-if="('saturatedFatContent' in nutrition) && !isNullOrEmpty(nutrition['saturatedFatContent'])" :title="t('cookbook', 'Saturated Fat')" :data="nutrition['saturatedFatContent']" />
                            <recipe-nutrition-info-item v-if="('unsaturatedFatContent' in nutrition) && !isNullOrEmpty(nutrition['unsaturatedFatContent'])" :title="t('cookbook', 'Unsaturated Fat')" :data="nutrition['unsaturatedFatContent']" />
                            <recipe-nutrition-info-item v-if="('transFatContent' in nutrition) && !isNullOrEmpty(nutrition['transFatContent'])" :title="t('cookbook', 'Trans Fat')" :data="nutrition['transFatContent']" />
                        </ul>
                    </section>
                </aside>
                <main v-if="instructions.length">
                    <h3>{{ t('cookbook', 'Instructions') }}</h3>
                    <ol class="instructions">
                        <RecipeInstruction v-for="(instruction,idx) in instructions" :key="'instr'+idx" :instruction="instruction" />
                    </ol>
                </main>
            </section>
        </div>
    </div>
</template>

<script>

import moment from '@nextcloud/moment'

import RecipeImages from './RecipeImages'
import RecipeIngredient from './RecipeIngredient'
import RecipeInstruction from './RecipeInstruction'
import RecipeKeyword from './RecipeKeyword'
import RecipeNutritionInfoItem from './RecipeNutritionInfoItem'
import RecipeTimer from './RecipeTimer'
import RecipeTool from './RecipeTool'

export default {
    name: 'RecipeView',
    components: {
        RecipeImages,
        RecipeIngredient,
        RecipeInstruction,
        RecipeKeyword,
        RecipeNutritionInfoItem,
        RecipeTimer,
        RecipeTool
    },
    data () {
        return {
            // Own properties
            ingredients: [],
            instructions: [],
            keywords: [],
            timerCook: null,
            timerPrep: null,
            timerTotal: null,
            tools: [],
            dateCreated: null,
            dateModified: null,
            nutrition: null
        }
    },
    computed: {
        showModifiedDate: function() {
            if (!this.dateModified) {  
                return false
            }
            else if ( this.$store.state.recipe.dateCreated
                && this.$store.state.recipe.dateModified
                && this.$store.state.recipe.dateCreated === this.$store.state.recipe.dateModified) {
                // don't show modified date if create and modified timestamp are the same
                return false
            }
            return true
        },
        showCreatedDate: function() {
            if (!this.dateCreated) {  
                return false
            }
            return true
        },
        showNutritions: function() { return this.nutrition && !(this.nutrition instanceof Array) && Object.keys(this.nutrition).length > 0 }
    },
    methods: {
        isNullOrEmpty: function(str) {
            return !str || typeof(str) === 'string' && 0 === str.trim().length;
        },
        /**
         * Callback for click on keyword
         */
        keywordClicked: function(keyword) {
            if(keyword) {
                this.$router.push('/tags/'+keyword)
            }
        },
        /* The schema.org standard requires the dates formatted as Date (https://schema.org/Date) 
         * or DateTime (https://schema.org/DateTime). This follows the ISO 8601 standard.
         */
        parseDateTime: function(dt) {
            if (!dt) return null
            var date = moment(dt, moment.ISO_8601)
            if(!date.isValid()) {
                return null
            }
            return date
        },
        setup: function() {
            // Make the control row show that a recipe is loading
            if (!this.$store.state.recipe) {
                this.$store.dispatch('setLoadingRecipe', { recipe: -1 })

            // Make the control row show that the recipe is reloading
            } else if (this.$store.state.recipe.id === parseInt(this.$route.params.id)) {
                this.$store.dispatch('setReloadingRecipe', {
                    recipe: this.$route.params.id
                })

            // Make the control row show that a new recipe is loading
            } else {
                this.$store.dispatch('setLoadingRecipe', { recipe: this.$route.params.id })
            }

            let $this = this

            $.ajax({
                url: this.$window.baseUrl + '/api/recipes/' + this.$route.params.id,
                method: 'GET',
                data: null,

            }).done(function (recipe) {
                // Store recipe data in vuex
                $this.$store.dispatch('setRecipe', { recipe: recipe })

                if ($this.$store.state.recipe.recipeIngredient) {
                    $this.ingredients = Object.values($this.$store.state.recipe.recipeIngredient)
                }

                if ($this.$store.state.recipe.recipeInstructions) {
                    $this.instructions = Object.values($this.$store.state.recipe.recipeInstructions)
                }

                if ($this.$store.state.recipe.keywords) {
                    $this.keywords = String($this.$store.state.recipe.keywords).split(',')
                }

                if ($this.$store.state.recipe.cookTime) {
                    let cookT = $this.$store.state.recipe.cookTime.match(/PT(\d+?)H(\d+?)M/)
                    $this.timerCook = { hours: parseInt(cookT[1]), minutes: parseInt(cookT[2]) }
                }

                if ($this.$store.state.recipe.prepTime) {
                    let prepT = $this.$store.state.recipe.prepTime.match(/PT(\d+?)H(\d+?)M/)
                    $this.timerPrep = { hours: parseInt(prepT[1]), minutes: parseInt(prepT[2]) }
                }

                if ($this.$store.state.recipe.totalTime) {
                    let totalT = $this.$store.state.recipe.totalTime.match(/PT(\d+?)H(\d+?)M/)
                    $this.timerTotal = { hours: parseInt(totalT[1]), minutes: parseInt(totalT[2]) }
                }

                if ($this.$store.state.recipe.tool) {
                    $this.tools = $this.$store.state.recipe.tool
                }

                if ($this.$store.state.recipe.dateCreated) {
                    let date = $this.parseDateTime($this.$store.state.recipe.dateCreated)
                    $this.dateCreated = (date != null ? date.format('L, LT').toString() : null)
                }
                
                if ($this.$store.state.recipe.dateModified) {
                    let date = $this.parseDateTime($this.$store.state.recipe.dateModified)
                    $this.dateModified = (date != null ? date.format('L, LT').toString() : null)
                }
                if ($this.$store.state.recipe.nutrition) {
                    if ( $this.$store.state.recipe.nutrition instanceof Array) {
                        $this.$store.state.recipe.nutrition = {}
                    }
                } else {
                    $this.$store.state.recipe.nutrition = {}
                }
                $this.nutrition = $this.$store.state.recipe.nutrition
                
                // Always set the active page last!
                $this.$store.dispatch('setPage', { page: 'recipe' })

            }).fail(function(e) {
                if ($this.$store.state.loadingRecipe) {
                    // Reset loading recipe
                    $this.$store.dispatch('setLoadingRecipe', { recipe: 0 })
                }

                if ($this.$store.state.reloadingRecipe) {
                    // Reset reloading recipe
                    $this.$store.dispatch('setReloadingRecipe', { recipe: 0 })
                }

                $this.$store.dispatch('setPage', { page: 'recipe' })

                alert(t('cookbook', 'Loading recipe failed'))
            })
        }
    },
    mounted () {
        this.setup()
        // Register data load method hook for access from the controls components
        this.$root.$off('reloadRecipeView')
        this.$root.$on('reloadRecipeView', () => {
            this.setup()
        })
    },
    /**
     * This is one tricky feature of Vue router. If different paths lead to
     * the same component (such as '/recipe/xxx' and '/recipe/yyy)',
     * the component will not automatically reload. So we have to manually
     * reload the page contents.
     * This can also be used to confirm that the user wants to leave the page
     * if there are unsaved changes.
     */
    beforeRouteUpdate (to, from, next) {
        // beforeRouteUpdate is called when the static route stays the same
        next()
        // Check if we should reload the component content
        if (this.$window.shouldReloadContent(from.fullPath, to.fullPath)) {
            this.setup()
        }
    },

}
</script>

<style scoped>


.wrapper {
    width: 100%;
}

aside {
    flex-basis: 20rem;
    padding-right: 2rem;
}
    aside ul {
        list-style-type: disc;
    }

.content {
    width: 100%;
    padding: 1rem;
    flex-basis: 100%;
}
    .content aside {
        width: 30%;
        float: left;
    }
        @media screen and (max-width:1199px) { .content aside {
            width: 100%;
            float: none;
        } }

    main {
        flex-basis: calc(100% - 22rem);
        width: 70%;
        float: left;
        text-align: justify;
    }

    @media screen and (max-width:1199px) { main {
        flex-basis: 100%;
        width: 100%;
    } }

        .dates {
            font-size: .9em;
        }
            .date {
                margin-right: 1.5em;
            }
                .date-icon {
                    display: inline-block;
                    background-size: 1em;
                    margin-right: .2em;
                    vertical-align: middle;
                    margin-bottom: .2em;
                } 
                .date-text {
                    vertical-align: middle;
                } 
        .description {
            font-style: italic;
            white-space: pre-line;
        }
        .details p {
            margin: 0.5em 0
        }

    section {
        margin-bottom: 1rem;
    }

    section::after {
        content: '';
        display: table;
        clear: both;
    }

    @media screen and (max-width:1199px) { .recipe-content aside {
        display: block;
        width: 100%;
        float: none;
    }}

    .instructions {
        list-style: none;
        padding: 0;
        counter-reset: instruction-counter;
        margin-top: 2rem;
    }

    .times {
        display: flex;
        margin-top: 10px;
    }

    div.meta {
    	margin: 0 1rem;
    }

@media print {
    #content {
        display: block !important;
        padding: 0 !important;
        overflow: visible !important;
    }

    div.header {
		display: flex;
	}

	div.header > div.image {
		flex: 600px 0 0;
	}

	div.header > div.meta {
		margin: 0 10px;
	}

	div.header a::after {
		content: '';
	}
}

@media only screen and (min-width: 1500px) {
	div.header.responsive {
		display: flex;
	}

	div.header.responsive > div.image {
		flex: 700px 0 0;
	}

    #app-content-wrapper div.times > div {
        margin: 1rem 0.75rem;
    }
}

</style>
