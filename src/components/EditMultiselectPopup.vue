<template>
    <div class="multiselect-popup-container">
        <Multiselect
            ref="ms"
            class="multiselect-popup-container__multiselect"
            v-bind="$attrs"
            v-on="$listeners"
            open-direction="bottom"
            :multiple="false"
            @select="optionSelected"
            @close="closePopup"
            @keyup="keyUp"
            />
        <div class="multiselect-popup-container__close-button">
            <button type="button" class="icon-close" :title="t('cookbook', 'Close')" @click="closePopup">{{ t('cookbook', 'Close') }}</button>
        </div>
    </div>
</template>

<script>
import Multiselect from '@nextcloud/vue/dist/Components/Multiselect'
export default {
    name: "EditMultiselectPopup",
    components: {
        Multiselect
    },
    props: {
        focused: false
    },
    watch: {
        focused () {
            if (this.focused == true) {
                this.$refs['ms'].$el.focus()
            }
        }
    },
    methods: {
        closePopup: function(opt) {
            this.$parent.$emit("ms-popup-selection-canceled")
        },
        optionSelected: function(opt) {
            this.$parent.$emit("ms-popup-selected", opt)
        },
        keyUp: function(e) {
            if (e.keyCode === 13 ) {
                console.log('13')
            }
            if (e.keyCode === 10 ) {
                console.log('10')
            }

        }
    }
}
</script>

<style scoped>

.multiselect-popup-container {
    display: flex;
    position: absolute;
    z-index: 1100;
    left: 0px;
    top: 50px;
    width: 100%;
    height: calc(100% - 50px - 44px);
    padding: .5em;
    background-color: var(--color-main-background);
}

    @media(min-width:768px) { .multiselect-popup-container {
        width: 500px;
        height: 300px;
        left: 50%;
        top: 50%;
        transform: translate(-50%,-50%);
        border-radius: 5px;
    }}


    .multiselect-popup-container__multiselect {
        width: 100%;
    }

    .multiselect-popup-container__close-button {
        height: 44px;
        width: 100%;
        position: fixed;
        bottom: 0px;
        left: 0px;
        background: var(--color-main-background);
    }
        @media(min-width:768px) { .multiselect-popup-container__close-button {
            display: none;
        }}


        .multiselect-popup-container__close-button > button {
            height: 44px;
            width: calc(100vw - 10px);
            margin: 5px;
            position: fixed;
            bottom: 0px;
            background: var(--color-main-background);
        }
</style>

<style>
@media(max-width:767px) {
    .multiselect-popup-container .multiselect div.multiselect__content-wrapper {
        max-height: calc(100vh - 145px) !important;
    }}

</style>
