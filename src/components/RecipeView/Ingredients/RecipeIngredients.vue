<template>
    <ul>
        <RecipeIngredient
            v-for="(supply, idx) in scaledSupplies"
            :key="'supply-' + idx"
            :ingredient="asIngredientString(supply)"
            :ingredient-has-correct-syntax="true"
        />
        <RecipeIngredient
            v-for="(ingredient, idx) in scaledIngredients"
            :key="'ingredient-' + idx"
            :ingredient="ingredient"
            :ingredient-has-correct-syntax="ingredientsWithValidSyntax[idx]"
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
import { HowToSupply } from 'cookbook/js/Models/schema';

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
     * List of supply.
     * @type {HowToSupply[]}
     */
    supplies: {
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

// ===================
// Computed properties
// ===================

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

/**
 * List of supplies with their original amounts with recipe references in name and description replaced with Markdown
 * links.
 * @type {Ref<NonNullable<HowToSupply>[] | string[]>}
 */
const parsedSupplies = computedAsync(
    async () => {
        if (props.supplies) {
            const normalizedSuppliesPromises = props.supplies.map(
                async (supply) => {
                    try {
                        const copy = JSON.parse(JSON.stringify(supply));
                        copy.name = await normalizeMarkdown(supply.name);
                        copy.description = await normalizeMarkdown(
                            supply.description,
                        );
                        return copy;
                    } catch (ex) {
                        log.error(ex);
                    }
                    return null;
                },
            );
            // return asCleanedArray(normalizedSuppliesPromises);

            const normalizedSupplies = await Promise.all(
                normalizedSuppliesPromises,
            );
            return asCleanedArray(normalizedSupplies);
        }
        return [];
    },
    props.supplies ? props.supplies.map(() => t('cookbook', 'Loading…')) : [],
);

/**
 * List of normalized supplies with updated amounts based on the current recipe yield.
 * @type {ComputedRef<HowToSupply[]>}
 */
const scaledSupplies = computed(() => {
    const factor = props.currentYield / props.originalYield;
    return parsedSupplies.value.map((supply) => {
        // This is still the loading string 'Loading...'
        if (typeof supply === 'string') {
            return new HowToSupply(supply);
        }
        // No quantity to recalculate
        if (!supply.requiredQuantity) return supply;

        const copy = JSON.parse(JSON.stringify(supply));
        copy.requiredQuantity.value *= factor;
        return copy;
    });
});

const ingredientsWithValidSyntax = computed(() =>
    parsedIngredients.value.map(yieldCalculator.isValidIngredientSyntax),
);

// ===================
// Methods
// ===================

/**
 * The stringified version of the supply to be used as the ingredient.
 * @param {HowToSupply} supply
 */
function asIngredientString(supply) {
    let str = '';
    const quantity = supply?.requiredQuantity;
    if (quantity?.value) {
        str += `${quantity.value}`;
        if (quantity.unitText) {
            str += ` ${quantity.unitText}&nbsp;`;
        }
    }
    if (supply?.name) {
        str += supply.name;
        if (supply.description) {
            str += `, ${supply.description}`;
        }
    }
    return str;
}

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
