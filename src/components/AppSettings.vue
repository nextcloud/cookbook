<template>
    <AppNavigationSettings :open="true">
        <div id="app-settings">
            <fieldset>
                <ul>
                    <li>
                        <ActionButton
                            class="button"
                            :icon="
                                scanningLibrary
                                    ? 'icon-loading-small'
                                    : 'icon-history'
                            "
                            :title="t('cookbook', 'Rescan library')"
                            @click="$emit('reindex')"
                        />
                    </li>
                    <li>
                        <label class="settings-input">{{
                            t("cookbook", "Recipe folder")
                        }}</label>
                        <input
                            type="text"
                            :value="recipeFolder"
                            :placeholder="t('cookbook', 'Please pick a folder')"
                            @click="pickRecipeFolder"
                        />
                    </li>
                    <li>
                        <label class="settings-input">
                            {{ t("cookbook", "Update interval in minutes") }}
                        </label>
                        <input
                            v-model="updateInterval"
                            type="number"
                            class="input settings-input"
                            placeholder="0"
                        />
                    </li>
                    <li>
                        <input
                            id="recipe-print-image"
                            v-model="printImage"
                            type="checkbox"
                            class="checkbox"
                        />
                        <label for="recipe-print-image">
                            {{ t("cookbook", "Print image with recipe") }}
                        </label>
                    </li>
                </ul>
            </fieldset>
        </div>
    </AppNavigationSettings>
</template>

<script>
import axios from "@nextcloud/axios"
import ActionButton from "@nextcloud/vue/dist/Components/ActionButton"
import AppNavigationSettings from "@nextcloud/vue/dist/Components/AppNavigationSettings"

export default {
    name: "AppSettings",
    components: {
        ActionButton,
        AppNavigationSettings,
    },
    props: {
        scanningLibrary: {
            type: Boolean,
            default: false,
        },
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
        printImage(newVal, oldVal) {
            // Avoid infinite loop on page load and when reseting value after failed submit
            if (this.resetPrintImage) {
                this.resetPrintImage = false
                return
            }
            axios({
                url: `${this.$window.baseUrl}/config`,
                method: "POST",
                data: { print_image: newVal ? 1 : 0 },
            })
                .then(() => {
                    // Should this check the response of the query? To catch some errors that redirect the page
                })
                .catch(() => {
                    // eslint-disable-next-line no-alert
                    alert(
                        // prettier-ignore
                        t("cookbook","Could not set preference for image printing")
                    )
                    this.resetPrintImage = true
                    this.printImage = oldVal
                })
        },
        updateInterval(newVal, oldVal) {
            // Avoid infinite loop on page load and when reseting value after failed submit
            if (this.resetInterval) {
                this.resetInterval = false
                return
            }
            axios({
                url: `${this.$window.baseUrl}/config`,
                method: "POST",
                data: { update_interval: newVal },
            })
                .then(() => {
                    // Should this check the response of the query? To catch some errors that redirect the page
                })
                .catch(() => {
                    // eslint-disable-next-line no-alert
                    alert(
                        // prettier-ignore
                        t("cookbook","Could not set recipe update interval to {interval}",
                            {
                                interval: newVal,
                            }
                        )
                    )
                    this.resetInterval = true
                    this.updateInterval = oldVal
                })
        },
    },
    mounted() {
        this.setup()
    },
    methods: {
        /**
         * Select a recipe folder using the Nextcloud file picker
         */
        pickRecipeFolder() {
            OC.dialogs.filepicker(
                t("cookbook", "Path to your recipe collection"),
                (path) => {
                    const $this = this
                    this.$store
                        .dispatch("updateRecipeDirectory", { dir: path })
                        .then(() => {
                            $this.recipeFolder = path
                            if ($this.$route.path !== "/") {
                                $this.$router.push("/")
                            }
                        })
                        .catch(() => {
                            // eslint-disable-next-line no-alert
                            alert(
                                // prettier-ignore
                                t("cookbook","Could not set recipe folder to {path}",
                                    {
                                        path
                                    }
                                )
                            )
                        })
                },
                false,
                ["httpd/unix-directory"],
                true
            )
        },

        /**
         * Initial setup
         */
        setup() {
            axios({
                url: `${this.$window.baseUrl}/config`,
                method: "GET",
                data: null,
            })
                .then((response) => {
                    const config = response.data
                    this.resetPrintImage = false
                    if (config) {
                        this.printImage = config.print_image
                        this.updateInterval = config.update_interval
                        this.recipeFolder = config.folder
                    } else {
                        // eslint-disable-next-line no-alert
                        alert(t("cookbook", "Loading config failed"))
                    }
                })
                .catch(() => {
                    // eslint-disable-next-line no-alert
                    alert(t("cookbook", "Loading config failed"))
                })
        },
    },
}
</script>

<style>
#app-settings input[type="text"],
#app-settings input[type="number"],
#app-settings .button {
    display: block;
    width: 100%;
}

#app-settings .button {
    z-index: 2;
    height: 44px;
    padding: 0;
    border-radius: var(--border-radius);
}
#app-settings .button p {
    margin: auto;
    font-size: 13px;
}
</style>
