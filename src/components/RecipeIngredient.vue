<template>
    <li
        :class="{
            header: isHeader(),
            unindented: !recipeIngredientsHaveSubgroups,
        }"
        @click="toggleDone"
    >
        <div class="checkmark" :class="{ done: isDone }">✔</div>
        <div class="ingredient" v-html="displayIngredient"></div>
    </li>
</template>

<script>
export default {
    name: "RecipeIngredient",
    props: ["ingredient", "recipeIngredientsHaveSubgroups"],
    data() {
        return {
            headerPrefix: "## ",
            isDone: false,
        }
    },
    computed: {
        displayIngredient: function () {
            if (this.isHeader()) {
                return this.ingredient.substring(this.headerPrefix.length)
            }
            return this.ingredient
        },
    },
    methods: {
        isHeader: function () {
            if (this.ingredient.startsWith(this.headerPrefix)) {
                return true
            }
            return false
        },
        toggleDone: function () {
            this.isDone = !this.isDone
        },
    },
}
</script>

<style scoped>
li {
    display: flex;
}
li.header {
    position: relative;
    left: -1.25em;
    margin-top: 0.25em;
    font-variant: small-caps;
    list-style-type: none;
}

li.unindented {
    position: relative;
    left: -1.25em;
}

li > div.checkmark {
    display: inline;
    visibility: hidden;
}

li > div.done {
    visibility: visible;
}

li:hover > div.checkmark {
    color: var(--color-primary-element);
    opacity: 0.5;
    visibility: visible;
}

li > div.ingredient {
    display: inline;
    padding-left: 1em;
    margin-left: 0.3em;
    text-indent: -1em;
}
</style>
