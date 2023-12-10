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

<script setup>
import { computed, ref } from 'vue';

const headerPrefix = '## ';

const props = defineProps({
    /* Ingredient HTML string to display. Content should be sanitized.
     */
    ingredient: {
        type: String,
        default: '',
    },
    ingredientHasCorrectSyntax: {
        type: Boolean,
    },
    recipeIngredientsHaveSubgroups: {
        type: Boolean,
    },
});

/**
 * @type {import('vue').Ref<boolean>}
 */
const isDone = ref(false);

// Methods
const isHeader = () => props.ingredient.startsWith(headerPrefix);

const toggleDone = () => {
    isDone.value = !isDone.value;
};

// Computed properties
const displayIngredient = computed(() => {
    if (isHeader()) {
        return props.ingredient.substring(headerPrefix.length);
    }
    return props.ingredient;
});

const formattedIngredient = computed(() =>
    isDone.value
        ? `~~${displayIngredient.value}~~`
        : `**${displayIngredient.value}**`,
);
</script>

<script>
export default {
    name: 'RecipeIngredient',
};
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

@media print {
    li > span.icon-error {
        display: none;
    }
}
</style>
