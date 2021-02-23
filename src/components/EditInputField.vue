<template>
    <fieldset @keyup="keyPressed">
        <label>
            {{ fieldLabel }}
        </label>
        <markdown-editor
            ref="inputField"
            class="editor"
            v-if="fieldType === 'markdown'"
            v-model="content"
            @input="handleInput"
            toolbar=""
        />
        <input
            ref="inputField"
            v-else-if="fieldType !== 'textarea'"
            :type="fieldType"
            v-model="content"
            @input="handleInput"
        />
        <textarea
            ref="inputField"
            v-if="fieldType === 'textarea'"
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
        value: function () {
            this.content = this.value
        },
    },
    methods: {
        handleInput(e) {
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
                    let cursorPos = JSON.parse(
                        JSON.stringify(
                            this.$refs.inputField.editor.getCursor("start")
                        )
                    )
                    let prevChar = this.$refs.inputField.editor.getRange(
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
                    this.$refs["inputField"].selectionStart
                    let content = this.$refs["inputField"].value
                    let prevChar =
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
            this.$nextTick(function () {
                let field = this.$refs["inputField"]
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
            let field = this.$refs["inputField"]

            if (this.fieldType == "markdown") {
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
                        let newCursorPos = this.lastCursorPosition + str.length
                        field.setSelectionRange(newCursorPos, newCursorPos)
                    })
                })
            }
        },
    },
    mounted() {},
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
}

fieldset > .editor {
    min-height: 10em;
    border-radius: 2;
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
