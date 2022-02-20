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
                    @keyup="keyPressed"
                    @input="handleInput"
                    @paste="handlePaste"
                />
                <textarea
                    v-else-if="fieldType === 'textarea'"
                    ref="list-field"
                    v-model="buffer[idx]"
                    @keyup="keyPressed"
                    @input="handleInput"
                    @paste="handlePaste"
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
            </li>
        </ul>
        <button class="button add-list-item" @click="addNewEntry()">
            <span class="icon-add"></span> {{ t("cookbook", "Add") }}
        </button>
    </fieldset>
</template>

<script>
export default {
    name: "EditInputGroup",
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
        referencePopupEnabled: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            // helper variables
            buffer: this.value.slice(),
            contentPasted: false,
            singleLinePasted: false,
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
        handleInput() {
            // wait a tick to check if content was typed or pasted
            this.$nextTick(function handlePastedOrTyped() {
                if (this.contentPasted) {
                    this.contentPasted = false

                    if (this.singleLinePasted) {
                        this.$emit("input", this.buffer)
                    }

                    return
                }
                this.$emit("input", this.buffer)
            })
        },
        /**
         * Handle paste in input field or textarea
         */
        handlePaste(e) {
            this.contentPasted = true
            if (!this.createFieldsOnNewlines) {
                return
            }

            // get data from clipboard to keep newline characters, which are stripped
            // from the data pasted in the input field (e.target.value)
            const clipboardData = e.clipboardData || window.clipboardData
            const pastedData = clipboardData.getData("Text")
            const inputLinesArray = pastedData
                .split(/\r\n|\r|\n/g)
                // Remove empty lines
                .filter((line) => line.trim() !== "")

            if (inputLinesArray.length === 1) {
                this.singleLinePasted = true
                return
            }
            this.singleLinePasted = false

            e.preventDefault()

            const $li = e.currentTarget.closest("li")
            const $ul = $li.closest("ul")
            const $insertedIndex = Array.prototype.indexOf.call(
                $ul.childNodes,
                $li
            )

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
                this.contentPasted = false
            })
        },
        /**
         * Catches enter and key down presses and either adds a new row or focuses the one below
         */
        keyPressed(e) {
            // If, e.g., enter has been pressed in the multiselect popup to select an option,
            // ignore the following keyup event
            if (this.ignoreNextKeyUp) {
                this.ignoreNextKeyUp = false
                return
            }

            // Allow new lines with shift key
            if ((e.keyCode === 13 || e.keyCode === 10) && e.shiftKey) {
                // Do nothing here, user wants a line break
                return
            }

            // Using keyup for trigger will prevent repeat triggering if key is held down
            if (
                e.keyCode === 13 ||
                e.keyCode === 10 ||
                (this.referencePopupEnabled && e.key === "#")
            ) {
                e.preventDefault()
                const $li = e.currentTarget.closest("li")
                const $ul = $li.closest("ul")
                const $pressedLiIndex = Array.prototype.indexOf.call(
                    $ul.childNodes,
                    $li
                )

                if (e.keyCode === 13 || e.keyCode === 10) {
                    if (
                        $pressedLiIndex >=
                        this.$refs["list-field"].length - 1
                    ) {
                        this.addNewEntry()
                    } else {
                        $ul.children[$pressedLiIndex + 1]
                            .getElementsByTagName("input")[0]
                            .focus()
                    }
                } else if (this.referencePopupEnabled && e.key === "#") {
                    e.preventDefault()
                    const elm = this.$refs["list-field"][$pressedLiIndex]
                    // Check if the letter before the hash
                    const cursorPos = elm.selectionStart
                    const content = elm.value
                    const prevChar =
                        cursorPos > 1 ? content.charAt(cursorPos - 2) : ""

                    if (
                        cursorPos === 1 ||
                        prevChar === " " ||
                        prevChar === "\n" ||
                        prevChar === "\r"
                    ) {
                        // Show dialog to select recipe
                        this.$parent.$emit("showRecipeReferencesPopup", {
                            context: this,
                        })
                        this.lastFocusedFieldIndex = $pressedLiIndex
                        this.lastCursorPosition = cursorPos
                    }
                }
            }
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
    },
}
</script>

<style scoped>
button {
    width: auto !important;
    padding: 0 1rem 0 0.75rem !important;
}

fieldset {
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
