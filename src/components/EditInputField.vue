<template>
    <fieldset>
        <label>
            {{ fieldLabel }}
        </label>
        <input v-if="fieldType!=='textarea'" :type="fieldType" v-model="content" @input="handleInput" />
        <textarea v-if="fieldType==='textarea'" v-model="content" @input="handleInput" />
    </fieldset>
</template>

<script>
export default {
    name: "EditInputField",
    props: ['value','fieldType','fieldLabel'],
    data () {
        return {
            content: ''
        }
    },
    watch: {
        value: function() {
            this.content = this.value
        }
    },
    methods: {
        handleInput (e) {
            this.$emit('input', this.content)
        },
    }
}
</script>

<style scoped>

fieldset {
    margin-bottom: 1em;
}

fieldset > * {
    margin: 0;
    float: left;
}

fieldset > label {
    vertical-align: top;
    display: inline-block;
    width: 10em;
    height: 34px;
    line-height: 17px;
    font-weight: bold;
}
    @media(max-width:1199px) { fieldset > label {
        display: block;
        float: none;
    }}

fieldset > input,
fieldset > textarea {
    width: calc(100% - 11em);
}

fieldset > textarea {
    min-height: 10em;
    resize: vertical;
}

    @media(max-width:1199px) { fieldset > input, fieldset > textarea {
        width: 100%;
    }}

</style>
