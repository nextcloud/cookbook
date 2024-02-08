<template>
    <div>
        <ul v-if="instructions">
            <component
                v-for="(item, idx) in instructions"
                :key="`instructions_item-${idx}}`"
                :is="childComponentType(item)"
                v-bind="childComponentProps(item, idx)"
            />
        </ul>
    </div>
</template>

<script setup>
import RecipeInstructionsSection from './RecipeInstructionsSection.vue';
import RecipeInstructionsStep from './RecipeInstructionsStep.vue';
import RecipeInstructionsDirection from './RecipeInstructionsDirection.vue';
import RecipeInstructionsTip from './RecipeInstructionsTip.vue';

defineProps({
    instructions: { type: Array, default: () => [] },
});

/**
 * Determines the type of component to render as the child list item.
 * @param {HowToDirection|HowToStep|HowToTip} item List item to render.
 */
function childComponentType(item) {
    switch (item['@type']) {
        case 'HowToDirection':
            return RecipeInstructionsDirection;
        case 'HowToSection':
            return RecipeInstructionsSection;
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
 * @param {number} index Index of the item in the instructions array.
 */
function childComponentProps(item, index) {
    switch (item['@type']) {
        case 'HowToDirection':
            return {
                direction: item,
                parentIsDone: false,
                parentId: `instructions_item-${index}}`,
            };
        case 'HowToSection':
            return {
                section: item,
                parentIsDone: false,
                parentId: `instructions_item-${index}}`,
            };
        case 'HowToStep':
            return {
                step: item,
                parentIsDone: false,
                parentId: `instructions_item-${index}}`,
            };
        case 'HowToTip':
            return {
                tip: item,
                parentIsDone: false,
                parentId: `instructions_item-${index}}`,
            };
        default:
            return '';
    }
}
</script>

<script>
export default {
    name: 'RecipeInstructions',
};
</script>

<style scoped lang="scss"></style>
