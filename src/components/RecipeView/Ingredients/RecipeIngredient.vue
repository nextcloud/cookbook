<template>
    <li
        class="ingredient-item"
        :class="{
            header: isHeader(),
            completed: isDone,
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

const formattedIngredient = computed(() => {
    if (isHeader()) {
        return displayIngredient.value;
    }

    return isDone.value
        ? `~~${displayIngredient.value}~~`
        : `${displayIngredient.value}`;
});
</script>

<script>
export default {
    name: 'RecipeIngredient',
};
</script>

<style scoped>
li.ingredient-item {
    display: flex;

    /* indent ingredient if it has a .header before and is not a header itself */
    .header ~ &:not(.header) {
        margin-left: 0.5em;
    }

    &.completed:not(.header) {
        color: var(--color-text-maxcontrast);
    }

    &:not(.header) {
        & > .ingredient:hover {
            color: var(--color-text-maxcontrast);
            cursor: pointer;
        }
    }
}

.header {
    position: relative;
    margin-top: 0.25em;
    font-variant: small-caps;
    list-style-type: none;
}

li.ingredient-item > .ingredient {
    display: inline;
    padding-left: 1em;
    text-indent: -1em;
}

li.ingredient-item > span.icon-error {
    margin-left: 0.3em;
}

@media print {
    li.ingredient-item > span.icon-error {
        display: none;
    }
}
</style>
