<template>
    <li
        :class="{
            header: isHeader(),
            unindented: !recipeIngredientsHaveSubgroups,
        }"
        @click="toggleDone"
    >
        <div class="ingredient">
            <VueShowdown :markdown="formattedIngredient" flavor="github" />
        </div>
        <span v-if="!ingredientHasCorrectSyntax" class="icon-error" />
    </li>
</template>

<script>
export default {
    name: "RecipeIngredient",
    props: {
        /* Ingredient HTML string to display. Content should be sanitized.
         */
        ingredient: {
            type: String,
            default: "",
        },
        ingredientHasCorrectSyntax: {
            type: Boolean,
        },
        recipeIngredientsHaveSubgroups: {
            type: Boolean,
        },
    },
    data() {
        return {
            headerPrefix: "## ",
            isDone: false,
        }
    },
    computed: {
        displayIngredient() {
            if (this.isHeader()) {
                return this.ingredient.substring(this.headerPrefix.length)
            }
            return this.ingredient
        },
        formattedIngredient() {
            return this.isDone
                ? `~~${this.displayIngredient}~~`
                : `**${this.displayIngredient}**`
        },
    },
    methods: {
        isHeader() {
            return this.ingredient.startsWith(this.headerPrefix)
        },
        toggleDone() {
            this.isDone = !this.isDone
        },
    },
}
</script>

<style scoped>
li {
    display: flex;
}

.header {
    position: relative;
    left: -1.25em;
    margin-top: 0.25em;
    font-variant: small-caps;
    list-style-type: none;
}

li > .ingredient {
    display: inline;
    padding-left: 1em;
    text-indent: -1em;
}

li > span.icon-error {
    margin-left: 0.3em;
}
</style>
