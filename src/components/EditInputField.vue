<template>
    <fieldset @keyup="keyPressed">
        <label>
            {{ fieldLabel }}
        </label>
        <markdown-editor
            v-if="fieldType === 'markdown'"
            ref="inputField"
            v-model="content"
            class="editor"
            toolbar=""
            @input="handleInput"
        />
        <input
            v-else-if="fieldType !== 'textarea'"
            ref="inputField"
            v-model="content"
            :type="fieldType"
            @input="handleInput"
        />
        <textarea
            v-if="fieldType === 'textarea'"
            ref="inputField"
            v-model="content"
            @input="handleInput"
        />
    </fieldset>
</template>

<script>
export default {
    name: "EditInputField",
    props: {
        fieldLabel: {
            type: String,
            default: "",
        },
        fieldType: {
            type: String,
            default: "",
        },
        referencePopupEnabled: {
            type: Boolean,
            default: false,
        },
        // Value (passed in v-model)
        // eslint-disable-next-line vue/require-prop-types
        value: {
            default: "",
            required: true,
        },
    },
    data() {
        return {
            content: "",
            lastCursorPosition: -1,
        }
    },
    watch: {
        value() {
            this.content = this.value
        },
    },
    methods: {
        handleInput() {
            this.$emit("input", this.content)
        },
        /**
         * Catches # key down presses and opens recipe-references dialog
         */
        keyPressed(e) {
            // Using keyup for trigger will prevent repeat triggering if key is held down
            if (this.referencePopupEnabled && e.keyCode === 51) {
                e.preventDefault()
                // Check if the letter before the hash
                if (this.fieldType === "markdown") {
                    // for reference: https://codemirror.net/doc/manual.html#api
                    const cursorPos = JSON.parse(
                        JSON.stringify(
                            this.$refs.inputField.editor.getCursor("start")
                        )
                    )
                    const prevChar = this.$refs.inputField.editor.getRange(
                        { line: cursorPos.line, ch: cursorPos.ch - 2 },
                        { line: cursorPos.line, ch: cursorPos.ch - 1 }
                    )
                    if (
                        cursorPos.ch === 1 ||
                        prevChar === " " ||
                        prevChar === "\n" ||
                        prevChar === "\r"
                    ) {
                        // beginning of line
                        this.$parent.$emit("showRecipeReferencesPopup", {
                            context: this,
                        })
                        this.lastCursorPosition = cursorPos
                    }
                } else {
                    const cursorPos = this.$refs.inputField.selectionStart
                    const content = this.$refs.inputField.value
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
                        this.lastCursorPosition = cursorPos
                    }
                }
            }
        },
        pasteCanceled() {
            // set cursor to position after pasted string
            this.$nextTick(function foc() {
                const field = this.$refs.inputField
                if (this.fieldType === "markdown") {
                    field.editor.setCursor(this.lastCursorPosition)
                    field.editor.focus()
                } else {
                    field.focus()
                    field.setSelectionRange(
                        this.lastCursorPosition,
                        this.lastCursorPosition
                    )
                }
            })
        },
        /**
         * Paste string at the last saved cursor position
         */
        pasteString(str) {
            const field = this.$refs.inputField

            if (this.fieldType === "markdown") {
                // insert at last cursor position
                field.editor.replaceRange(str, {
                    line: this.lastCursorPosition.line,
                    ch: this.lastCursorPosition.ch,
                })
                this.$emit("input", this.content)
                this.$nextTick(() => {
                    this.$nextTick(() => {
                        field.editor.focus()
                        field.editor.setCursor({
                            line: this.lastCursorPosition.line,
                            ch: this.lastCursorPosition.ch + str.length,
                        })
                    })
                })
            } else {
                // insert str
                this.content =
                    this.content.slice(0, this.lastCursorPosition) +
                    str +
                    this.content.slice(this.lastCursorPosition)
                this.$emit("input", this.content)

                // set cursor to position after pasted string. Waiting two ticks is necessary for
                // the data to be updated in the field
                this.$nextTick(() => {
                    this.$nextTick(() => {
                        field.focus()
                        const newCursorPos =
                            this.lastCursorPosition + str.length
                        field.setSelectionRange(newCursorPos, newCursorPos)
                    })
                })
            }
        },
    },
}
</script>

<style scoped>
fieldset {
    margin-bottom: 1em;
}

fieldset > * {
    margin: 0;
    float: left;
}

fieldset > label {
    display: inline-block;
    width: 10em;
    height: 34px;
    font-weight: bold;
    line-height: 17px;
    vertical-align: top;
}
@media (max-width: 1199px) {
    fieldset > label {
        display: block;
        float: none;
    }
}

fieldset > input,
fieldset > textarea {
    width: calc(100% - 11em);
}

fieldset > textarea {
    min-height: 10em;
    resize: vertical;
}

fieldset > .editor {
    width: calc(100% - 11em);
    min-height: 10em;
    border-radius: 2px;
    resize: vertical;
}

@media (max-width: 1199px) {
    fieldset > input,
    fieldset > textarea,
    fieldset > .editor {
        width: 100%;
    }
}
</style>
