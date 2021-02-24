<template>
    <fieldset>
        <label>{{ fieldLabel }}</label>
        <ul ref="list">
            <li v-for="(entry, idx) in selectedOpts" :key="fieldLabel + idx">
                <multiselect
                    class="key"
                    :options="selectableOptions(idx)"
                    track-by="key"
                    label="label"
                    :multiple="false"
                    :placeholder="labelSelectPlaceholder"
                    :value="selectedOptions[idx][0]"
                    @change="(e) => optionUpdated(idx, e)"
                />
                <input
                    type="text"
                    class="val"
                    :placeholder="placeholder(idx)"
                    :value="filledValues[idx]"
                    @input="(e) => fieldValueUpdated(idx, e)"
                />
            </li>
            <li
                v-if="selectedOptions.length < options.length"
                :key="fieldLabel + selectedOptions.length"
            >
                <multiselect
                    class="key"
                    :options="selectableOptions(selectedOptions.length)"
                    track-by="key"
                    label="label"
                    :placeholder="labelSelectPlaceholder"
                    :multiple="false"
                    @change="(e) => optionUpdated(selectedOptions.length, e)"
                />
                <input
                    type="text"
                    class="val"
                    :placeholder="placeholder(selectedOptions.length)"
                    @input="(e) => fieldValueUpdated(selectedOptions.length, e)"
                />
            </li>
        </ul>
    </fieldset>
</template>

<script>
import Multiselect from "@nextcloud/vue/dist/Components/Multiselect"

export default {
    name: "EditMultiselectInputGroup",
    components: {
        Multiselect,
    },
    // Define which prop and which event is used here, for binding to the
    // v-model used in the parent (the one using this component)
    model: {
        prop: "value",
        event: "change",
    },
    props: {
        fieldLabel: {
            type: String,
            default: "",
        },
        labelSelectPlaceholder: {
            type: String,
            default: "Select option",
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
            default: () => ({}),
            required: true,
        },
    },
    data() {
        return {
            selectedOptions: {
                type: Array,
                default: [],
            },
            filledValues: {
                type: Array,
                default: null,
            },
            localValue: {
                type: Object,
                default: JSON.parse(JSON.stringify(this.value)),
            },
        }
    },
    computed: {
        selectedOpts() {
            if (!(this.selectedOptions instanceof Array)) {
                return []
            }
            return this.selectedOptions
        },
    },
    watch: {
        // Update local value when value property is updated
        value(val) {
            this.localValue = JSON.parse(JSON.stringify(val))
            this.updateLocalValues()
        },
        options: {
            deep: true,
            handler(val, oldVal) {
                this.updateLocalValues()
            },
        },
    },
    methods: {
        /**
         * Emit locally updated value to parent component.
         */
        emitUpdate() {
            this.$emit("change", JSON.parse(JSON.stringify(this.localValue)))
        },
        /**
         * Called when input-fields content is changed.
         */
        fieldValueUpdated(idx, e) {
            this.filledValues[idx] = e.target.value
            this.localValue[this.selectedOptions[idx][0][0].key] =
                e.target.value
            this.emitUpdate()
        },
        /**
         * Called when a new option is chosen in one of the `Multiselect`s.
         */
        optionUpdated(idx, val) {
            if (
                idx == this.selectedOptions.length &&
                typeof this.filledValues[idx] === "undefined"
            ) {
                this.filledValues[idx] = ""
            }
            // Entry exists
            if (
                this.selectedOptions[idx] != null &&
                typeof this.selectedOptions[idx] !== "undefined"
            ) {
                delete this.localValue[this.selectedOptions[idx][0][0].key]
            }
            this.localValue[val.key] = this.filledValues[idx]
            this.$set(this.selectedOptions, idx, [val])
            this.emitUpdate()
        },
        /**
         * Get content of the descriptive placeholder for an input field.
         */
        placeholder(idx) {
            if (
                idx >= this.selectedOptions.length ||
                idx >= this.options.length
            ) {
                return ""
            }
            const optionIdx = this.options
                .map((o) => o.key)
                .indexOf(this.selectedOptions[idx][0][0].key)
            if (optionIdx > -1 && "placeholder" in this.options[optionIdx]) {
                return this.options[optionIdx].placeholder
            }
            return ""
        },
        /**
         * Get a list of not yet selected options.
         */
        selectableOptions(idx) {
            if (!(this.selectedOptions instanceof Array)) {
                return []
            }
            const selectableOpts = []
            const selectedKeys = this.selectedOptions.map((m) => m[0][0].key)
            for (let i = 0; i < this.options.length; i++) {
                const option = this.options[i]
                if (!("label" in option)) {
                    option.label = option.key
                }
                if (!selectedKeys.includes(option.key)) {
                    selectableOpts.push(option)
                }
            }
            return selectableOpts
        },
        /**
         * Update the helper fields with the localValue.
         */
        updateLocalValues() {
            // show only fields made available in passed `options`
            this.selectedOptions = []
            this.filledValues = []
            for (let key in this.localValue) {
                if (this.options.map((o) => o.key).includes(key)) {
                    const opt = this.options.filter((o) => o.key === key)
                    this.selectedOptions.push([opt])
                    this.filledValues.push(this.localValue[key])
                }
            }
        },
    },
}
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

fieldset > ul > li .key {
    margin-right: 1em;
}

fieldset > ul > li .val {
    width: 100%;
    margin: 0;
}

/* .ms {
    width: 20em;
}

@media (max-width: 1199px) {
    .ms {
        width: 100%;
    }
} */
</style>
