<template>
    <fieldset>
        <label>{{ fieldLabel }}</label>
        <transition-group name="list" tag="ul">
            <li v-for="(row, index) in rowsFromValue" :key="String(rowKeys[row.selectedOption.key])">
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
                <NcButton
                    class="ml-2"
                    :aria-label="t('cookbook', 'Delete nutrition item')"
                    @click="deleteEntry(index)"
                >
                    <template #icon>
                        <DeleteIcon :size="20" />
                    </template>
                </NcButton>
            </li>
            <li v-if="showAdditionalRow" key="new">
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
        </transition-group>
    </fieldset>
</template>

<script setup>
import { ref, defineProps, defineEmits, computed, onBeforeMount, set, del } from 'vue';

import NcButton from '@nextcloud/vue/dist/Components/NcButton.js';
import NcSelect from '@nextcloud/vue/dist/Components/NcSelect.js';
import DeleteIcon from 'vue-material-design-icons/Delete.vue';

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

// A fixed index for a row to identify it in the process of changes
// This is a map from the option key to a unique index.
const rowKeys = ref({});
// The next index to provide
const nextKey = ref(0);

// All registered keys in the options set in props
const optionKeys = computed(() => props.options.map((x) => x.key));

// The currently available options
const currentKeys = computed(() => Object.keys(props.value));

// All possible keys that are provided in the prop value
const valueFilteredKeys = computed(() =>
    optionKeys.value.filter(x => currentKeys.value.includes(x))
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
const availableOptionKeys = computed(() =>
    valueFilteredKeys.value.map((x) => [...unusedOptionKeys.value, x]),
);

// This is mainly the same as the availableOptionKeys but uses full objects and sorts these according to the input property
const availableOptions = computed(() =>
    availableOptionKeys.value.map((x) =>
        props.options.filter((y) => x.includes(y.key)),
    ),
);

/**
 * Delete a nutrition item.
 * @param index The index of the item to delete in the `value` property.
 */
function deleteEntry(index) {
    const data = { ...props.value };
    const { key } = rowsFromValue.value[index].selectedOption;
    delete data[key];
    del(rowKeys.value, key);
    emits('input', data);
}

// Add a new row to the model
function newRowByOption(ev) {
    const data = { ...props.value };
    data[ev.key] = '';
    set(rowKeys.value, ev.key, nextKey.value);
    nextKey.value += 1;
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
    data[ev.key] = data[key];
    delete data[key];
    rowKeys.value[ev.key] = rowKeys.value[key];
    delete rowKeys.value[key];
    emits('input', data);
}

onBeforeMount(() => {
    valueFilteredKeys.value.forEach((x, idx) => {
        set(rowKeys.value, x, idx);
    });
    Object.keys(rowKeys.value).forEach(rkKey => {
        const rk = rowKeys.value[rkKey];
        if(rk >= nextKey.value) {
            nextKey.value = rk + 1;
        }
    });
});
</script>

<script>
export default {
    name: 'EditMultiselectInputGroup',
};
</script>

<style scoped>
.ml-2 {
    margin-left: 0.5rem;
}

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

.list-move {
    transition: transform 1s;
}
</style>
