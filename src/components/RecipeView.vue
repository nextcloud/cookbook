<template>
    <div class="wrapper">

        <RecipeImages v-if="$store.state.recipe" />

        <div v-if="$store.state.recipe" class="content">
            <h2>{{ $store.state.recipe.name }}</h2>

            <div class="details">
                <p class="description">{{ $store.state.recipe.description }}</p>
                <p v-if="$store.state.recipe.url">
                    <strong>{{ t('Source') }}: </strong><a target="_blank" :href="$store.state.recipe.url">{{ $store.state.recipe.url }}</a>
                </p>
                <p><strong>{{ t('Servings') }}: </strong>{{ $store.state.recipe.recipeYield }}</p>
            </div>
            <div class="times">
                <RecipeTimer v-if="timerPrep" :value="timerPrep" :phase="'prep'" :timer="false" :label="'Preparation time'" />
                <RecipeTimer v-if="timerCook" :value="timerCook" :phase="'prep'" :timer="true" :label="'Cooking time'" />
                <RecipeTimer v-if="timerTotal" :value="timerTotal" :phase="'total'" :timer="false" :label="'Total time'" />
            </div>

            <section>
                <aside>
                    <section>
                        <h3 v-if="ingredients.length">{{ t('Ingredients') }}</h3>
                        <ul v-if="ingredients.length">
                            <RecipeIngredient v-for="(ingredient,idx) in ingredients" :key="'ingr'+idx" :ingredient="ingredient" />
                        </ul>
                    </section>

                    <section>
                        <h3 v-if="tools.length">{{ t('Tools') }}</h3>
                        <ul v-if="tools.length">
                            <RecipeTool v-for="(tool,idx) in tools" :key="'tool'+idx" :tool="tool" />
                        </ul>
                    </section>
                </aside>
                <main v-if="instructions.length">
                    <h3>{{ t('Instructions') }}</h3>
                    <ol class="instructions">
                        <RecipeInstruction v-for="(instruction,idx) in instructions" :key="'instr'+idx" :instruction="instruction" />
                    </ol>
                </main>
            </section>
        </div>
    </div>
</template>

<script>

import RecipeImages from './RecipeImages'
import RecipeIngredient from './RecipeIngredient'
import RecipeInstruction from './RecipeInstruction'
import RecipeTimer from './RecipeTimer'
import RecipeTool from './RecipeTool'

export default {
    name: 'RecipeView',
    components: {
        RecipeImages,
        RecipeIngredient,
        RecipeInstruction,
        RecipeTimer,
        RecipeTool,
    },
    data () {
        return {
            // Own properties
            ingredients: [],
            instructions: [],
            timerCook: null,
            timerPrep: null,
            timerTotal: null,
            tools: [],
        }
    },
    methods: {
        setup: function() {
            if (!this.$store.state.recipe) {
                // Make the control row show that a recipe is loading
                this.$store.dispatch('setLoadingRecipe', { recipe: -1 })
            } else if (this.$store.state.recipe.id === parseInt(this.$route.params.id)) {
                // Make the control row show that the recipe is reloading
                this.$store.dispatch('setReloadingRecipe', {
                    recipe: this.$route.params.id
                })
            } else {
                // Make the control row show that a new recipe is loading
                this.$store.dispatch('setLoadingRecipe', { recipe: this.$route.params.id })
            }
            let $this = this
            $.ajax({
                url: this.$window.baseUrl + '/api/recipes/'+this.$route.params.id,
                method: 'GET',
                data: null,
            }).done(function (recipe) {
                // Store recipe data in vuex
                $this.$store.dispatch('setRecipe', { recipe: recipe })
                if ($this.$store.state.recipe.recipeIngredient) {
                    $this.ingredients = $this.$store.state.recipe.recipeIngredient
                }
                if ($this.$store.state.recipe.recipeInstructions) {
                    $this.instructions = $this.$store.state.recipe.recipeInstructions
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
                alert($this.t('Loading recipe failed'))
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
        @media(max-width:1199px) { .content aside {
            width: 100%;
            float: none;
        } }

    main {
        flex-basis: calc(100% - 22rem);
        width: 70%;
        float: left;
        text-align: justify;
    }
    
    @media(max-width:1199px) { main {
        flex-basis: 100%;
        width: 100%;
    } }

        .description {
            font-style: italic;
            white-space: pre;
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

    @media(max-width:1199px) { .recipe-content aside {
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

@media print {
    #content {
        display: block !important;
        padding: 0 !important;
        overflow: visible !important;
    }
}

</style>
