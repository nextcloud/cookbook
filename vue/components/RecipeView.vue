<template>

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


</template>

<script>

import RecipeIngredient from './RecipeIngredient'
import RecipeInstruction from './RecipeInstruction'
import RecipeTool from './RecipeTool'

export default {

    components: {
        RecipeIngredient,
        RecipeInstruction,
        RecipeTool,
    },
    props: ['recipe'],
    data () {
        return {
            ingredients: [],
            instructions: [],
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
        if (recipeData.tool) {
            this.tools = recipeData.tool
        }
    },

}
</script>

<style scoped>

</style>
