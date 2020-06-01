<template>
    <fieldset>
        <label>
            {{ fieldLabel }}
        </label>
        <input :type="fieldType" v-model="$parent.recipe[fieldName]" />
    </fieldset>
</template>

<script>
export default {
    name: "EditInputField",
    props: ['fieldType','fieldName','fieldLabel'],
    data () {
        return {

        }
    },
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

fieldset > input {
    width: calc(100% - 11em);
}
    @media(max-width:1199px) { fieldset > input {
        width: 100%;
    }}

</style>
