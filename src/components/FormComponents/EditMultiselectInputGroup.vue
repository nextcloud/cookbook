<template>
    <fieldset>
        <label>{{ fieldLabel }}</label>
        <ul ref="list">
            <li v-for="(row, index) in rows" :key="index">
                <NcMultiselect
                    v-model="row.selectedOption"
                    :options="row.options"
                    track-by="key"
                    label="label"
                    :placeholder="labelSelectPlaceholder"
                    class="key"
                    @change="handleMultiselectChange(index)"
                />
                <input
                    v-model="row.customText"
                    :placeholder="row.options.placeholder"
                    class="val"
                />
            </li>
        </ul>
    </fieldset>
</template>

<script setup>
import NcMultiselect from '@nextcloud/vue/dist/Components/NcMultiselect';
import { ref, defineProps, defineEmits, nextTick, watch } from 'vue';

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

// Methods
const getAvailableOptions = async () => {
    // Calculate available options by excluding those already selected
    const selectedOptions = rows.value.map((row) => row.selectedOption);
    return props.options.filter((option) => !selectedOptions.includes(option));
};

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

const createRowsBasedOnModelValue = async () => {
    const initialModelValue = props.value || {};
    const keys = Object.keys(initialModelValue);

    const availableOptions = await getAvailableOptions();
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

watch(
    () => props.value,
    async (newModelValue) => {
        // React to external changes in modelValue
        await createRowsBasedOnModelValue(newModelValue);
    },
);
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
