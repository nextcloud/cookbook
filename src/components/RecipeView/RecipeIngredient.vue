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

const displayIngredient = computed(() => {
    if (isHeader()) {
        return props.ingredient.substring(headerPrefix.length);
    }
    return props.ingredient;
});

const isHeader = () => {
    return props.ingredient.startsWith(headerPrefix);
};

const toggleDone = () => {
    isDone.value = !isDone.value;
};
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
