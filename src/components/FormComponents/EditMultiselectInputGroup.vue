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
                    :placeholder="t('cookbook', 'Please select nutrition category first.')"
                    :disabled="true"
                    class="val"
                />
            </li>
        </ul>
    </fieldset>
</template>

<script setup>
import NcSelect from '@nextcloud/vue/dist/Components/NcSelect.js';
import { ref, defineProps, defineEmits, nextTick, watch, computed } from 'vue';
import { prop } from 'lodash/fp';

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

/**
 * @type {import('vue').Ref<Array>}
 */
const rows = ref([]);

const additionalRow = ref({
    selectedOption: null,
    customText: '',
});

// Methods
const getAvailableOptions = async () => {
    // Calculate available options by excluding those already selected
    const selectedOptions = rows.value.map((row) => row.selectedOption);
    return props.options.filter((option) => !selectedOptions.includes(option));
};

const availableOptionsOld = computed(() => {
    // Calculate available options by excluding those already selected
    const selectedOptions = rows.value.map((row) => row.selectedOption);
    return props.options.filter((option) => !selectedOptions.includes(option));
});

// All resistered keys in the options set in props
const optionKeys = computed(() => props.options.map((x) => x.key));

// All possible keys that are provided in the prop value
const valueFilteredKeys = computed(() => Object.keys(props.value).filter((x) => optionKeys.value.includes(x)));

const rowsFromValue = computed(() => valueFilteredKeys.value.map((x) => ({
    options: [],
    selectedOption: props.options.find((y) => y.key === x),
    customText: props.value[x],
})));

const showAdditionalRow = computed(() => (valueFilteredKeys.value.length < props.options.length));

const unusedOptionKeys = computed(() => optionKeys.value.filter(x => ! valueFilteredKeys.value.includes(x)));

const unusedOptions = computed(() => props.options.filter(x => unusedOptionKeys.value.includes(x.key)));

const availabeOptionKeys = computed(() => 
    valueFilteredKeys.value.map((x) => [...unusedOptionKeys.value, x])
);

const availableOptionsPure = computed(() => 
    availabeOptionKeys.value.map(x => props.options.filter(y => x.includes(y.key)))
);

const availableOptions = computed(() => 
    availableOptionsPure.value.map(x => [{
        key: null,
        label: '---',
    }, ...x])
);

// const clearable = computed(() => valueFilteredKeys.value.map(x => props.value[x].length === 0));

function newRowByOption(ev) {
    const data = {... props.value};
    data[ev.key] = '';
    emits('input', data);
}

function updateByText(ev, idx) {
    const data = { ... props.value };
    data[rowsFromValue.value[idx].selectedOption.key] = ev.target.value;
    emits('input', data);
}

function updateByOption(ev, index) {
    const data = { ... props.value };
    const { key } = rowsFromValue.value[index].selectedOption;
    if(ev.key === null) {
        delete data[key];
        emits('input', data);
    } else {
        data[ev.key] = data[key];
        delete data[key];
        emits('input', data)
    }
    //
}

const getSelectedValues = () => {
    const selectedValues = {};
    rows.value.forEach((row) => {
        // Only include rows with selected values
        if (row.selectedOption) {
            selectedValues[row.selectedOption.key] = row.customText;
        }
    });
    return selectedValues;
};

const selectedValues = computed(() => {
    const ret = {};
    rows.value.forEach((row) => {
        // Only include rows with selected values
        if (row.selectedOption) {
            ret[row.selectedOption.key] = row.customText;
        }
    });
    return ret;
});

const createRow = async () => {
    // Remove empty rows at the end before creating a new one
    for (let i = rows.value.length - 1; i >= 0; i--) {
        if (!rows.value[i].selectedOption) {
            rows.value.pop();
        }
    }

    // Create row only if there are still options to select from
    if (rows.value.length < props.options.length) {
        const availableOptions = await getAvailableOptions();
        rows.value.push({
            options: availableOptions,
            selectedOption: null,
            customText: '',
        });
    }
};

const recalculateAvailableOptions = async () => {
    // Update options in all rows based on the current selections
    const availableOptions = await getAvailableOptions();
    for (let i = 0; i < rows.value.length; i++) {
        rows.value[i].options = availableOptions;
    }
};



// const presentedRows = computed(() => {
//     const tmp = rowsFromValue.value;
//     if (rows.value.length < props.options.length){
//         tmp.push({
//             options: availableOptions.value,
//             selectedOption: null,
//             customText: '',
//         });
//     }
//     return tmp;
// });

const createRowsBasedOnModelValue = async () => {
    const initialModelValue = props.value || {};
    const keys = Object.keys(initialModelValue);

    for (const key of keys) {
        const option = props.options.find((opt) => opt.key === key);
        const row = rows.value.find(
            (myRow) => myRow.selectedOption.key === key,
        );
        // Update row with key if it already exists
        if (row) {
            row.customText = initialModelValue[key] || '';
        }
        // otherwise create new row
        else if (option) {
            rows.value.push({
                options: availableOptions,
                selectedOption: option,
                customText: initialModelValue[key] || '',
            });
        }
    }

    await recalculateAvailableOptions();
    await createRow(); // Create an additional row for future selections
};

const handleMultiselectChange = async (changedIndex) => {
    // Wait for the DOM to update after the multiselect change
    await nextTick();

    // Update options in all other rows based on the changed selection
    const availableOptions = await getAvailableOptions();
    for (let i = 0; i < rows.value.length; i++) {
        if (i !== changedIndex) {
            rows.value[i].options = availableOptions;
        }
    }
    // Emit the updated modelValue
    emits('input', getSelectedValues());

    await createRow();
};

// Watchers

// watch(
//     () => props.value,
//     async (newModelValue) => {
//         // React to external changes in modelValue
//         await createRowsBasedOnModelValue(newModelValue);
//     },
// );
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
