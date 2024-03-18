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

        <NcPopover
            :focus-trap="false"
            popup-role="dialog"
            class="inline-flex align-items-center"
            popoverBaseClass="sm:w-11/12 md:w-96 px-2"
        >
            <template #trigger>
                <span
                    v-if="!ingredientHasCorrectSyntax"
                    class="ml-1 inline-flex align-items-center"
                >
                    <ErrorIcon
                        :size="18"
                        class="inline-flex align-self-center hover:cursor-pointer"
                    />
                </span>
            </template>
            <template #default>
                <div class="p-2">
                    {{
                        t(
                            'cookbook',
                            'The ingredient cannot be recalculated due to incorrect syntax. Please ensure the syntax follows this format: amount unit ingredient and that a specific number of portions is set for this function to work correctly. Examples: 200 g carrots or 1 pinch of salt.',
                        )
                    }}
                </div>
            </template>
        </NcPopover>
    </li>
</template>

<script setup>
import { computed, ref } from 'vue';
import ErrorIcon from 'icons/AlertOctagonOutline.vue';
import NcPopover from '@nextcloud/vue/dist/Components/NcPopover.js';

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
