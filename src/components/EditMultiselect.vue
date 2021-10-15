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
        fieldLabel: {
            type: String,
            default: "",
        },
    },
    data() {
        return {}
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
    width: 10em;
    font-weight: bold;
    line-height: 18px;
    vertical-align: top;
    word-spacing: initial;
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
/* stylelint-disable selector-class-pattern */
#app .edit-multiselect .multiselect__tags {
    height: auto;
    min-height: 34px;
}
/* stylelint-enable selector-class-pattern */

/* stylelint-disable selector-class-pattern */
#app .edit-multiselect .multiselect__tags .multiselect__tags-wrap {
    display: flex;
    flex-wrap: wrap;
    padding-bottom: 0;
}
/* stylelint-enable selector-class-pattern */

/* stylelint-disable selector-class-pattern,selector-max-compound-selectors */
#app
    .edit-multiselect
    .multiselect__tags
    .multiselect__tags-wrap
    .multiselect__tag {
    flex-basis: 50px;
    margin-bottom: 3px;
}
/* stylelint-enable selector-class-pattern,selector-max-compound-selectors */
</style>
