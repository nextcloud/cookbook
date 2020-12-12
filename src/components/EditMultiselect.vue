<template>
    <fieldset>
        <label>{{ fieldLabel }}</label>
        <Multiselect
            class="edit_ms"
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
        fieldLabel: String,
    },
    data () {
        return {
        }
    }
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
