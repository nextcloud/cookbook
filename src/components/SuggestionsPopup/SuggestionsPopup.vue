<template>
    <div
        class="suggestions-popup"
        :style="{
            left: `${offset.left}px`,
            top: `${offset.top}px`,
            width: `${width}px`,
        }"
    >
        <ul ref="scroller" class="scroller">
            <li
                v-for="(option, i) in options"
                :key="option.recipe_id"
                class="item"
                :class="{ focused: i === focusIndex }"
            >
                <a
                    class="link"
                    href="#"
                    :data-id="option.recipe_id"
                    @click="handleClick"
                >
                    {{ option.title }}
                </a>
            </li>
        </ul>
    </div>
</template>

<script setup>
import { computed, defineProps, onMounted, ref, watch } from "vue";
const SUGGESTIONS_POPUP_WIDTH = 300;
defineExpose({
    SUGGESTIONS_POPUP_WIDTH
});
const emit = defineEmits(['suggestions-selected']);

const scroller = ref(null);
const width = ref(SUGGESTIONS_POPUP_WIDTH);

const props = defineProps({
    options: {
        type: Array,
        required: true,
    },
    focusIndex: {
        type: Number,
        required: true,
    },
    searchText: {
        type: String,
        required: true,
    },
    field: {
        type: HTMLElement,
        required: true,
    },
    caretPos: {
        type: Object,
        required: true,
    },
});

const offset = computed(() => {
    const caretPos = props.caretPos;
    const field = props.field;

    return {
        left: Math.min(
            props.field.offsetLeft + caretPos.left,
            field.offsetLeft +
            field.offsetWidth -
            SUGGESTIONS_POPUP_WIDTH
        ),
        top: field.offsetTop + caretPos.top + caretPos.height,
    };
});

/**
 * Scroll to centre the focused element in the parent when it changes
 * (with arrow keys, for example)
 */
watch(() => props.focusIndex, (focusIndex) => {
    if(scroller.value === null) return;

    const parentHeight = scroller.value.offsetHeight;
    const childHeight = scroller.value.children[0].offsetHeight;

    // Get the scroll position of the top of the focused element
    const focusedChildTop = childHeight * focusIndex;
    // Get the centre
    const focusedChildMiddle = focusedChildTop + childHeight / 2;
    // Offset to centre in the parent scrolling element
    const parentMiddle = focusedChildMiddle - parentHeight / 2;

    // Scroll to that position
    scroller.value.scrollTo(0, parentMiddle);
});

onMounted(() => {
    scroller.value.scrollTo(0, 0);
});

const clamp = (val, min, max) => Math.min(max, Math.max(min, val));

const handleClick = (e) => {
    e.preventDefault();
    e.stopPropagation();
    emit('suggestions-selected', {
        recipe_id: e.target.getAttribute('data-id'),
    });
};
</script>

<script>
export default {
    name: 'SuggestionsPopup'
};
</script>

<style scoped>
.suggestions-popup {
    position: absolute;
    z-index: 2;
    overflow: hidden;
    border: 1px solid var(--color-background-darker);
    border-radius: 5px;
    background-color: var(--color-main-background);
}

.scroller {
    max-height: 150px;
    overflow-y: auto;
}

.item:not(:last-child) {
    border-bottom: 1px solid var(--color-background-darker);
}

.link {
    display: block;
    overflow: hidden;
    width: 100%;
    padding: 0.2rem 0.5rem;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.link:hover,
.focused > .link {
    background-color: var(--color-primary);
    color: var(--color-primary-text);
}

@media (max-width: 400px) {
    .suggestions-popup {
        left: 0 !important;
        width: 100% !important;
    }
}
</style>
