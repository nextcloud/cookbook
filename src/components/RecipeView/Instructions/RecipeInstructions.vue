<template>
    <div>
        <ol v-if="instructions" class="instructions">
            <component
                :is="childComponentType(item)"
                v-for="(item, idx) in instructions"
                :key="`instructions_item-${idx}}`"
                v-bind="childComponentProps(item, idx)"
            />
        </ol>
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

<style scoped lang="scss">
ol.instructions {
    counter-reset: sectionIndex 0;
}

ol.instructions > li {
    list-style-type: none;
}

ol.instructions > li::before {
    content: counter(sectionIndex);
    counter-increment: sectionIndex 1;
}

ol.instructions > li.instructions-section-root::before {
    content: none;
}

/** Handle counting within sections */
:deep(ol:not(.instructions)) {
    counter-reset: innerSectionIndex 0;
}

:deep(ol:not(.instructions) > li::before) {
    content: counters(innerSectionIndex, '.', decimal);
    counter-increment: innerSectionIndex 1;
}

/** Style Markdown for all children */

:deep(.markdown-instruction ol > li) {
    list-style-type: numbered;
}

:deep(.markdown-instruction ul > li) {
    list-style-type: disc;
}

:deep(.markdown-instruction ol > li),
:deep(.markdown-instruction ul > li) {
    margin-left: 20px;
}

:deep(.markdown-instruction a) {
    text-decoration: underline;
}

/** /end Style Markdown */
</style>
