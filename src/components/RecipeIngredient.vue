<template>
    <li
        :class="{
            header: isHeader(),
            unindented: !recipeIngredientsHaveSubgroups,
        }"
        @click="toggleDone"
    >
        <div class="checkmark" :class="{ done: isDone }">✔</div>
        <div class="ingredient">
            <VueShowdown :markdown="displayIngredient" />
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

.unindented {
    position: relative;
    left: -1.25em;
}

li > .checkmark {
    display: inline;
    visibility: hidden;
}

li > .done {
    visibility: visible;
}

li:hover > .checkmark {
    color: var(--color-primary-element);
    opacity: 0.5;
    visibility: visible;
}

li > .ingredient {
    display: inline;
    padding-left: 1em;
    margin-left: 0.3em;
    text-indent: -1em;
}

.ingredient:deep(a) {
    text-decoration: underline;
}

li > span.icon-error {
    margin-left: 0.3em;
}
</style>
