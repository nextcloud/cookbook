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

<style>

</style>
