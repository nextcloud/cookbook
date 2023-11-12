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
                    ref="listField"
                    v-model="buffer[idx]"
                    type="text"
                    @keydown="keyDown"
                    @keyup="keyUp"
                    @input="handleInput"
                    @paste="handlePaste"
                    @focus="handleSuggestionsPopupFocus"
                    @blur="handleSuggestionsPopupBlur"
                    @mouseup="handleSuggestionsPopupMouseUp"
                />
                <textarea
                    v-else-if="fieldType === 'textarea'"
                    ref="listField"
                    v-model="buffer[idx]"
                    @keydown="keyDown"
                    @keyup="keyUp"
                    @input="handleInput"
                    @paste="handlePaste"
                    @focus="handleSuggestionsPopupFocus"
                    @blur="handleSuggestionsPopupBlur"
                    @mouseup="handleSuggestionsPopupMouseUp"
                ></textarea>
                <div class="controls">
                    <button
                        class=""
                        :title="t('cookbook', 'Move entry up')"
                        @click="moveEntryUp(idx)"
                    >
                        <TriangleUpIcon :size="25" />
                    </button>
                    <button
                        class=""
                        :title="t('cookbook', 'Move entry down')"
                        @click="moveEntryDown(idx)"
                    >
                        <TriangleDownIcon :size="25" />
                    </button>
                    <button
                        class="icon-add pad-icon"
                        :title="t('cookbook', 'Insert entry above')"
                        @click="addNewEntry(idx)"
                    ></button>
                    <button
                        class="icon-delete pad-icon"
                        :title="t('cookbook', 'Delete entry')"
                        @click="deleteEntry(idx)"
                    ></button>
                </div>
                <SuggestionsPopup
                    v-if="
                        suggestionsPopupVisible &&
                        suggestionsData.fieldIndex === idx
                    "
                    ref="suggestionsPopupElement"
                    v-bind="suggestionsData"
                    :options="filteredSuggestionOptions"
                    v-on:suggestions-selected="handleSuggestionsPopupSelectedEvent"
                />
            </li>
        </ul>
        <button class="button add-list-item pad-icon" @click="addNewEntry()">
            <span class="icon-add"></span> {{ t("cookbook", "Add") }}
        </button>
    </fieldset>
</template>

<script setup>
import { getCurrentInstance, nextTick, ref, watch } from "vue";
import TriangleUpIcon from "icons/TriangleSmallUp.vue";
import TriangleDownIcon from "icons/TriangleSmallDown.vue";

import SuggestionsPopup from '../SuggestionsPopup/SuggestionsPopup';
import useSuggestionPopup from '../SuggestionsPopup/suggestionsPopupComposable';
const log = getCurrentInstance().proxy.$log;

const emit = defineEmits(['input']);

// Template refs
const listField = ref(null);

const suggestionsData = ref(null);

const props = defineProps({
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
    suggestionOptions: {
        type: Array
    },
});

// helper variables
const buffer = ref(props.value.slice());
const lastFocusedFieldIndex = ref(null);
const lastCursorPosition = ref(-1);
const ignoreNextKeyUp = ref(false);

watch(() => props.value, (newValue) => {
    buffer.value = newValue.slice();
});

// deconstruct composable
let {
    handleSuggestionsPopupCancel,
    suggestionsPopupVisible,
    filteredSuggestionOptions,
    suggestionsPopupElement,
    // mounted
    handleSuggestionsPopupSelected,
    handleSuggestionsPopupOpenKeyUp,
    getClosestListItemIndex,
    handleSuggestionsPopupKeyUp,
    handleSuggestionsPopupKeyDown,
    handleSuggestionsPopupOpenKeyDown,
    handleSuggestionsPopupFocus,
    handleSuggestionsPopupBlur,
    handleSuggestionsPopupMouseUp,
    handleSuggestionsPopupSelectedEvent,
} = useSuggestionPopup(suggestionsPopupElement, lastCursorPosition, suggestionsData, buffer, emit, log, props);


watch(() => props.value,
    (val) => {
        buffer.value = val.slice();
    },
    { deep: true }
);

const linesMatchAtPosition = (lines, i) =>
    lines.every((line) => line[i] === lines[0][i]);

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
};

/* if index = -1, element is added at the end
 * if focusAfterInsert=true, the element is focussed after inserting
 * the content is inserted into the newly created field
 * */
const addNewEntry = async (index = -1, focusAfterInsert = true, content = "") => {
    let entryIdx = index
    if (entryIdx === -1) {
        entryIdx = buffer.value.length
    }
    buffer.value.splice(entryIdx, 0, content)

    if (focusAfterInsert) {
        await nextTick();
        const listFields = listField.value;
        if (listFields.length > entryIdx) {
            listFields[entryIdx].focus();
        }
    }
};

/**
 * Delete an entry from the list
 */
const deleteEntry = (index) => {
    buffer.value.splice(index, 1);
    emit("input", buffer.value);
};

/**
 * Handle typing in input or field or textarea
 */
const handleInput = (e) => {
    // Exit early if input was pasted. Let `handlePaste` handle this.
    // References:
    // https://developer.mozilla.org/en-US/docs/Web/API/InputEvent/inputType
    // https://rawgit.com/w3c/input-events/v1/index.html#interface-InputEvent-Attributes
    if (
        e.inputType === "insertFromPaste" ||
        e.inputType === "insertFromPasteAsQuotation"
    ) {
        return;
    }
    emit("input", buffer.value);
};

/**
 * Handle paste in input field or textarea
 */
const handlePaste = async (e) => {
    // get data from clipboard to keep newline characters, which are stripped
    // from the data pasted in the input field (e.target.value)
    const clipboardData = e.clipboardData || window.clipboardData;
    const pastedData = clipboardData.getData("Text");
    const inputLinesArray = pastedData
        .split(/\r\n|\r|\n/g)
        // Remove empty lines
        .filter((line) => line.trim() !== "");

    // If only a single line pasted, emit that line and exit
    // Treat it as if that single line was typed
    if (inputLinesArray.length === 1) {
        emit("input", buffer.value);
        return;
    }

    // From here on, multiple lines pasted
    if (!props.createFieldsOnNewlines) {
        return;
    }

    e.preventDefault()

    const $li = e.currentTarget.closest("li");
    const $ul = $li.closest("ul");
    const $insertedIndex = Array.prototype.indexOf.call(
        $ul.childNodes,
        $li,
    );

    // Remove the common prefix from each line of the pasted text
    // For example, if the pasted text uses - for a bullet list
    const prefix = findCommonPrefix(inputLinesArray);

    // Inspired from https://stackoverflow.com/a/25575009
    // Ensure that we are only removing common punctuation
    // For example, if many lines start with the same word, keep that
    // This is more robust than filtering our [a-zA-Z] in the prefix
    // as it should work for any alphabet
    const re =
        /[^\s\u2000-\u206F\u2E00-\u2E7F\\'!"#$%&()*+,\-./:;<=>?@[\]^_`{|}~]/g;
    const prefixLength = re.test(prefix)
        ? prefix.search(re)
        : prefix.length;

    for (let i = 0; i < inputLinesArray.length; ++i) {
        inputLinesArray[i] = inputLinesArray[i].slice(prefixLength);
    }

    // Replace multiple whitespace characters with a single space
    // This has to be applied to each item in the list if we don't want
    // to accidentally replace all newlines with spaces before splitting
    // Fixes #713
    for (let i = 0; i < inputLinesArray.length; ++i) {
        inputLinesArray[i] = inputLinesArray[i]
            .trim()
            .replaceAll(/\s+/g, " ");
    }

    for (let i = 0; i < inputLinesArray.length; ++i) {
        await addNewEntry(
            $insertedIndex + i + 1,
            false,
            inputLinesArray[i],
        );
    }
    emit("input", buffer.value)

    await nextTick();
    let indexToFocus = $insertedIndex + inputLinesArray.length
    // Delete field if it's empty
    if (buffer.value[$insertedIndex].trim() === "") {
        deleteEntry($insertedIndex);
        indexToFocus -= 1;
    }
    // this.$refs["list-field"][indexToFocus].focus()
    listField[indexToFocus].focus();
};

/**
 * Catches enter and key down presses and either adds a new row or focuses the one below
 */
const keyDown = async (e) => {
    // If, e.g., enter has been pressed in the multiselect popup to select an option,
    // ignore the following keyup event
    if (ignoreNextKeyUp.value) {
        ignoreNextKeyUp.value = false;
        return;
    }

    // Redirect to suggestions handler if in suggestion mode
    if (suggestionsPopupVisible) {
        handleSuggestionsPopupKeyDown(e);
        return;
    }

    // Allow new lines with shift key
    if (e.shiftKey) {
        // Do nothing here, user wants a line break
        return;
    }

    // Repeat events should be ignored
    if (e.repeat) {
        return;
    }

    // Only do anything for enter
    if (e.key !== "Enter") {
        return;
    }

    // At this point, we are sure that we want to modify the default
    // behaviour
    e.preventDefault();

    // Get the index of the pressed list item
    const $li = e.currentTarget.closest("li");
    const $ul = $li.closest("ul");
    const $pressedLiIndex = Array.prototype.indexOf.call(
        $ul.childNodes,
        $li,
    );

    if ($pressedLiIndex >= this.$refs["list-field"].length - 1) {
        await addNewEntry();
    } else {
        // Focus the next input or textarea
        // We have to check for both, as inputs are used for
        // ingredients and textareas are used for instructions
        $ul.children[$pressedLiIndex + 1]
            .querySelector("input, textarea")
            .focus();
    }
};

/**
 * Shows the recipe linking popup when # is pressed
 */
const keyUp = (e) => {
    // If, e.g., enter has been pressed in the multiselect popup to select an option,
    // ignore the following keyup event
    if (ignoreNextKeyUp.value) {
        ignoreNextKeyUp.value = false;
        return;
    }

    const $li = e.currentTarget.closest("li");
    const $ul = $li.closest("ul");
    // noinspection UnnecessaryLocalVariableJS
    const $pressedLiIndex = Array.prototype.indexOf.call(
        $ul.childNodes,
        $li,
    );
    lastFocusedFieldIndex.value = $pressedLiIndex
    handleSuggestionsPopupKeyUp(e)
};

const moveEntryDown = (index) => {
    if (index >= buffer.value.length - 1) {
        // Already at the end of array
        return;
    }
    const entry = buffer.value.splice(index, 1)[0];
    if (index + 1 < buffer.value.length) {
        buffer.value.splice(index + 1, 0, entry);
    } else {
        buffer.value.push(entry);
    }
    emit("input", buffer.value);
};

const moveEntryUp = (index) => {
    if (index < 1) {
        // Already at the start of array
        return;
    }
    const entry = buffer.value.splice(index, 1)[0];
    buffer.value.splice(index - 1, 0, entry);
    emit("input", buffer.value);
};

const pasteCanceled = async () => {
    const field = listField[this.lastFocusedFieldIndex];
    // set cursor back to previous position
    await nextTick()
    field.focus();
    field.setSelectionRange(
        lastCursorPosition.value,
        lastCursorPosition.value,
    );
};

/**
 * Paste string at the last saved cursor position
 */
const pasteString = async (str, ignoreKeyup = true) => {
    const field = listField[lastFocusedFieldIndex.value];

    // insert str
    const content = field.value;
    // noinspection UnnecessaryLocalVariableJS
    const updatedContent =
        content.slice(0, lastCursorPosition.value) +
        str +
        content.slice(lastCursorPosition.value);
    buffer.value[lastFocusedFieldIndex.value] = updatedContent;
    emit("input", buffer.value);

    // set cursor to position after pasted string. Waiting two ticks is necessary for
    // the data to be updated in the field
    await nextTick();
    await nextTick();
    ignoreNextKeyUp.value = ignoreKeyup;
    field.focus();
    const newCursorPos = lastCursorPosition.value + str.length;
    field.setSelectionRange(newCursorPos, newCursorPos);
};
</script>

<script>
export default {
    name: 'EditInputGroup'
};
</script>

<style scoped>
.pad-icon {
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
    border-radius: 0;
    border-right-color: transparent;
    border-left-color: transparent;
    margin: 0;
}

li .controls > button:last-child {
    border-right-width: 1px;
    border-bottom-right-radius: var(--border-radius);
    border-top-right-radius: var(--border-radius);
}

li .controls > button:last-child:not(:hover):not(:focus) {
    border-right-color: var(--color-border-dark);
}

/*noinspection CssUnusedSymbol*/
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

/*noinspection CssUnusedSymbol*/
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
    border-radius: 50%;
    background-color: var(--color-background-dark);
    background-position: center;
    background-repeat: no-repeat;
    line-height: 36px;
    outline: none;
    text-align: center;
}
</style>
