<template>
    <fieldset>
        <label>{{ fieldLabel }}</label>
        <Multiselect
            class="edit-multiselect"
            v-bind="$attrs"
            v-on="$listeners"
        />
    </fieldset>
</template>

<script>
import Multiselect from "@nextcloud/vue/dist/Components/Multiselect"
export default {
    name: "EditMultiselect",
    components: {
        Multiselect,
    },
    props: {
        fieldLabel: String,
    },
    data() {
        return {}
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
@media (max-width: 1199px) {
    fieldset > label {
        display: block;
        float: none;
    }
}
fieldset > label {
    display: inline-block;
    width: 10em;
    line-height: 18px;
    font-weight: bold;
    word-spacing: initial;
    vertical-align: top;
}

.edit-multiselect {
    width: calc(100% - 11em + 10px);
}

@media (max-width: 1199px) {
    .edit-multiselect {
        width: 100%;
    }
}
</style>

<style>
#app .edit-multiselect .multiselect__tags {
    height: auto;
    min-height: 34px;
}

#app .edit-multiselect .multiselect__tags .multiselect__tags-wrap {
    display: flex;
    flex-wrap: wrap;
    padding-bottom: 0px;
}

#app
    .edit-multiselect
    .multiselect__tags
    .multiselect__tags-wrap
    .multiselect__tag {
    flex-basis: 50px;
    margin-bottom: 3px;
}
</style>
