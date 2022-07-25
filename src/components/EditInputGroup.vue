<template>
    <fieldset>
        <label>{{ fieldLabel }}</label>
        <ul>
            <li
                v-for="(entry, idx) in buffer"
                :key="fieldName + idx"
                :class="fieldType"
            >
                <div v-if="showStepNumber" class="step-number">
                    {{ parseInt(idx) + 1 }}
                </div>
                <input
                    v-if="fieldType === 'text'"
                    ref="list-field"
                    v-model="buffer[idx]"
                    type="text"
                    @keydown="keyDown"
                    @keyup="keyUp"
                    @input="handleInput"
                    @paste="handlePaste"
                    @blur="handleSuggestionsPopupBlur"
                />
                <textarea
                    v-else-if="fieldType === 'textarea'"
                    ref="list-field"
                    v-model="buffer[idx]"
                    @keydown="keyDown"
                    @keyup="keyUp"
                    @input="handleInput"
                    @paste="handlePaste"
                    @blur="handleSuggestionsPopupBlur"
                ></textarea>
                <div class="controls">
                    <button
                        class="icon-arrow-up"
                        :title="t('cookbook', 'Move entry up')"
                        @click="moveEntryUp(idx)"
                    ></button>
                    <button
                        class="icon-arrow-down"
                        :title="t('cookbook', 'Move entry down')"
                        @click="moveEntryDown(idx)"
                    ></button>
                    <button
                        class="icon-add"
                        :title="t('cookbook', 'Insert entry above')"
                        @click="addNewEntry(idx)"
                    ></button>
                    <button
                        class="icon-delete"
                        :title="t('cookbook', 'Delete entry')"
                        @click="deleteEntry(idx)"
                    ></button>
                </div>
                <SuggestionsPopup
                    v-if="
                        suggestionsData !== null &&
                        suggestionsData.fieldIndex === idx
                    "
                    ref="suggestionsPopup"
                    :offset="popupOffset"
                    :focus-index="suggestionsData.focusIndex"
                    :suggestion-options="filteredSelectionOptions"
                />
            </li>
        </ul>
        <button class="button add-list-item" @click="addNewEntry()">
            <span class="icon-add"></span> {{ t("cookbook", "Add") }}
        </button>
    </fieldset>
</template>

<script>
import SuggestionsPopup, { suggestionsPopupMixin } from "./SuggestionsPopup.vue"

const linesMatchAtPosition = (lines, i) =>
    lines.every((line) => line[i] === lines[0][i])
const findCommonPrefix = (lines) => {
    // Find the substring common to the array of strings
    // Inspired from https://stackoverflow.com/questions/68702774/longest-common-prefix-in-javascript

    // Check border cases size 1 array and empty first word)
    if (!lines[0] || lines.length === 1) return lines[0] || ""

    // Loop up index until the characters do not match
    for (let i = 0; ; i++) {
        // If first line has fewer than i characters
        // or the character of each line at position i is not identical
        if (!lines[0][i] || !linesMatchAtPosition(lines, i)) {
            // Then the desired prefix is the substring from the beginning to i
            return lines[0].substr(0, i)
        }
    }
}

export default {
    name: "EditInputGroup",
    components: {
        SuggestionsPopup,
    },
    mixins: [suggestionsPopupMixin],
    props: {
        value: {
            type: Array,
            default: () => [],
        },
        fieldType: {
            type: String,
            default: "text",
        },
        fieldName: {
            type: String,
            default: "",
        },
        showStepNumber: {
            type: Boolean,
            default: false,
        },
        fieldLabel: {
            type: String,
            default: "",
        },
        // If true, add new fields, for newlines in pasted data
        createFieldsOnNewlines: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            // helper variables
            buffer: this.value.slice(),
            lastFocusedFieldIndex: null,
            lastCursorPosition: -1,
            ignoreNextKeyUp: false,
        }
    },
    watch: {
        value: {
            handler() {
                this.buffer = this.value.slice()
            },
            deep: true,
        },
    },
    methods: {
        /* if index = -1, element is added at the end
         * if focusAfterInsert=true, the element is focussed after inserting
         * the content is inserted into the newly created field
         * */
        addNewEntry(index = -1, focusAfterInsert = true, content = "") {
            let entryIdx = index
            if (entryIdx === -1) {
                entryIdx = this.buffer.length
            }
            this.buffer.splice(entryIdx, 0, content)

            if (focusAfterInsert) {
                // const $this = this
                this.$nextTick(function foc() {
                    const listFields = this.$refs["list-field"]
                    if (listFields.length > entryIdx) {
                        listFields[entryIdx].focus()
                    }
                })
            }
        },
        /**
         * Delete an entry from the list
         */
        deleteEntry(index) {
            this.buffer.splice(index, 1)
            this.$emit("input", this.buffer)
        },
        /**
         * Handle typing in input or field or textarea
         */
        handleInput(e) {
            // Exit early if input was pasted. Let `handlePaste` handle this.
            // References:
            // https://developer.mozilla.org/en-US/docs/Web/API/InputEvent/inputType
            // https://rawgit.com/w3c/input-events/v1/index.html#interface-InputEvent-Attributes
            if (
                e.inputType === "insertFromPaste" ||
                e.inputType === "insertFromPasteAsQuotation"
            ) {
                return
            }
            this.$emit("input", this.buffer)
        },
        /**
         * Handle paste in input field or textarea
         */
        handlePaste(e) {
            // get data from clipboard to keep newline characters, which are stripped
            // from the data pasted in the input field (e.target.value)
            const clipboardData = e.clipboardData || window.clipboardData
            const pastedData = clipboardData.getData("Text")
            const inputLinesArray = pastedData
                .split(/\r\n|\r|\n/g)
                // Remove empty lines
                .filter((line) => line.trim() !== "")

            // If only a single line pasted, emit that line and exit
            // Treat it as if that single line was typed
            if (inputLinesArray.length === 1) {
                this.$emit("input", this.buffer)
                return
            }

            // From here on, multiple lines pasted
            if (!this.createFieldsOnNewlines) {
                return
            }

            e.preventDefault()

            const $li = e.currentTarget.closest("li")
            const $ul = $li.closest("ul")
            const $insertedIndex = Array.prototype.indexOf.call(
                $ul.childNodes,
                $li
            )

            // Remove the common prefix from each line of the pasted text
            // For example, if the pasted text uses - for a bullet list
            const prefix = findCommonPrefix(inputLinesArray)

            // Inspired from https://stackoverflow.com/a/25575009
            // Ensure that we are only removing common punctuation
            // For example, if many lines start with the same word, keep that
            // This is more robust than filtering our [a-zA-Z] in the prefix
            // as it should work for any alphabet
            const re =
                /[^\s\u2000-\u206F\u2E00-\u2E7F\\'!"#$%&()*+,\-./:;<=>?@[\]^_`{|}~]/g
            const prefixLength = re.test(prefix)
                ? prefix.search(re)
                : prefix.length

            for (let i = 0; i < inputLinesArray.length; ++i) {
                inputLinesArray[i] = inputLinesArray[i].slice(prefixLength)
            }

            // Replace multiple whitespace characters with a single space
            // This has to be applied to each item in the list if we don't want
            // to accidentally replace all newlines with spaces before splitting
            // Fixes #713
            for (let i = 0; i < inputLinesArray.length; ++i) {
                inputLinesArray[i] = inputLinesArray[i]
                    .trim()
                    .replaceAll(/\s+/g, " ")
            }

            for (let i = 0; i < inputLinesArray.length; ++i) {
                this.addNewEntry(
                    $insertedIndex + i + 1,
                    false,
                    inputLinesArray[i]
                )
            }
            this.$emit("input", this.buffer)

            this.$nextTick(function foc() {
                let indexToFocus = $insertedIndex + inputLinesArray.length
                // Delete field if it's empty
                if (this.buffer[$insertedIndex].trim() === "") {
                    this.deleteEntry($insertedIndex)
                    indexToFocus -= 1
                }
                this.$refs["list-field"][indexToFocus].focus()
            })
        },
        /**
         * Catches enter and key down presses and either adds a new row or focuses the one below
         */
        keyDown(e) {
            // If, e.g., enter has been pressed in the multiselect popup to select an option,
            // ignore the following keyup event
            if (this.ignoreNextKeyUp) {
                this.ignoreNextKeyUp = false
                return
            }

            // Redirect to suggestions handler if in suggestion mode
            if (this.suggestionsData !== null) {
                this.handleSuggestionsPopupKeyDown(e)
                return
            }

            // Allow new lines with shift key
            if (e.shiftKey) {
                // Do nothing here, user wants a line break
                return
            }

            // Repeat events should be ignored
            if (e.repeat) {
                return
            }

            // Only do anything for enter
            if (e.key !== "Enter") {
                return
            }

            // At this point, we are sure that we want to modify the default
            // behaviour
            e.preventDefault()

            // Get the index of the pressed list item
            const $li = e.currentTarget.closest("li")
            const $ul = $li.closest("ul")
            const $pressedLiIndex = Array.prototype.indexOf.call(
                $ul.childNodes,
                $li
            )

            if ($pressedLiIndex >= this.$refs["list-field"].length - 1) {
                this.addNewEntry()
            } else {
                // Focus the next input or textarea
                // We have to check for both, as inputs are used for
                // ingredients and textareas are used for instructions
                $ul.children[$pressedLiIndex + 1]
                    .querySelector("input, textarea")
                    .focus()
            }
        },
        /**
         * Shows the recipe linking popup when # is pressed
         */
        keyUp(e) {
            // If, e.g., enter has been pressed in the multiselect popup to select an option,
            // ignore the following keyup event
            if (this.ignoreNextKeyUp) {
                this.ignoreNextKeyUp = false
                return
            }

            const $li = e.currentTarget.closest("li")
            const $ul = $li.closest("ul")
            const $pressedLiIndex = Array.prototype.indexOf.call(
                $ul.childNodes,
                $li
            )
            this.lastFocusedFieldIndex = $pressedLiIndex
            this.handleSuggestionsPopupKeyUp(e)
        },
        moveEntryDown(index) {
            if (index >= this.buffer.length - 1) {
                // Already at the end of array
                return
            }
            const entry = this.buffer.splice(index, 1)[0]
            if (index + 1 < this.buffer.length) {
                this.buffer.splice(index + 1, 0, entry)
            } else {
                this.buffer.push(entry)
            }
            this.$emit("input", this.buffer)
        },
        moveEntryUp(index) {
            if (index < 1) {
                // Already at the start of array
                return
            }
            const entry = this.buffer.splice(index, 1)[0]
            this.buffer.splice(index - 1, 0, entry)
            this.$emit("input", this.buffer)
        },
        pasteCanceled() {
            const field = this.$refs["list-field"][this.lastFocusedFieldIndex]
            // set cursor back to previous position
            this.$nextTick(function foc() {
                field.focus()
                field.setSelectionRange(
                    this.lastCursorPosition,
                    this.lastCursorPosition
                )
            })
        },
        /**
         * Paste string at the last saved cursor position
         */
        pasteString(str, ignoreKeyup = true) {
            const field = this.$refs["list-field"][this.lastFocusedFieldIndex]

            // insert str
            const content = field.value
            const updatedContent =
                content.slice(0, this.lastCursorPosition) +
                str +
                content.slice(this.lastCursorPosition)
            this.buffer[this.lastFocusedFieldIndex] = updatedContent
            this.$emit("input", this.buffer)

            // set cursor to position after pasted string. Waiting two ticks is necessary for
            // the data to be updated in the field
            this.$nextTick(function delayedFocus() {
                this.$nextTick(function foc() {
                    this.ignoreNextKeyUp = ignoreKeyup
                    field.focus()
                    const newCursorPos = this.lastCursorPosition + str.length
                    field.setSelectionRange(newCursorPos, newCursorPos)
                })
            })
        },
        handleSuggestionSelected(recipeId) {
            this.pasteString(`r/${recipeId} `)
            this.suggestionsData = null
        },
    },
}
</script>

<style scoped>
button {
    width: auto !important;
    padding: 0 1rem 0 0.75rem !important;
}

fieldset {
    position: relative;
    width: 100%;
    margin-bottom: 1em;
}

fieldset > label {
    display: inline-block;
    width: 10em;
    font-weight: bold;
    line-height: 18px;
    word-spacing: initial;
}

fieldset > ul {
    margin-top: 1rem;
}

fieldset > ul + button {
    width: 36px;
    padding: 0;
    float: right;
    text-align: center;
}

fieldset > ul > li {
    display: flex;
    width: 100%;
    padding-right: 0.25em;
    margin: 0 0 1em;
}

.text > input {
    width: 100%;
    margin: 0;
    border-bottom-right-radius: 0;
    border-top-right-radius: 0;
}

li .controls {
    display: flex;
}

li .controls > button {
    width: 34px;
    height: 34px;
    padding: 0;
    border-right-color: transparent;
    border-left-color: transparent;
    margin: 0;
    border-radius: 0;
}

li .controls > button:last-child {
    border-right-width: 1px;
    border-bottom-right-radius: var(--border-radius);
    border-top-right-radius: var(--border-radius);
}

li .controls > button:last-child:not(:hover):not(:focus) {
    border-right-color: var(--color-border-dark);
}

.textarea {
    position: relative;
    z-index: 1;
    top: 1px;
    float: right;
}

.textarea > textarea {
    width: calc(100% - 44px);
    min-height: 10em;
    margin: 0 0 0 44px;
    border-top-right-radius: 0;
    resize: vertical;
}

.textarea::after {
    display: table;
    clear: both;
    content: "";
}

.step-number {
    position: absolute;
    top: 0;
    left: 0;
    width: 36px;
    height: 36px;
    border: 1px solid var(--color-border-dark);
    background-color: var(--color-background-dark);
    background-position: center;
    background-repeat: no-repeat;
    border-radius: 50%;
    line-height: 36px;
    outline: none;
    text-align: center;
}

.icon-arrow-up {
    background-image: var(--icon-triangle-n-000);
}

.icon-arrow-down {
    background-image: var(--icon-triangle-s-000);
}
</style>
