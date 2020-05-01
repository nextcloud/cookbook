<template>
    <fieldset>
        <label>
            {{ fieldLabel }}
        </label>
        <input type="text" v-model="$parent.recipe[fieldName]" />
        <button type="button" :title="$t('Pick a local image')" @click="pickImage()"><span class="icon-category-multimedia"></span></button>
    </fieldset>
</template>

<script>
export default {
    name: "EditImageField",
    props: ['fieldName','fieldLabel'],
    data () {
        return {

        }
    },
}
</script>

<style>

</style>
