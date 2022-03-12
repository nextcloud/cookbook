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
            if (this.referencePopupEnabled && e.key === "#") {
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
    display: flex;
    flex-direction: column;
    margin-bottom: 1em;
}

@media (min-width: 1200px) {
    fieldset {
        flex-direction: row;
    }
}

fieldset > * {
    margin: 0;
}

fieldset > label {
    display: inline-block;
    width: 10em;
    height: 34px;
    font-weight: bold;
    line-height: 17px;
    vertical-align: top;
}

fieldset > input,
fieldset > textarea {
    flex: 1;
    width: revert;
}

fieldset > input[type="number"] {
    width: 5em;
    flex-grow: 0;
}

fieldset > textarea {
    min-height: 10em;
    resize: vertical;
}

fieldset > .editor {
    min-height: 10em;
    flex: 1;
    border-radius: 2px;
    resize: vertical;
}

/*
Hack to overwrite the heavy-handed global unscoped styles of Nextcloud core
that cause our markdown editor CodeMirror to behave strangely on mobile
See: https://github.com/nextcloud/cookbook/issues/908

Use /deep/ because >>> did not work for some reason
*/
.editor /deep/ div[contenteditable="true"] {
    width: revert;
    min-height: revert;
    margin: revert;
    padding: revert;
    font-size: revert;
    background-color: revert;
    color: revert;
    border: revert;
    outline: revert;
    border-radius: revert;
}

</style>
