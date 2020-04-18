<template>

    <div>

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

</template>

<script>

import RecipeIngredient from './RecipeIngredient'
import RecipeInstruction from './RecipeInstruction'
import RecipeTimer from './RecipeTimer'
import RecipeTool from './RecipeTool'

export default {

    components: {
        RecipeIngredient,
        RecipeInstruction,
        RecipeTimer,
        RecipeTool,
    },
    props: ['recipe'],
    data () {
        return {
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
        let recipeData = JSON.parse(document.getElementById("app-recipe-data").innerHTML)
        if (recipeData.recipeIngredient) {
            this.ingredients = recipeData.recipeIngredient
        }
        if (recipeData.recipeInstructions) {
            this.instructions = recipeData.recipeInstructions
        }
        if (recipeData.timeCook) {
            this.timerCook = recipeData.timeCook
        }
        if (recipeData.timePrep) {
            this.timerPrep = recipeData.timePrep
        }
        if (recipeData.timeTotal) {
            this.timerTotal = recipeData.timeTotal
        }
        if (recipeData.tool) {
            this.tools = recipeData.tool
        }
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

.recipe-content {
    padding: 1rem;
    flex-basis: 100%;
}
    .recipe-content aside {
        width: 30%;
        float: left;
    }

    main {
        width: 70%;
        float: left;
        text-align: justify;
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
