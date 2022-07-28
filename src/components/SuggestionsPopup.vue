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

<script>
import { position as caretPosition } from "caret-pos"

const clamp = (val, min, max) => Math.min(max, Math.max(min, val))

export const SUGGESTIONS_POPUP_WIDTH = 300

export const suggestionsPopupMixin = {
    methods: {
        /**
         * Handle something selected by click or by `Enter`
         * Insert the reference with `pasteString`,
         * which should exist everywhere this mixin is used
         * (`EditInputGroup` and `EditInputField`) and close the popup
         */
        handleSuggestionsPopupSelected(recipeId) {
            const { field, hashPosition } = this.suggestionsData
            const { value } = field
            const before = value.slice(0, hashPosition)
            const after = value.slice(field.selectionStart)
            const replace = `r/${recipeId}` 
            const newValue =  `${before}${replace}${after}`
            if (this.buffer ?? false) {
                this.buffer[this.suggestionsData.fieldIndex] = newValue
                this.$emit("input", this.buffer)
            } else {
                this.$emit("input", newValue)
            }
            this.handleSuggestionsPopupCancel()
            // set cursor to position after pasted string. Waiting two ticks is necessary for
            // the data to be updated in the field
            this.$nextTick(() => {
                this.$nextTick(() => {
                    field.focus()
                    const newCursorPos = hashPosition + replace.length
                    field.setSelectionRange(newCursorPos, newCursorPos)
                })
            })
        },
        /**
         * Handle keyups events when the suggestions popup is open
         * The event will be sent here from the normal keydown handler
         * if suggestionsPopupVisible
         */
        handleSuggestionsPopupOpenKeyUp(e, cursorPos) {
            const caretPos = caretPosition(e.target, {
                customPos: this.suggestionsData.hashPosition - 1,
            })

            // Only update the popover position if the line changes (caret pos y changes)
            if (caretPos.top !== this.suggestionsData.caretPos.top) {
                this.suggestionsData.caretPos = caretPos
            }

            // Cancel suggestion popup on whitespace or caret movement
            if (
                [
                    " ",
                    "\t",
                    "#",
                    "ArrowLeft",
                    "ArrowRight",
                    "Home",
                    "End",
                    "PageUp",
                    "PageDown",
                    "Escape",
                ].includes(e.key)
            ) {
                this.handleSuggestionsPopupCancel()
                return
            }

            // Cancel suggestions popup if hash deleted
            if (cursorPos < this.suggestionsData.hashPosition) {
                this.handleSuggestionsPopupCancel()
                return
            }

            // Update the search text
            // Slice the input from the position of the "#" to the caret position
            const { hashPosition, field } = this.suggestionsData
            const searchText = field.value.slice(hashPosition, cursorPos)
            this.suggestionsData = {
                ...this.suggestionsData,
                searchText,
            }
        },
        getClosestListItemIndex(field) {
            const $li = field.closest("li")
            const $ul = $li?.closest("ul")
            if (!$ul) return null

            return Array.prototype.indexOf.call($ul.childNodes, $li)
        },
        /**
         * Shows the recipe linking popup when # is pressed
         */
        handleSuggestionsPopupKeyUp(e) {
            const field = e.currentTarget
            const cursorPos = field.selectionStart

            // No suggestion options means suggestions are disabled for this input
            if (!this.suggestionOptions) return

            if (this.suggestionsPopupVisible) {
                this.handleSuggestionsPopupOpenKeyUp(e, cursorPos)
                return
            }

            // Only do anything for # key
            if (e.key !== "#") return

            // Show the popup only if the # was inserted at the very
            // beggining of the input or after any whitespace character
            if (
                !(
                    cursorPos === 1 ||
                    /\s/.test(field.value.charAt(cursorPos - 2))
                )
            ) {
                return
            }

            // Show dialog to select recipe
            const caretPos = caretPosition(field, { customPos: cursorPos - 1 })
            this.suggestionsData = {
                field,
                searchText: "",
                caretPos,
                focusIndex: 0,
                hashPosition: cursorPos,
                blurred: false,
                fieldIndex: this.getClosestListItemIndex(field),
            }
            this.lastCursorPosition = cursorPos
        },
        handleSuggestionsPopupKeyDown(e) {
            if (!this.suggestionsPopupVisible) return

            this.handleSuggestionsPopupOpenKeyDown(e)
        },
        /**
         * Handle keydown events for suggestions popup
         * The event will be sent here from the normal keydown handler
         * if suggestionsPopupVisible
         */
        handleSuggestionsPopupOpenKeyDown(e) {
            // Handle switching the focused option with up/down keys
            if (["ArrowUp", "ArrowDown"].includes(e.key)) {
                e.preventDefault()

                // Increment/decrement focuse index based on which key was pressed
                // and constrain between 0 and length - 1
                const focusIndex = clamp(
                    this.suggestionsData.focusIndex +
                        {
                            ArrowUp: -1,
                            ArrowDown: +1,
                        }[e.key],
                    0,
                    this.suggestionOptions.length - 1
                )
                this.suggestionsData = {
                    ...this.suggestionsData,
                    focusIndex,
                }
                return
            }

            // Handle selecting the current option when enter is pressed
            if (e.key === "Enter") {
                e.preventDefault()
                const { focusIndex } = this.suggestionsData
                const selection = this.filteredSuggestionOptions[focusIndex]
                this.handleSuggestionsPopupSelected(selection.recipe_id)
            }
        },
        /**
         * Recover suggestions popup on focus
         */
        handleSuggestionsPopupFocus(e) {
            console.log('focus', e, JSON.stringify(this.suggestionsData))
            if (this.suggestionsData?.blurred) {
                this.suggestionsData.blurred = false
            }
        },
        /**
         * Cancel selection if input gets blurred
         */
        handleSuggestionsPopupBlur(e) {
            console.log('blur', e, JSON.stringify(this.suggestionsData))
            if (!this.suggestionsPopupVisible || !this.$refs.suggestionsPopup) {
                return
            }

            // Get reference to the popup
            // There is only ever one at a time, but if it's defined in a v-for
            // the reference will be an array of one
            const $suggestionsPopup = Array.isArray(this.$refs.suggestionsPopup)
                ? this.$refs.suggestionsPopup[0].$el
                : this.$refs.suggestionsPopup.$el

            // Do not cancel suggestions if the new focused element (e.relatedTarget)
            // is a child of the suggestions popup
            // That is the case when clicking an option in the suggestions popup,
            // and cancelling too early prevents the option from being properly selected
            if ($suggestionsPopup.contains(e.relatedTarget)) {
                return
            }
            this.suggestionsData.blurred = true
        },
        handleSuggestionsPopupCancel() {
            this.suggestionsData = null
        },
    },
    computed: {
        suggestionsPopupVisible() {
            return this.suggestionsData !== null && !this.suggestionsData.blurred
        },
        filteredSuggestionOptions() {
            const { searchText } = this.suggestionsData

            return this.suggestionOptions.filter(
                (option) =>
                    searchText === "" ||
                    option.title
                        .toLowerCase()
                        .includes(searchText.toLowerCase())
            )
        },
    },
    data() {
        return {
            suggestionsData: null,
        }
    },
    props: {
        suggestionOptions: {
            type: Array,
        },
    },
    mounted() {
        this.$on("suggestions-selected", (opt) => {
            this.handleSuggestionsPopupSelected(opt.recipe_id)
        })
    },
}

export default {
    name: "SuggestionsPopup",
    props: {
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
    },
    computed: {
        offset() {
            const { caretPos, field } = this

            return {
                left: Math.min(
                    field.offsetLeft + caretPos.left,
                    field.offsetLeft +
                        field.offsetWidth -
                        SUGGESTIONS_POPUP_WIDTH
                ),
                top: field.offsetTop + caretPos.top + caretPos.height,
            }
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
    mounted() {
        this.$refs.scroller.scrollTo(0, 0)
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
