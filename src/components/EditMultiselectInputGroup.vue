<template>
    <fieldset>
        <label>{{ fieldLabel }}</label>
        <ul ref="list">
            <li v-for="(entry,idx) in selectedOpts" :key="fieldLabel+idx">
                <multiselect
                    class="key"
                    :options="selectableOptions(idx)"
                    :multiple="false"
                    @change="e => optionUpdated(idx, e)"
                    :value="selectedOptions[idx][0]"
                    />
                <input 
                    type="text"
                    class="val"
                    :placeholder="placeholder(idx)"
                    @input="e => fieldValueUpdated(idx, e)"
                    :value="filledValues[idx]"
                    />
            </li>
            
            <li v-if="selectedOptions.length < options.length" :key="fieldLabel+selectedOptions.length">
                <multiselect
                    class="key"
                    :options="selectableOptions(selectedOptions.length)"
                    :multiple="false"
                    @change="e => optionUpdated(selectedOptions.length, e)"
                    />
                <input 
                    type="text"
                    class="val"
                    :placeholder="placeholder(selectedOptions.length)"
                    @input="e => fieldValueUpdated(selectedOptions.length, e)"
                    />
            </li>
        </ul>
    </fieldset>
</template>

<script>
import Multiselect from '@nextcloud/vue/dist/Components/Multiselect'

export default {
    name: "EditMultiselectInputGroup",
    components: {
        Multiselect
    },
    model: {
        prop: 'value',
        event: 'change'
    },
    props: {
        fieldLabel: {
            type: String,
            default: ""
        },
        // Selectable options
        options: {
            type: Array,
            default: [],
            required: true
        },
        // Placeholder content of the input fields
        placeholders: {
            type: Array,
            default: []
        },
        // Value (passed in v-model)
        value: {
            type: Object,
            default: () => ({}),
            required: true
        },
    },
    data () {
        return {
            selectedOptions: {
                type: Array,
                default: []
            },
            filledValues: {
                type: Array,
                default: null
            },
            localValue: {
                type: Object,
                default: JSON.parse(JSON.stringify(this.value))
            },
        }
    },
    computed: {
        selectedOpts() {
            if(!(this.selectedOptions instanceof Array))
            {
                return []
            }
            return this.selectedOptions
        }
    },
    watch: {
        // Update local value when value property is updated
        value (val) {
            this.localValue = JSON.parse(JSON.stringify(val))
            this.updateLocalValues()
        },
        options: {
            deep: true,
            handler (val, oldVal) {
                this.updateLocalValues()
            }
        }
    },
    methods: {
        /**
         * Called when a new option is chosen in one of the `Multiselect`s.
         */
        optionUpdated: function (idx, val) {
            delete this.localValue[this.selectedOptions[idx]]
            this.localValue[val] = this.filledValues[idx]
            this.$set(this.selectedOptions,idx, [val]);
            this.emitUpdate()
        },
        /**
         * Called when input-fields content is changed.
         */
        fieldValueUpdated: function (idx, e) {
            this.filledValues[idx] = e.target.value
            this.localValue[this.selectedOptions[idx]] = e.target.value
            this.emitUpdate()
        },
        /**
         * Emit locally updated value to parent component.
         */
        emitUpdate: function() {
            console.log("Emitting new value from MultiselectInputGroup: ")
            console.log(this.localValue)
            this.$emit('change', JSON.parse(JSON.stringify(this.localValue)))
        },
        /**
         * Get a list of not yet selected options.
         */
        selectableOptions: function (idx) {
            if (!(this.selectedOptions instanceof Array)) {
                return []
            }
            return this.options.filter(
                    x => !(this.selectedOptions.map(m => m[0]).includes(x))
                )
        },
        /**
         * Get content of the describptive placeholder for input field.
         */
        placeholder: function (idx) {
            let optionIdx = this.options.indexOf(this.selectedOptions[idx])
            if (optionIdx > -1 && this.placeholders.length >= optionIdx-1) {
                return this.placeholders[optionIdx]
            }
            return ''
        },
        /**
         * Update the helper fields with the localValue.
         */
        updateLocalValues: function() {
            // show only fields made available in passed options
            this.selectedOptions = []
            this.filledValues = []
            for (let key in this.localValue) {
                if (this.options.includes(key)) {
                    this.selectedOptions.push([key])
                    this.filledValues.push(this.localValue[key])
                }                    
            }
        },
    },
}
</script>

<style scoped>

fieldset {
    margin-bottom: 1em;
    width: 100%;
}

fieldset > * {
    margin: 0;
    float: left;
}
    @media(max-width:1199px) { fieldset > label {
        display: block;
        float: none;
    }}
fieldset > label {
    display: inline-block;
    width: 10em;
    line-height: 18px;
    font-weight: bold;
    word-spacing: initial;
    vertical-align: top;
    margin-bottom: 1rem;
}

fieldset > ul {
    width: 100%;
}

fieldset > ul > li {
    display: flex;
    margin-bottom: .5em;
}

fieldset > ul > li .key {
    margin-right: 1em;
}

fieldset > ul > li .val {
    margin: 0px;
    width: 100%;
}

.ms {
    width: 20em;
}

    @media(max-width:1199px) { .ms {
        width: 100%;
    }}

</style>
