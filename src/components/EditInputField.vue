<template>
    <fieldset>
        <label>
            {{ fieldLabel }}
        </label>
        <textarea
            v-if="fieldType === 'textarea' || fieldType === 'markdown'"
            ref="inputField"
            v-model="content"
            @input="handleInput"
            @keydown="keyDown"
            @keyup="handleSuggestionsPopupKeyUp"
            @blur="handleSuggestionsPopupBlur"
        />
        <div v-else>
            <slot />
            <input
                v-if="!hide"
                ref="inputField"
                v-model="content"
                :type="fieldType"
                @input="handleInput"
                @keydown="keyDown"
                @keyup="handleSuggestionsPopupKeyUp"
                @blur="handleSuggestionsPopupBlur"
            />
        </div>
        <SuggestionsPopup
            v-if="suggestionsData !== null"
            ref="suggestionsPopup"
            v-bind="suggestionsData"
            :options="filteredSuggestionOptions"
        />
    </fieldset>
</template>

<script>
import SuggestionsPopup, { suggestionsPopupMixin } from "./SuggestionsPopup.vue"

export default {
    name: "EditInputField",
    components: {
        SuggestionsPopup,
    },
    mixins: [suggestionsPopupMixin],
    props: {
        fieldLabel: {
            type: String,
            default: "",
        },
        fieldType: {
            type: String,
            default: "",
        },
        // Value (passed in v-model)
        // eslint-disable-next-line vue/require-prop-types
        value: {
            default: "",
            required: true,
        },
        hide: {
            type: Boolean,
            default: false,
            required: false,
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
        keyDown(e) {
            // Redirect to suggestions handler if in suggestion mode
            if (this.suggestionsData !== null) {
                this.handleSuggestionsPopupKeyDown(e)
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

fieldset > div,
fieldset > textarea {
    width: revert;
    flex: 1;
}

fieldset > div > input {
    width: 100%;
}

fieldset input[type="number"] {
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
    padding: revert;
    border: revert;
    margin: revert;
    background-color: revert;
    border-radius: revert;
    color: revert;
    font-size: revert;
    outline: revert;
}
</style>
