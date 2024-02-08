<template>
    <li class="instructions-section-root">
        <fieldset v-if="section" class="instructions-section">
            <legend v-if="section.name" class="instructions-section__title">
                {{ section.name }}
            </legend>
            <div v-if="section.description">{{ section.description }}</div>
            <!--        TODO Add support for missing properties -->
            <!--        <div>{{ section.timeRequired }}</div>-->
            <!--        <div>{{ section.image }}</div>-->
            <ol v-if="section.itemListElement">
                <component
                    :is="childComponentType(item)"
                    v-bind="childComponentProps(item)"
                    v-for="(item, idx) in section.itemListElement"
                    :key="`${parentId}_section-${item.position}-${item['name']}_item-${idx}`"
                >
                </component>
            </ol>
        </fieldset>
    </li>
</template>

<script setup>
import RecipeInstructionsDirection from 'cookbook/components/RecipeView/Instructions/RecipeInstructionsDirection.vue';
import RecipeInstructionsTip from 'cookbook/components/RecipeView/Instructions/RecipeInstructionsTip.vue';
import RecipeInstructionsStep from 'cookbook/components/RecipeView/Instructions/RecipeInstructionsStep.vue';

defineProps({
    /** @type {HowToSection|null} */
    section: {
        type: Object,
        default: null,
    },
    /** @type {string} Identifier of the parent to be used as key. */
    parentId: {
        type: String,
        default: '',
    },
    /** @type {bool} The parent is marked as completed */
    parentIsDone: {
        type: Boolean,
        default: false,
    },
});

/**
 * Determines the type of component to render as the child list item.
 * @param {HowToDirection|HowToStep|HowToTip} item List item to render.
 */
function childComponentType(item) {
    switch (item['@type']) {
        case 'HowToDirection':
            return RecipeInstructionsDirection;
        case 'HowToStep':
            return RecipeInstructionsStep;
        case 'HowToTip':
            return RecipeInstructionsTip;
        default:
            return '';
    }
}

/**
 * Determines the props to pass for the type of component to render as the child list item.
 * @param {HowToDirection|HowToStep|HowToTip} item List item to render.
 */
function childComponentProps(item) {
    switch (item['@type']) {
        case 'HowToDirection':
            return { direction: item, parentIsDone: false };
        case 'HowToStep':
            return { step: item, parentIsDone: false };

        case 'HowToTip':
            return { tip: item, parentIsDone: false };
        default:
            return '';
    }
}
</script>

<style scoped lang="scss">
li.instructions-section-root {
    list-style-type: none;
}

.instructions-section {
    padding: 1.5em 1.8em 0.5em;
    border: solid 1px var(--color-border);
    border-radius: 5px;
    margin: 1em 0 3em;

    .instructions-section__title {
        padding: 0 0.5em;
        font-size: 1.3em;
    }
}

ol {
    counter-reset: item;
    list-style-type: none;
}

ol > li {
    counter-increment: item;
}

ol > li::before {
    content: counters(item, '.');
}
</style>
