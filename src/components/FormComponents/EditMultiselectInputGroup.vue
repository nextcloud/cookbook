<template>
    <fieldset>
        <label>{{ fieldLabel }}</label>
        <ul ref="list">
            <li v-for="(row, index) in rowsFromValue" :key="index">
                <NcSelect
                    :value="row.selectedOption"
                    :options="availableOptions[index]"
                    track-by="key"
                    label="label"
                    :multiple="false"
                    :clearable="false"
                    :searchable="false"
                    class="key"
                    @input="updateByOption($event, index)"
                />
                <input
                    :value="row.customText"
                    :placeholder="row.selectedOption.placeholder"
                    class="val"
                    @change="updateByText($event, index)"
                />
            </li>
            <li v-if="showAdditionalRow">
                <NcSelect
                    :value="additionalRow.selectedOption"
                    :options="unusedOptions"
                    label="label"
                    :placeholder="labelSelectPlaceholder"
                    :multiple="false"
                    class="key"
                    @input="newRowByOption"
                />
                <input
                    :value="additionalRow.customText"
                    :placeholder="t('cookbook', 'Please select option first.')"
                    :disabled="true"
                    class="val"
                />
            </li>
        </ul>
    </fieldset>
</template>

<script setup>
import NcSelect from '@nextcloud/vue/dist/Components/NcSelect.js';
import { ref, defineProps, defineEmits, computed } from 'vue';

const emits = defineEmits(['input']);

const props = defineProps({
    fieldLabel: {
        type: String,
        default: '',
    },
    labelSelectPlaceholder: {
        type: String,
        default: t('cookbook', 'Select option'),
    },
    /** Selectable options.
     * Array of option objects with keys: key, label, and placeholder
     * key: Key of the v-model object
     * label: label to display for the key in the Multiselect
     * placeholder: Placeholder shown for the key in the empty input field
     */
    options: {
        type: Array,
        default: () => [],
        required: true,
    },
    // Value (passed in v-model)
    value: {
        type: Object,
        default: () => {},
        required: true,
    },
});

const additionalRow = ref({
    selectedOption: null,
    customText: '',
});

// All resistered keys in the options set in props
const optionKeys = computed(() => props.options.map((x) => x.key));

// All possible keys that are provided in the prop value
const valueFilteredKeys = computed(() =>
    Object.keys(props.value).filter((x) => optionKeys.value.includes(x)),
);

const rowsFromValue = computed(() =>
    valueFilteredKeys.value.map((x) => ({
        options: [],
        selectedOption: props.options.find((y) => y.key === x),
        customText: props.value[x],
    })),
);

// Should the additional row to add new nutrition entries be shown (or hidden)
const showAdditionalRow = computed(
    () => valueFilteredKeys.value.length < props.options.length,
);

// A list of all nutrition options that are not yet used (just the internal keys)
const unusedOptionKeys = computed(() =>
    optionKeys.value.filter((x) => !valueFilteredKeys.value.includes(x)),
);

// A list of all nutrition options that are not yet used (complete objects, sorted according to prop)
const unusedOptions = computed(() =>
    props.options.filter((x) => unusedOptionKeys.value.includes(x.key)),
);

// Create a list of lists of available options for the individual rows (just the internal keys)
// This will be a list. Each (top) list entry represents the internal keys of the available options for the corresponding row.
// Note that each row can set the option to any yet unused option or the currently selected one.
const availabeOptionKeys = computed(() =>
    valueFilteredKeys.value.map((x) => [...unusedOptionKeys.value, x]),
);

// This is mainly the same as the availabelOptionKeys but uses full objects and sorts these according to the input property
const availableOptionsPure = computed(() =>
    availabeOptionKeys.value.map((x) =>
        props.options.filter((y) => x.includes(y.key)),
    ),
);

// The actual available options per row are augmented by an option to remove a row.
const availableOptions = computed(() =>
    availableOptionsPure.value.map((x) => [
        {
            key: null,
            label: '---',
        },
        ...x,
    ]),
);

// Add a new row to the model
function newRowByOption(ev) {
    const data = { ...props.value };
    data[ev.key] = '';
    emits('input', data);
}

// Update the text of an existing row
function updateByText(ev, idx) {
    const data = { ...props.value };
    data[rowsFromValue.value[idx].selectedOption.key] = ev.target.value;
    emits('input', data);
}

// Change the actual option. This might change the option or plainly delete it.
function updateByOption(ev, index) {
    const data = { ...props.value };
    const { key } = rowsFromValue.value[index].selectedOption;
    if (ev.key === null) {
        delete data[key];
        emits('input', data);
    } else {
        data[ev.key] = data[key];
        delete data[key];
        emits('input', data);
    }
}
</script>

<script>
export default {
    name: 'EditMultiselectInputGroup',
};
</script>

<style scoped>
fieldset {
    width: 100%;
    margin-bottom: 1em;
}

fieldset > * {
    margin: 0;
    float: left;
}
@media (max-width: 1199px) {
    fieldset > label {
        display: block;
        float: none;
    }
}

fieldset > label {
    display: inline-block;
    width: 16em;
    margin-bottom: 1rem;
    font-weight: bold;
    line-height: 18px;
    vertical-align: top;
    word-spacing: initial;
}

fieldset > ul {
    width: 100%;
}

fieldset > ul > li {
    display: flex;
    margin-bottom: 0.5em;
}

fieldset > ul > li > .key {
    margin-right: 1em;
}

fieldset > ul > li > input.val {
    width: 100%;
    height: 44px !important;
    margin: 0;
}
</style>
