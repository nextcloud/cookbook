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
                v-for="(option, i) in suggestionOptions"
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

<script>
export const SUGGESTIONS_POPUP_WIDTH = 300

export default {
    name: "SuggestionsPopup",
    props: {
        suggestionOptions: {
            type: Array,
            default: () => [],
        },
        offset: {
            type: Object,
            default: () => ({ top: 0, left: 0 }),
        },
        focusIndex: {
            type: Number,
            default: 0,
        },
    },
    watch: {
        /**
         * Scroll to centre the focused element in the parent when it changes
         * (with arrow keys, for example)
         */
        focusIndex(focusIndex) {
            const parentHeight = this.$refs.scroller.offsetHeight
            const childHeight = this.$refs.scroller.children[0].offsetHeight

            // Get the scroll position of the top of the focused element
            const focusedChildTop = childHeight * focusIndex
            // Get the centre
            const focusedChildMiddle = focusedChildTop + childHeight / 2
            // Offset to centre in the parent scrolling element
            const parentMiddle = focusedChildMiddle - parentHeight / 2

            // Scroll to that position
            this.$refs.scroller.scrollTo(0, parentMiddle)
        },
    },
    created() {
        this.width = SUGGESTIONS_POPUP_WIDTH
    },
    methods: {
        handleClick(e) {
            e.preventDefault()
            e.stopPropagation()
            this.$parent.$emit("suggestions-selected", {
                recipe_id: e.target.getAttribute("data-id"),
            })
        },
    },
}
</script>

<style scoped>
.suggestions-popup {
    position: absolute;
    z-index: 2;
    overflow: hidden;
    border: 1px solid var(--color-background-darker);
    background-color: var(--color-main-background);
    border-radius: 5px;
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
    width: 100%;
    padding: 0.2rem 0.5rem;
}

.link:hover,
.focused > .link {
    background-color: var(--color-primary);
    color: var(--color-primary-text);
}
</style>
