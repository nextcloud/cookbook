import {position as caretPosition} from "caret-pos";
import { computed, nextTick, ref } from "vue";

export default function useSuggestionsPopup(suggestionsPopupElementA, lastCursorPosition, suggestionsData, buffer, emit, log, props) {
    const clamp = (val, min, max) => Math.min(max, Math.max(min, val));

    /**
     * Reference to the SuggestionsPopup DOM element.
     * @type {Ref<HTMLElement | null>}
     */
    const suggestionsPopupElement = ref(null)

    /**
     * Cancel the suggestions popup by setting the data object to null
     */
    const handleSuggestionsPopupCancel = () => {
        suggestionsData.value = null;
    };

    const suggestionsPopupVisible = computed(() => {
        return (
            suggestionsData.value !== null && !suggestionsData.value.blurred
        );
    });

    const filteredSuggestionOptions = computed(() => {
        const { searchText } = suggestionsData.value;

        return props.suggestionOptions.filter(
            (option) =>
                searchText === "" ||
                option.title
                    .toLowerCase()
                    .includes(searchText.toLowerCase()),
        );
    });

    /**
     * Handles the 'suggestions-selected' event emitted by the
     * SuggestionPopup component.
     */
    const handleSuggestionsPopupSelectedEvent = (opt) => {
        handleSuggestionsPopupSelected(opt.recipe_id);
    }

    /**
     * Handle something selected by click or by `Enter`
     * Insert the reference with `pasteString`,
     * which should exist everywhere this mixin is used
     * (`EditInputGroup` and `EditInputField`) and close the popup
     */
    const handleSuggestionsPopupSelected = async (recipeId) => {
        const { field, hashPosition } = suggestionsData.value;
        const { value } = field;
        const before = value.slice(0, hashPosition);
        const after = value.slice(field.selectionStart);
        const replace = `r/${recipeId}`;
        const newValue = `${before}${replace}${after}`;

        if (buffer?.value) {
            buffer.value[suggestionsData.value.fieldIndex] = newValue;
            emit("input", buffer.value);
        } else {
            emit("input", newValue);
        }
        handleSuggestionsPopupCancel();
        // set cursor to position after pasted string. Waiting two ticks is necessary for
        // the data to be updated in the field
        await nextTick();
        await nextTick();

        field.focus();
        const newCursorPos = hashPosition + replace.length;
        field.setSelectionRange(newCursorPos, newCursorPos);
    };

    /**
     * Handle keyup events when the suggestions popup is open
     * The event will be sent here from the normal keydown handler
     * if suggestionsPopupVisible
     */
    const handleSuggestionsPopupOpenKeyUp = (e, cursorPos) => {
        const caretPos = caretPosition(e.target, {
            customPos: suggestionsData.value.hashPosition - 1,
        })

        // Only update the popover position if the line changes (caret pos y changes)
        if (caretPos.top !== suggestionsData.value.caretPos.top) {
            suggestionsData.value.caretPos = caretPos
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
            handleSuggestionsPopupCancel();
            return;
        }

        // Cancel suggestions popup if hash deleted
        if (cursorPos < suggestionsData.value.hashPosition) {
            handleSuggestionsPopupCancel();
            return;
        }

        // Update the search text
        // Slice the input from the position of the "#" to the caret position
        const { hashPosition, field } = suggestionsData.value;
        const searchText = field.value.slice(hashPosition, cursorPos);
        suggestionsData.value = {
            ...suggestionsData.value,
            searchText,
            caretIndex: field.selectionStart,
        };
    };

    const getClosestListItemIndex = (field) => {
        const $li = field.closest("li");
        const $ul = $li?.closest("ul");
        if (!$ul) return null;

        return Array.prototype.indexOf.call($ul.childNodes, $li);
    };

    /**
     * Shows the recipe linking popup when # is pressed
     */
    const handleSuggestionsPopupKeyUp = (e) => {
        const field = e.currentTarget;
        const cursorPos = field.selectionStart;

        // No suggestion options means suggestions are disabled for this input
        if (!props.suggestionOptions) return;

        if (suggestionsPopupVisible.value) {
            handleSuggestionsPopupOpenKeyUp(e, cursorPos);
            return;
        }

        // Only do anything for # key
        if (e.key !== "#") return;

        // Show the popup only if the # was inserted at the very
        // beginning of the input or after any whitespace character
        if (
            !(
                cursorPos === 1 ||
                /\s/.test(field.value.charAt(cursorPos - 2))
            )
        ) {
            return;
        }

        // Show dialog to select recipe
        const caretPos = caretPosition(field, { customPos: cursorPos - 1 })
        suggestionsData.value = {
            field,
            searchText: "",
            caretPos,
            focusIndex: 0,
            hashPosition: cursorPos,
            blurred: false,
            caretIndex: field.selectionStart,
            fieldIndex: getClosestListItemIndex(field),
        };
        lastCursorPosition = cursorPos;
    };

    const handleSuggestionsPopupKeyDown = (e) => {
        if (!suggestionsPopupVisible.value) return;

        handleSuggestionsPopupOpenKeyDown(e);
    };

    /**
     * Handle keydown events for suggestions popup
     * The event will be sent here from the normal keydown handler
     * if suggestionsPopupVisible
     */
    const handleSuggestionsPopupOpenKeyDown = (e) => {
        // Handle switching the focused option with up/down keys
        if (["ArrowUp", "ArrowDown"].includes(e.key)) {
            e.preventDefault();

            // Increment/decrement focuse index based on which key was pressed
            // and constrain between 0 and length - 1
            const focusIndex = clamp(
                suggestionsData.value.focusIndex +
                {
                    ArrowUp: -1,
                    ArrowDown: +1,
                }[e.key],
                0,
                props.suggestionOptions.length - 1,
            );
            suggestionsData.value = {
                ...suggestionsData.value,
                focusIndex,
            };
            return;
        }

        // Handle selecting the current option when enter is pressed
        if (e.key === "Enter") {
            e.preventDefault();
            const { focusIndex } = suggestionsData.value;
            const selection = filteredSuggestionOptions.value[focusIndex];
            handleSuggestionsPopupSelected(selection.recipe_id);
        }
    };

    /**
     * Recover suggestions popup on focus
     */
    const handleSuggestionsPopupFocus = (e) => {
        log.debug("focus", e, JSON.stringify(suggestionsData.value))
        if (suggestionsData.value?.blurred) {
            suggestionsData.value.blurred = false
        }
    };

    /**
     * Cancel selection if input gets blurred
     */
    const handleSuggestionsPopupBlur = (e) => {
        log.debug("blur", e, JSON.stringify(suggestionsData.value))
        if (!suggestionsPopupVisible.value || !suggestionsPopupElement.value) {
            return;
        }
        // Get reference to the popup
        // There is only ever one at a time, but if it's defined in a v-for
        // the reference will be an array of one
        const $suggestionsPopup = Array.isArray(suggestionsPopupElement.value)
            ? suggestionsPopupElement.value[0].$el
            : suggestionsPopupElement.value.$el;

        // Do not cancel suggestions if the new focused element (e.relatedTarget)
        // is a child of the suggestions popup
        // That is the case when clicking an option in the suggestions popup,
        // and cancelling too early prevents the option from being properly selected
        if ($suggestionsPopup.contains(e.relatedTarget)) {
            return;
        }
        suggestionsData.value.blurred = true;
    };

    /**
     * Cancel the popup if the mouse is used to change the caret position
     */
    const handleSuggestionsPopupMouseUp = () => {
        if (!suggestionsPopupVisible.value) return;

        const { caretIndex, field } = suggestionsData.value;
        if (caretIndex !== field.selectionStart) {
            handleSuggestionsPopupCancel();
        }
    };

    return {
        handleSuggestionsPopupCancel,
        suggestionsPopupVisible,
        filteredSuggestionOptions,
        suggestionsPopupElement,
        handleSuggestionsPopupSelected,
        handleSuggestionsPopupOpenKeyUp,
        getClosestListItemIndex,
        handleSuggestionsPopupKeyUp,
        handleSuggestionsPopupKeyDown,
        handleSuggestionsPopupOpenKeyDown,
        handleSuggestionsPopupFocus,
        handleSuggestionsPopupBlur,
        handleSuggestionsPopupMouseUp,
        handleSuggestionsPopupSelectedEvent
    };
}
