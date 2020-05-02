<template>
    <div class="wrapper">

        <RecipeImages v-if="$store.state.recipe" />

        <div v-if="$store.state.recipe" class="content">
            <h2>{{ $store.state.recipe.name }}</h2>

            <div class="details">
                <p class="description">{{ $store.state.recipe.description }}</p>
                <p v-if="$store.state.recipe.url">
                    <strong>{{ $t('recipe.view.source') }}: </strong><a target="_blank" :href="$store.state.recipe.url">{{ $store.state.recipe.url }}</a>
                </p>
                <p><strong>{{ $t('recipe.view.servings') }}: </strong>{{ $store.state.recipe.recipeYield }}</p>
            </div>
            <div class="times">
                <RecipeTimer v-if="timerPrep" :value="timerPrep" :phase="'prep'" :timer="false" />
                <RecipeTimer v-if="timerCook" :value="timerCook" :phase="'prep'" :timer="true" />
                <RecipeTimer v-if="timerTotal" :value="timerTotal" :phase="'total'" :timer="false" />
            </div>

            <section>
                <aside>
                    <section>
                        <h3 v-if="ingredients.length">{{ $t('recipe.view.ingredients.header') }}</h3>
                        <ul v-if="ingredients.length">
                            <RecipeIngredient v-for="ingredient in ingredients" :key="ingredient" :ingredient="ingredient" />
                        </ul>
                    </section>

                    <section>
                        <h3 v-if="tools.length">{{ $t('recipe.view.tools.header') }}</h3>
                        <ul v-if="tools.length">
                            <RecipeTool v-for="tool in tools" :key="tool" :tool="tool" />
                        </ul>
                    </section>
                </aside>
                <main v-if="instructions.length">
                    <h3>{{ $t('recipe.view.instructions.header') }}</h3>
                    <ol class="instructions">
                        <RecipeInstruction v-for="instruction in instructions" :key="instruction" :instruction="instruction" />
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
    mounted () {
        let $this = this
        $.ajax({
            url: this.$window.baseUrl + '/api/recipes/'+this.$route.params.id,
            method: 'GET',
            data: null,
        }).done(function (recipe) {
            //console.log(recipe) // Testing
            // Store recipe data in vuex
            $this.$store.dispatch('setRecipe', { recipe: recipe })
            if ($this.$store.state.recipe.ingredients) {
                $this.ingredients = $this.$store.state.recipe.ingredients
            }
            if ($this.$store.state.recipe.instructions) {
                $this.instructions = $this.$store.state.recipe.instructions
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
            if ($this.$store.state.recipe.tools) {
                $this.tools = $this.$store.state.recipe.tools
            }
            // Always set the active page last!
            $this.$store.dispatch('setPage', { page: 'recipe' })
        }).fail(function(e) {
            alert($this.$t('Loading recipe failed'))
        })
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

</style>
