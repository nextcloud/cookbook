<template>
    <fieldset>
        <label>{{ fieldLabel }}</label>
        <Multiselect
            class="edit_ms"
		    v-model="localValue"
            v-bind="$attrs"
            v-on="$listeners"
            />
    </fieldset>
</template>

<script>
import Multiselect from '@nextcloud/vue/dist/Components/Multiselect'
export default {
    name: "EditMultiselect",
    components: {
        Multiselect
    },
    props: {
		value: {
			default() {
				return []
			},
		},
        fieldLabel: String,
    },
    data () {
        return {
        }
    },
    computed: {
        localValue: {
			get() {
				if (this.trackBy && this.options
					&& typeof this.value !== 'object'
					&& this.options[this.value]) {
					return this.options[this.value]
				}
				return this.value
			},
			set(value) {
				this.$emit('update:value', value)
				this.$emit('change', value)
			},
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
}

.edit_ms {
    width: calc(100% - 11em + 10px);
}

    @media(max-width:1199px) { .edit_ms {
        width: 100%;
    }}

</style>
