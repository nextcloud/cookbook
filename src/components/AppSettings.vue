<template>
    <AppNavigationSettings :open="true">
        <div id="app-settings">
            <fieldset>
                <ul>
                    <li>
                        <ActionButton
                            class="button"
                            :icon="scanningLibrary ? 'icon-loading-small' : 'icon-history'"
                            @click="emit('reindex')"
                            :title="t('cookbook', 'Rescan library')"
                        />
                    </li>
                    <li>
                        <label class="settings-input">{{ t('cookbook', 'Recipe folder') }}</label>
                        <input type="text" :value="recipeFolder" @click="pickRecipeFolder" :placeholder="t('cookbook', 'Please pick a folder')">
                    </li>
                    <li>
                        <label class="settings-input">
                            {{ t('cookbook', 'Update interval in minutes') }}
                        </label>
                        <input type="number" class="input settings-input" v-model="updateInterval" placeholder="0">
                    </li>
                    <li>
                        <input type="checkbox" class="checkbox" v-model="printImage" id="recipe-print-image">
                        <label for="recipe-print-image">
                            {{ t('cookbook', 'Print image with recipe') }}
                        </label>
                    </li>
                </ul>
            </fieldset>
        </div>
    </AppNavigationSettings>
</template>

<script>
import axios from '@nextcloud/axios'
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'
import AppNavigationSettings from '@nextcloud/vue/dist/Components/AppNavigationSettings'

export default {
    name: 'AppSettings',
    props: ['scanningLibrary'],
    components: {
        ActionButton,
        AppNavigationSettings
    },
    data() {
        return {
            printImage: false,
            recipeFolder: "",
            resetPrintImage: true,
            // By setting the reset value initially to true, it will skip one watch event
            // (the one when config is loaded at page load)
            resetInterval: true,
            updateInterval: 0,
        }
    },
    watch: {

        printImage: function(newVal, oldVal) {
            // Avoid infinite loop on page load and when reseting value after failed submit
            if (this.resetPrintImage) {
                this.resetPrintImage = false
                return
            }
            axios({
                url: this.$window.baseUrl + '/config',
                method: 'POST',
                data: { 'print_image': newVal ? 1 : 0 }
            })
                .then((response) => {
                        // Should this check the response of the query? To catch some errors that redirect the page
                })
                .catch((e) => {
                    alert(t('cookbook', 'Could not set preference for image printing'));
                    this.resetPrintImage = true
                    this.printImage = oldVal
                })
        },
        updateInterval: function(newVal, oldVal) {
            // Avoid infinite loop on page load and when reseting value after failed submit
            if (this.resetInterval) {
                this.resetInterval = false
                return
            }
            axios({
                url: this.$window.baseUrl + '/config',
                method: 'POST',
                data: { 'update_interval': newVal }
                })
                .then((response) => {
                    // Should this check the response of the query? To catch some errors that redirect the page
                })
                .catch((e) => {
                    alert(t('cookbook', 'Could not set recipe update interval to {interval}', { interval: newVal }))
                    this.resetInterval = true
                    this.updateInterval = oldVal
                })
        }
    },
    methods: {
        /**
         * Select a recipe folder using the Nextcloud file picker
         */
        pickRecipeFolder: function(e) {
            OC.dialogs.filepicker(
                t('cookbook', 'Path to your recipe collection'),
                (path) => {
                    let $this = this
                    this.$store.dispatch('updateRecipeDirectory', { dir: path })
                        .then(() => {
                            $this.recipeFolder = path
                            if($this.$route.path != '/') {
                                $this.$router.push('/')
                            }
                        })
                        .catch((e) => {
                            alert(t('cookbook', 'Could not set recipe folder to {path}', { path: path }))
                        })
                },
                false,
                'httpd/unix-directory',
                true
            )
        },

        /**
         * Initial setup
         */
        setup: function() {
            axios({
                url: this.$window.baseUrl + '/config',
                method: 'GET',
                data: null,
                })
                .then((response) => {
                    let config = response.data
                    this.resetPrintImage = false;
                    if(config) {
                        this.printImage = config['print_image'];
                        this.updateInterval = config['update_interval'];
                        this.recipeFolder = config['folder'];
                    } else {
                        alert(t('cookbook', 'Loading config failed'))
                    }
                })
                .catch((e) => {
                    alert(t('cookbook', 'Loading config failed'))
                })
        }
    },
    mounted () {
        this.setup()
    },
}
</script>

<style>

#app-settings input[type="text"],
#app-settings input[type="number"],
#app-settings .button {
    width: 100%;
    display: block;
}

#app-settings .button {
    padding: 0;
    height: 44px;
    border-radius: var(--border-radius);
    z-index: 2;
}
    #app-settings .button p {
        margin: auto;
        font-size: 13px;
    }

</style>
