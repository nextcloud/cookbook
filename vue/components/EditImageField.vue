<template>
    <fieldset>
        <label>
            {{ fieldLabel }}
        </label>
        <input type="text" v-model="$parent.recipe[fieldName]" />
        <button type="button" :title="$t('Pick a local image')" @click="pickImage"><span class="icon-category-multimedia"></span></button>
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
    methods: {
        pickImage: function(e) {
            e.preventDefault()
            let $this = this
            OC.dialogs.filepicker(
                this.$t('Path to your Recipe Image'),
                function (path) {
                    $this.$parent.recipe.image = path
                },
                false,
                ['image/jpeg', 'image/png'],
                true,
                OC.dialogs.FILEPICKER_TYPE_CHOOSE
            )
        }
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

fieldset > input {
    width: calc(100% - 14em);
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    border-right: 0;
}
    @media(max-width:1199px) { fieldset > input {
        width: calc(100% - 3em);
    }}

    fieldset > input + button {
        border-top-right-radius: var(--border-radius);
        border-bottom-right-radius: var(--border-radius);
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        width: 3em;
    }

        fieldset > input + button > * {
            pointer-events: none;
        }

</style>
