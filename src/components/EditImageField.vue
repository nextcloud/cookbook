<template>
    <fieldset>
        <label>
            {{ fieldLabel }}
        </label>
        <input
            type="text"
            :value="value"
            @input="$emit('input', $event.target.value)"
        />
        <button
            type="button"
            :title="t('cookbook', 'Pick a local image')"
            @click="pickImage"
        >
            <span class="icon-category-multimedia"></span>
        </button>
    </fieldset>
</template>

<script>
export default {
    name: "EditImageField",
    props: {
        value: {
            type: String,
            default: "",
        },
        fieldLabel: {
            type: String,
            default: "",
        },
    },
    methods: {
        pickImage(e) {
            e.preventDefault()
            const $this = this
            OC.dialogs.filepicker(
                t("cookbook", "Path to your recipe image"),
                (path) => {
                    $this.$emit("input", path)
                },
                false,
                ["image/jpeg", "image/png"],
                true,
                OC.dialogs.FILEPICKER_TYPE_CHOOSE
            )
        },
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
    display: inline-block;
    width: 10em;
    height: 34px;
    font-weight: bold;
    line-height: 17px;
    vertical-align: top;
}
@media (max-width: 1199px) {
    fieldset > label {
        display: block;
        float: none;
    }
}

fieldset > input {
    width: calc(100% - 14em);
    border-right: 0;
    border-bottom-right-radius: 0;
    border-top-right-radius: 0;
}
@media (max-width: 1199px) {
    fieldset > input {
        width: calc(100% - 3em);
    }
}

fieldset > input + button {
    width: 3em;
    border-radius: 0 var(--border-radius) var(--border-radius) 0;
}

fieldset > input + button > * {
    pointer-events: none;
}
</style>
