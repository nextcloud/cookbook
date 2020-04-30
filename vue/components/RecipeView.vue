<template>
    <div style="width:100%">

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
        // Have to use a gimmic to get the recipe data at this point
        // Store recipe data
        if (document.getElementById("app-recipe-data")) {
            this.$store.dispatch('setRecipe', { recipe: JSON.parse(document.getElementById("app-recipe-data").innerHTML) })
            if (this.$store.state.recipe.recipeIngredient) {
                this.ingredients = this.$store.state.recipe.recipeIngredient
            }
            if (this.$store.state.recipe.recipeInstructions) {
                this.instructions = this.$store.state.recipe.recipeInstructions
            }
            if (this.$store.state.recipe.timeCook) {
                this.timerCook = this.$store.state.recipe.timeCook
            }
            if (this.$store.state.recipe.timePrep) {
                this.timerPrep = this.$store.state.recipe.timePrep
            }
            if (this.$store.state.recipe.timeTotal) {
                this.timerTotal = this.$store.state.recipe.timeTotal
            }
            if (this.$store.state.recipe.tool) {
                this.tools = this.$store.state.recipe.tool
            }
        }
        // Always set the active page last!
        this.$store.dispatch('setPage', { page: 'recipe' })
    },

}
</script>

<style scoped>

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
        width: 70%;
        float: left;
        text-align: justify;
    }
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
