<template>
    <NcAppSettingsDialog
        :open.sync="isOpen"
        :title="t('cookbook', 'Cookbook settings')"
        :show-navigation="true"
        first-selected-section="keyboard shortcuts"
    >
        <NcAppSettingsSection
            id="settings-recipe-folder"
            :title="t('cookbook', 'Recipe folder')"
            class="app-settings-section"
        >
            <fieldset>
                <ul>
                    <li>
                        <NcButton @click="reindex">
                            <template #icon>
                                <LoadingIcon v-if="scanningLibrary" />
                                <ReloadIcon v-else />
                            </template>
                            {{ t("cookbook", "Rescan library") }}
                        </NcButton>
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
                </ul>
            </fieldset>
        </NcAppSettingsSection>
        <NcAppSettingsSection
            id="settings-recipe-display"
            :title="t('cookbook', 'Recipe display settings')"
            class="app-settings-section"
        >
            <fieldset>
                <ul>
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
                    <li>
                        <input
                            id="tag-cloud"
                            v-model="showTagCloudInRecipeList"
                            type="checkbox"
                            class="checkbox"
                        />
                        <label for="tag-cloud">
                            {{
                                // prettier-ignore
                                t("cookbook", "Show keyword cloud in recipe lists")
                            }}
                        </label>
                    </li>
                </ul>
            </fieldset>
        </NcAppSettingsSection>
        <NcAppSettingsSection
            id="settings-info-blocks"
            :title="t('cookbook', 'Info blocks')"
            class="app-settings-section"
        >
            <fieldset>
                <legend class="settings-info-blocks__legend">
                    {{
                        t(
                            "cookbook",
                            "Control which blocks of information are shown in the recipe view. If you do not use some features and find them distracting, you may hide them."
                        )
                    }}
                </legend>
                <ul>
                    <li>
                        <input
                            id="info-blocks-checkbox-preparation-time"
                            v-model="visibleInfoBlocks"
                            type="checkbox"
                            class="checkbox"
                            value="preparation-time"
                        />
                        <label for="info-blocks-checkbox-preparation-time">
                            {{ t("cookbook", "Preparation time") }}
                        </label>
                    </li>
                    <li>
                        <input
                            id="info-blocks-checkbox-cooking-time"
                            v-model="visibleInfoBlocks"
                            type="checkbox"
                            class="checkbox"
                            value="cooking-time"
                        />
                        <label for="info-blocks-checkbox-cooking-time">
                            {{ t("cookbook", "Cooking time") }}
                        </label>
                    </li>
                    <li>
                        <input
                            id="info-blocks-checkbox-total-time"
                            v-model="visibleInfoBlocks"
                            type="checkbox"
                            class="checkbox"
                            value="total-time"
                        />
                        <label for="info-blocks-checkbox-total-time">
                            {{ t("cookbook", "Total time") }}
                        </label>
                    </li>
                    <li>
                        <input
                            id="info-blocks-checkbox-nutrition-information"
                            v-model="visibleInfoBlocks"
                            type="checkbox"
                            class="checkbox"
                            value="nutrition-information"
                        />
                        <label for="info-blocks-checkbox-nutrition-information">
                            {{ t("cookbook", "Nutrition information") }}
                        </label>
                    </li>
                    <li>
                        <input
                            id="info-blocks-checkbox-tools"
                            v-model="visibleInfoBlocks"
                            type="checkbox"
                            class="checkbox"
                            value="tools"
                        />
                        <label for="info-blocks-checkbox-tools">
                            {{ t("cookbook", "Tools") }}
                        </label>
                    </li>
                </ul>
            </fieldset>
        </NcAppSettingsSection>
    </NcAppSettingsDialog>
</template>

<script>
import { subscribe, unsubscribe } from "@nextcloud/event-bus"

import NcAppSettingsDialog from "@nextcloud/vue/dist/Components/NcAppSettingsDialog"
import NcAppSettingsSection from "@nextcloud/vue/dist/Components/NcAppSettingsSection"
import NcButton from "@nextcloud/vue/dist/Components/NcButton"
import LoadingIcon from "icons/Loading.vue"
import ReloadIcon from "icons/Cached.vue"

import api from "cookbook/js/api-interface"
import { showSimpleAlertModal } from "cookbook/js/modals"

export const SHOW_SETTINGS_EVENT = "show-settings"

const INFO_BLOCK_KEYS = [
    "preparation-time",
    "cooking-time",
    "total-time",
    "nutrition-information",
    "tools",
]

// The Vue representation of multiple checkboxes is an array of all checked values
// However, the backend representation is an object (map of block ids to booleans)
const visibleInfoBlocksEncode = (arr) =>
    Object.fromEntries(INFO_BLOCK_KEYS.map((key) => [key, arr.includes(key)]))
const visibleInfoBlocksDecode = (obj) =>
    Object.entries(obj)
        .filter(([, v]) => v)
        .map(([k]) => k)

export default {
    name: "SettingsDialog",
    components: {
        NcButton,
        NcAppSettingsDialog,
        NcAppSettingsSection,
        LoadingIcon,
        ReloadIcon,
    },
    data() {
        return {
            isOpen: false,
            printImage: false,
            recipeFolder: "",
            resetPrintImage: true,
            showTagCloudInRecipeList: true,
            resetTagCloud: true,
            scanningLibrary: false,
            // By setting the reset value initially to true, it will skip one watch event
            // (the one when config is loaded at page load)
            resetInterval: true,
            updateInterval: 0,
            visibleInfoBlocks: [...INFO_BLOCK_KEYS],
            resetVisibleInfoBlocks: true,
        }
    },
    watch: {
        async printImage(newVal, oldVal) {
            // Avoid infinite loop on page load and when reseting value after failed submit
            if (this.resetPrintImage) {
                this.resetPrintImage = false
                return
            }
            try {
                await api.config.printImage.update(newVal)
                // Should this check the response of the query? To catch some errors that redirect the page
            } catch {
                await showSimpleAlertModal(
                    // prettier-ignore
                    t("cookbook","Could not set preference for image printing")
                )
                this.resetPrintImage = true
                this.printImage = oldVal
            }
        },
        // eslint-disable-next-line no-unused-vars
        showTagCloudInRecipeList(newVal, oldVal) {
            this.$store.dispatch("setShowTagCloudInRecipeList", {
                showTagCloud: newVal,
            })
        },
        async updateInterval(newVal, oldVal) {
            // Avoid infinite loop on page load and when reseting value after failed submit
            if (this.resetInterval) {
                this.resetInterval = false
                return
            }
            try {
                await api.config.updateInterval.update(newVal)
                // Should this check the response of the query? To catch some errors that redirect the page
            } catch {
                await showSimpleAlertModal(
                    // prettier-ignore
                    t("cookbook","Could not set recipe update interval to {interval}",
                        {
                            interval: newVal,
                        }
                    )
                )
                this.resetInterval = true
                this.updateInterval = oldVal
            }
        },
        async visibleInfoBlocks(newVal, oldVal) {
            // Avoid infinite loop on page load and when reseting value after failed submit
            if (this.resetVisibleInfoBlocks) {
                this.resetVisibleInfoBlocks = false
                return
            }
            try {
                const data = visibleInfoBlocksEncode(newVal)
                await api.config.visibleInfoBlocks.update(data)
                await this.$store.dispatch("refreshConfig")
                // Should this check the response of the query? To catch some errors that redirect the page
            } catch (err) {
                // eslint-disable-next-line no-console
                console.error("Error while trying to save info blocks", err)
                await showSimpleAlertModal(
                    t("cookbook", "Couldn't save visible info blocks")
                )
                this.resetVisibleInfoBlocks = true
                this.visibleInfoBlocks = oldVal
            }
        },
    },
    mounted() {
        this.setup()

        subscribe(SHOW_SETTINGS_EVENT, this.handleShowSettings)
    },
    methods: {
        handleShowSettings() {
            this.isOpen = true
        },

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
                        .catch(() =>
                            showSimpleAlertModal(
                                // prettier-ignore
                                t("cookbook","Could not set recipe folder to {path}",
                                {
                                    path
                                }
                            )
                            )
                        )
                },
                false,
                ["httpd/unix-directory"],
                true
            )
        },

        /**
         * Initial setup
         */
        async setup() {
            try {
                await this.$store.dispatch("refreshConfig")
                const { config } = this.$store.state
                this.resetPrintImage = false
                this.resetVisibleInfoBlocks = false

                if (!config) {
                    throw new Error()
                }

                this.printImage = config.print_image
                this.visibleInfoBlocks = visibleInfoBlocksDecode(
                    config.visibleInfoBlocks
                )
                this.showTagCloudInRecipeList =
                    this.$store.state.localSettings.showTagCloudInRecipeList
                this.updateInterval = config.update_interval
                this.recipeFolder = config.folder
            } catch (err) {
                this.$log.error("Error setting up SettingsDialog", err)
                await showSimpleAlertModal(
                    t("cookbook", "Loading config failed")
                )
            }
        },

        /**
         * Reindex all recipes
         */
        reindex() {
            const $this = this
            if (this.scanningLibrary) {
                // No repeat clicks until we're done
                return
            }
            this.scanningLibrary = true
            api.recipes
                .reindex()
                .then(() => {
                    $this.scanningLibrary = false
                    this.$log.info("Library reindexing complete")
                    if (
                        ["index", "search"].indexOf(this.$store.state.page) > -1
                    ) {
                        // This refreshes the current router view in case items in it changed during reindex
                        $this.$router.go()
                    } else {
                        $this.getCategories()
                    }
                })
                .catch(() => {
                    $this.scanningLibrary = false
                    this.$log.error("Library reindexing failed!")
                })
        },

        beforeDestroy() {
            unsubscribe(SHOW_SETTINGS_EVENT, this.handleShowSettings)
        },
    },
}
</script>

<style scoped>
/* TODO: Use @nextcloud/vue LoadingIcon once we update to 7.0.0 and we won't
 * have to do this */
.material-design-icon.loading-icon:deep(svg) {
    animation: rotate var(--animation-duration, 0.8s) linear infinite;
    color: var(--color-loading-dark);
}

.settings-info-blocks__legend {
    margin-bottom: 10px;
}
</style>

<style>
#app-settings input[type="text"],
#app-settings input[type="number"],
#app-settings .button.disable {
    display: block;
    width: 100%;
}

/* #app-settings .button { */
/*     z-index: 2; */
/*     height: 44px; */
/*     padding: 0; */
/*     border-radius: var(--border-radius); */
/* } */

/* #app-settings .button p { */
/*     margin: auto; */
/*     font-size: 13px; */
/* } */
</style>
