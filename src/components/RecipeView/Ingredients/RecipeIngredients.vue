<template>
    <ul>
        <RecipeIngredient
            v-for="(ingredient, idx) in scaledIngredients"
            :key="'ingr' + idx"
            :ingredient="ingredient"
            :ingredient-has-correct-syntax="ingredientsWithValidSyntax[idx]"
            :recipe-ingredients-have-subgroups="recipeIngredientsHaveSubgroups"
            :class="
                ingredientsWithValidSyntax[idx] ? '' : 'ingredient-highlighted'
            "
        />
    </ul>
</template>

<script setup>
import { computed, getCurrentInstance, watch } from 'vue';
import RecipeIngredient from 'cookbook/components/RecipeView/Ingredients/RecipeIngredient.vue';
import yieldCalculator from 'cookbook/js/yieldCalculator';
import normalizeMarkdown from 'cookbook/js/title-rename';
import { showError, showSuccess } from '@nextcloud/dialogs';
import { computedAsync } from '@vueuse/core';
import { asCleanedArray } from 'cookbook/js/helper';

const log = getCurrentInstance().proxy.$log;

const props = defineProps({
    /**
     * List of ingredients.
     * @type {string[]}
     */
    ingredients: {
        type: Array,
        default: () => [],
    },
    /**
     * The yield of the original recipe.
     * @type {number}
     */
    originalYield: {
        type: Number,
        default: 1,
    },
    /**
     * The currently requested yield of the recipe.
     * @type {number}
     */
    currentYield: {
        type: Number,
        default: 1,
    },
});

const emit = defineEmits({
    /** The syntax check for string ingredients has failed for one or more ingredients. */
    'syntax-validity-changed': null,
});

/**
 * Prefix used for ingredient strings to be used as a header for ingredient sections.
 * @type {string}
 */
const headerPrefix = '## ';

// ===================
// Computed properties
// ===================

const recipeIngredientsHaveSubgroups = computed(() => {
    if (props.ingredients && props.ingredients.length > 0) {
        for (let idx = 0; idx < props.ingredients.length; ++idx) {
            if (props.ingredients[idx].startsWith(headerPrefix)) {
                return true;
            }
        }
    }
    return false;
});

/**
 * List of ingredients with their original amounts with recipe references replaced with Markdown links.
 * @type {Ref<NonNullable<string>[]>}
 */
const parsedIngredients = computedAsync(
    async () => {
        if (props.ingredients) {
            const normalizedIngredientsPromises = props.ingredients.map(
                (ingredient) => {
                    try {
                        return normalizeMarkdown(ingredient);
                    } catch (ex) {
                        log.error(ex);
                    }
                    return null;
                },
            );
            const normalizedIngredients = await Promise.all(
                normalizedIngredientsPromises,
            );
            return asCleanedArray(normalizedIngredients);
        }
        return [];
    },
    props.ingredients
        ? props.ingredients.map(() => t('cookbook', 'Loading…'))
        : [],
);

/**
 * List of normalized ingredients with updated amounts based on the current recipe yield.
 * @type {ComputedRef<string[]>}
 */
const scaledIngredients = computed(() =>
    yieldCalculator.recalculateIngredients(
        parsedIngredients.value,
        props.currentYield,
        props.originalYield,
    ),
);

const ingredientsWithValidSyntax = computed(() =>
    parsedIngredients.value.map(yieldCalculator.isValidIngredientSyntax),
);

/**
 * Shows success notification when ingredient copying succeeded.
 * @param item
 */
function showCopySuccess(item) {
    showSuccess(t('cookbook', '{item} copied to clipboard', { item }));
}

/**
 * Shows error notification when ingredient copying failed.
 * @param item
 */
function showCopyError(item) {
    showError(t('cookbook', 'Copying {item} to clipboard failed', { item }));
}

/**
 * Copies the current list of scaled ingredients to the clipboard.
 */
function copyIngredientsToClipboard() {
    const ingredientsToCopy = scaledIngredients.value.join('\n');

    if (navigator.clipboard) {
        navigator.clipboard
            .writeText(ingredientsToCopy)
            .then(() => {
                log.info('JSON array copied to clipboard');
                showCopySuccess(t('cookbook', 'Ingredients'));
            })
            .catch((err) => {
                log.error('Failed to copy JSON array: ', err);
                showCopyError(t('cookbook', 'ingredients'));
            });
    } else {
        // fallback solution
        const input = document.createElement('textarea');
        input.style.position = 'absolute';
        input.style.left = '-1000px';
        input.style.top = '-1000px';
        input.value = ingredientsToCopy;
        document.body.appendChild(input);
        input.select();
        try {
            const successful = document.execCommand('copy');
            if (successful) {
                log.info('JSON array copied to clipboard');
                showCopySuccess(t('cookbook', 'Ingredients'));
            } else {
                log.error('Failed to copy JSON array');
                showCopyError(t('cookbook', 'ingredients'));
            }
        } catch (err) {
            log.error('Failed to copy JSON array: ', err);
            showCopyError(t('cookbook', 'ingredients'));
        }
        document.body.removeChild(input);
    }
}

/**
 * Emit event communicating if the syntax of all ingredients is valid.
 */
watch(
    () => ingredientsWithValidSyntax.value,
    (validIngredients) => {
        emit(
            'syntax-validity-changed',
            validIngredients.every((x) => x),
        );
    },
);

// ===================
// Exposed methods
// ===================

defineExpose({ copyIngredientsToClipboard });
</script>

<style scoped lang="scss">
.ingredient-highlighted {
    font-style: italic;
}
</style>
