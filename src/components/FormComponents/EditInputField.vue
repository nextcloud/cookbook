<template>
    <fieldset>
        <label>
            {{ fieldLabel }}
        </label>
        <textarea
            v-if="props.fieldType === 'textarea' || props.fieldType === 'markdown'"
            ref="inputField"
            v-model="content"
            @input="handleInput"
            @keydown="keyDown"
            @keyup="handleSuggestionsPopupKeyUp"
            @focus="handleSuggestionsPopupFocus"
            @blur="handleSuggestionsPopupBlur"
            @mouseup="handleSuggestionsPopupMouseUp"
        />
        <div v-else>
            <slot />
            <input
                v-if="!hide"
                ref="inputField"
                v-model="content"
                :type="props.fieldType"
                @input="handleInput"
                @keydown="keyDown"
                @keyup="handleSuggestionsPopupKeyUp"
                @focus="handleSuggestionsPopupFocus"
                @blur="handleSuggestionsPopupBlur"
                @mouseup="handleSuggestionsPopupMouseUp"
            />
        </div>
        <SuggestionsPopup
            v-if="suggestionsPopupVisible"
            ref="suggestionsPopupElement"
            v-bind="suggestionsData"
            :options="filteredSuggestionOptions"
            v-on:suggestions-selected="handleSuggestionsPopupSelectedEvent"
        />
    </fieldset>
</template>

<script setup>
import { getCurrentInstance, nextTick, ref, watch } from "vue";
import SuggestionsPopup from '../Modals/SuggestionsPopup';
import useSuggestionPopup from '../Modals/suggestionsPopupComposable';
const log = getCurrentInstance().proxy.$log;

const emit = defineEmits(['input']);

// Template refs
const inputField = ref(null);


const suggestionsData = ref(null);

const props = defineProps({
    fieldLabel: {
        type: String,
        default: "",
    },
    fieldType: {
        type: String,
        default: "",
    },
    hide: {
        type: Boolean,
        default: false,
        required: false,
    },
    suggestionOptions: {
        type: Array
    },
    // Value (passed in v-model)
    // eslint-disable-next-line vue/require-prop-types
    value: {
        type: String,
        default: "",
        required: true,
    },
});

const content = ref(props.value);
const lastCursorPosition = ref(-1);

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
} = useSuggestionPopup(suggestionsPopupElement, lastCursorPosition, suggestionsData, null, emit, log, props);

watch(() => props.value, (newValue) => {
    content.value = newValue;
});

const handleInput = () => {
    emit("input", content.value);
};

const keyDown = (e) => {
    // Redirect to suggestions handler if in suggestion mode
    if (suggestionsPopupVisible) {
        handleSuggestionsPopupKeyDown(e);
    }
};

const pasteCanceled = async () => {
    // set cursor to position after pasted string
    await nextTick();
    const field = inputField;
    if (props.fieldType === "markdown") {
        field.editor.setCursor(lastCursorPosition.value);
        field.editor.focus();
    } else {
        field.focus();
        field.setSelectionRange(
            lastCursorPosition.value,
            lastCursorPosition.value,
        );
    }
};

/**
 * Paste string at the last saved cursor position
 */
const pasteString = async (str) => {
    const field = inputField;

    if (props.fieldType === "markdown") {
        // insert at last cursor position
        field.editor.replaceRange(str, {
            line: lastCursorPosition.value.line,
            ch: lastCursorPosition.value.ch,
        });
        emit("input", content.value);
        await nextTick();
        await nextTick();

        field.editor.focus();
        field.editor.setCursor({
            line: lastCursorPosition.value.line,
            ch: lastCursorPosition.value.ch + str.length,
        });
    } else {
        // insert str
        content.value =
            content.value.slice(0, lastCursorPosition.value) +
            str +
            content.value.slice(lastCursorPosition.value);
        emit("input", content.value);

        // set cursor to position after pasted string. Waiting two ticks is necessary for
        // the data to be updated in the field
        await nextTick();
        await nextTick();

        field.focus();
        const newCursorPos = lastCursorPosition.value + str.length;
        field.setSelectionRange(newCursorPos, newCursorPos);
    }
};


</script>

<script>
export default {
    name: 'EditInputField'
};
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
*/
.editor:deep(div[contenteditable="true"]) {
    width: revert;
    min-height: revert;
    padding: revert;
    border: revert;
    border-radius: revert;
    margin: revert;
    background-color: revert;
    color: revert;
    font-size: revert;
    outline: revert;
}
</style>
