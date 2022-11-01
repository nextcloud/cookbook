<template>
    <div class="multiselect-popup-container">
        <NcMultiselect
            ref="ms"
            class="multiselect-popup-container__multiselect"
            v-bind="$attrs"
            open-direction="bottom"
            :multiple="false"
            v-on="$listeners"
            @select="optionSelected"
            @close="closePopup"
        />
        <div class="multiselect-popup-container__close-button">
            <button
                type="button"
                class="icon-close"
                :title="t('cookbook', 'Close')"
                @click="closePopup"
            >
                {{ t("cookbook", "Close") }}
            </button>
        </div>
    </div>
</template>

<script>
import NcMultiselect from "@nextcloud/vue/dist/Components/NcMultiselect"

export default {
    name: "EditMultiselectPopup",
    components: {
        NcMultiselect,
    },
    props: {
        focused: {
            type: Boolean,
            default: false,
        },
    },
    watch: {
        focused() {
            if (this.focused === true) {
                this.$refs.ms.$el.focus()
            }
        },
    },
    methods: {
        closePopup() {
            this.$parent.$emit("ms-popup-selection-canceled")
        },
        optionSelected(opt) {
            this.$parent.$emit("ms-popup-selected", opt)
        },
    },
}
</script>

<style scoped>
.multiselect-popup-container {
    position: absolute;
    z-index: 1100;
    top: 50px;
    left: 0;
    display: flex;
    width: 100%;
    height: calc(100% - 50px - 44px);
    padding: 0.5em;
    background-color: var(--color-main-background);
}

@media (min-width: 768px) {
    .multiselect-popup-container {
        top: 50%;
        left: 50%;
        width: 500px;
        height: 300px;
        border-radius: 5px;
        transform: translate(-50%, -50%);
    }
}

/* stylelint-disable selector-class-pattern */
.multiselect-popup-container__multiselect {
    width: 100%;
}
/* stylelint-enable selector-class-pattern */

/* stylelint-disable selector-class-pattern */
.multiselect-popup-container__close-button {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 44px;
    background: var(--color-main-background);
}
/* stylelint-enable selector-class-pattern */

@media (min-width: 768px) {
    /* stylelint-disable selector-class-pattern */
    .multiselect-popup-container__close-button {
        display: none;
    }
    /* stylelint-enable selector-class-pattern */
}

/* stylelint-disable selector-class-pattern */
.multiselect-popup-container__close-button > button {
    position: fixed;
    bottom: 0;
    width: calc(100vw - 10px);
    height: 44px;
    margin: 5px;
    background: var(--color-main-background);
}
/* stylelint-enable selector-class-pattern */
</style>

<style>
@media (max-width: 767px) {
    /* stylelint-disable selector-class-pattern */
    .multiselect-popup-container .multiselect .multiselect__content-wrapper {
        max-height: calc(100vh - 145px) !important;
    }
    /* stylelint-enable selector-class-pattern */
}
</style>
