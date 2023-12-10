<template>
    <NcAppSettingsDialog
        :open.sync="isOpen"
        :name="t('cookbook', 'Cookbook settings')"
        :show-navigation="true"
        first-selected-section="keyboard shortcuts"
    >
        <NcAppSettingsSection
            id="settings-recipe-folder"
            :name="t('cookbook', 'Recipe folder')"
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
                            {{ t('cookbook', 'Rescan library') }}
                        </NcButton>
                    </li>
                    <li>
                        <label class="settings-input">{{
                            t('cookbook', 'Recipe folder')
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
                            {{ t('cookbook', 'Update interval in minutes') }}
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
            :name="t('cookbook', 'Recipe display settings')"
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
                            {{ t('cookbook', 'Print image with recipe') }}
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
                                t('cookbook', 'Show keyword cloud in recipe lists')
                            }}
                        </label>
                    </li>
                </ul>
            </fieldset>
        </NcAppSettingsSection>
        <NcAppSettingsSection
            id="settings-info-blocks"
            :name="t('cookbook', 'Info blocks')"
            class="app-settings-section"
        >
            <fieldset>
                <legend class="settings-info-blocks__legend">
                    {{
                        // prettier-ignore
                        t('cookbook', 'Control which blocks of information are shown in the recipe view. If you do not use some features and find them distracting, you may hide them.')
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
                            {{ t('cookbook', 'Preparation time') }}
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
                            {{ t('cookbook', 'Cooking time') }}
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
                            {{ t('cookbook', 'Total time') }}
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
                            {{ t('cookbook', 'Nutrition information') }}
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
                            {{ t('cookbook', 'Tools') }}
                        </label>
                    </li>
                </ul>
            </fieldset>
        </NcAppSettingsSection>
        <NcAppSettingsSection
            id="debug"
            :name="t('cookbook', 'Frontend debug settings')"
            class="app-settings-section"
        >
            <legend class="settings-info-blocks__legend">
                {{
                    // prettier-ignore
                    t('cookbook', 'This allows to temporarily enable logging in the browser console in case of problems. You will not need these settings by default.')
                }}
            </legend>
            <NcButton @click="enableLogger">{{
                t('cookbook', 'Enable debugging')
            }}</NcButton>
        </NcAppSettingsSection>
    </NcAppSettingsDialog>
</template>

<script setup>
import {
    getCurrentInstance,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
    nextTick,
} from 'vue';
import { subscribe, unsubscribe } from '@nextcloud/event-bus';
import { getFilePickerBuilder } from '@nextcloud/dialogs';

import NcAppSettingsDialog from '@nextcloud/vue/dist/Components/NcAppSettingsDialog.js';
import NcAppSettingsSection from '@nextcloud/vue/dist/Components/NcAppSettingsSection.js';
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js';
import { NcLoadingIcon as LoadingIcon } from '@nextcloud/vue';
import ReloadIcon from 'icons/Cached.vue';

import api from 'cookbook/js/api-interface';
import { showSimpleAlertModal } from 'cookbook/js/modals';

import { enableLogging } from 'cookbook/js/logging';
import { useRoute, useRouter } from 'vue-router/composables';
import { useStore } from '../../store';
import { SHOW_SETTINGS_EVENT } from '../../composables/useSettingsDialog';

const log = getCurrentInstance().proxy.$log;
const route = useRoute();
const router = useRouter();
const store = useStore();

const INFO_BLOCK_KEYS = [
    'preparation-time',
    'cooking-time',
    'total-time',
    'nutrition-information',
    'tools',
];

// The Vue representation of multiple checkboxes is an array of all checked values
// However, the backend representation is an object (map of block ids to booleans)
const visibleInfoBlocksEncode = (arr) =>
    Object.fromEntries(INFO_BLOCK_KEYS.map((key) => [key, arr.includes(key)]));
const visibleInfoBlocksDecode = (obj) =>
    Object.entries(obj)
        .filter(([, v]) => v)
        .map(([k]) => k);

/**
 * @type {import('vue').Ref<boolean>}
 */
const isOpen = ref(false);
/**
 * @type {import('vue').Ref<boolean>}
 */
const printImage = ref(false);
/**
 * @type {import('vue').Ref<string>}
 */
const recipeFolder = ref('');
/**
 * @type {import('vue').Ref<boolean>}
 */
const showTagCloudInRecipeList = ref(true);
/**
 * @type {import('vue').Ref<boolean>}
 */
const scanningLibrary = ref(false);
/**
 * @type {import('vue').Ref<number>}
 */
const updateInterval = ref(0);
/**
 * @type {import('vue').Ref<Array>}
 */
const visibleInfoBlocks = ref([...INFO_BLOCK_KEYS]);
/**
 * @type {import('vue').Ref<boolean>}
 */
const writeChanges = ref(true);

// Watchers
watch(
    () => printImage.value,
    async (newVal, oldVal) => {
        // Avoid infinite loop on page load and when resetting value after failed submit
        if (!writeChanges.value) {
            return;
        }
        try {
            await api.config.printImage.update(newVal);
            await store.dispatch('refreshConfig');
            // Should this check the response of the query? To catch some errors that redirect the page
        } catch {
            await showSimpleAlertModal(
                // prettier-ignore
                t('cookbook','Could not set preference for image printing'),
            );
            printImage.value = oldVal;
        }
    },
);

// eslint-disable-next-line no-unused-vars
watch(
    () => showTagCloudInRecipeList.value,
    (newVal) => {
        if (!writeChanges.value) {
            return;
        }

        store.dispatch('setShowTagCloudInRecipeList', {
            showTagCloud: newVal,
        });
    },
);

watch(
    () => updateInterval.value,
    async (newVal, oldVal) => {
        // Avoid infinite loop on page load and when resetting value after failed submit
        if (!writeChanges.value) {
            return;
        }
        try {
            await api.config.updateInterval.update(newVal);
            await store.dispatch('refreshConfig');
        } catch {
            await showSimpleAlertModal(
                // prettier-ignore
                t('cookbook','Could not set recipe update interval to {interval}',
                {
                    interval: newVal,
                }
            ),
            );
            updateInterval.value = oldVal;
        }
    },
);

watch(
    () => visibleInfoBlocks.value,
    async (newVal, oldVal) => {
        // Avoid infinite loop on page load and when resetting value after failed submit
        if (!writeChanges.value) {
            return;
        }
        try {
            const data = visibleInfoBlocksEncode(newVal);
            await api.config.visibleInfoBlocks.update(data);
            await store.dispatch('refreshConfig');
            // Should this check the response of the query? To catch some errors that redirect the page
        } catch (err) {
            // eslint-disable-next-line no-console
            console.error('Error while trying to save info blocks', err);
            await showSimpleAlertModal(
                t('cookbook', 'Could not save visible info blocks'),
            );
            visibleInfoBlocks.value = oldVal;
        }
    },
);

/**
 * Select a recipe folder using the Nextcloud file picker
 */
const pickRecipeFolder = () => {
    const filePicker = getFilePickerBuilder(
        t('cookbook', 'Path to your recipe collection'),
    )
        .addMimeTypeFilter('httpd/unix-directory')
        .addButton({
            label: 'Pick',
            type: 'primary',
        })
        .build();
    filePicker.pick().then((path) => {
        store
            .dispatch('updateRecipeDirectory', { dir: path })
            .then(() => store.dispatch('refreshConfig'))
            .then(() => {
                recipeFolder.value = path;
                if (route.path !== '/') {
                    router.push('/');
                }
            })
            .catch(() =>
                showSimpleAlertModal(
                    // prettier-ignore
                    t('cookbook','Could not set recipe folder to {path}',
                        {
                            path
                        }
                    ),
                ),
            );
    });
};

/**
 * Reindex all recipes
 */
const reindex = () => {
    if (scanningLibrary.value) {
        // No repeat clicks until we're done
        return;
    }
    scanningLibrary.value = true;
    api.recipes
        .reindex()
        .then(() => {
            scanningLibrary.value = false;
            log.info('Library reindexing complete');
            if (['index', 'search'].indexOf(store.state.page) > -1) {
                // This refreshes the current router view in case items in it changed during reindex
                router.go();
            }
            // todo this ws here before but no function was defined. what is it supposed to do?
            // else {
            //     getCategories();
            // }
        })
        .catch(() => {
            scanningLibrary.value = false;
            log.error('Library reindexing failed!');
        });
};

const enableLogger = () => {
    enableLogging();
};

const handleShowSettings = () => {
    isOpen.value = true;

    // Temporarily disable the storage of settings to allow for initialization
    writeChanges.value = false;

    const { config } = store.state;

    if (!config) {
        throw new Error();
    }

    printImage.value = config.print_image;
    visibleInfoBlocks.value = visibleInfoBlocksDecode(config.visibleInfoBlocks);
    showTagCloudInRecipeList.value =
        store.state.localSettings.showTagCloudInRecipeList;
    updateInterval.value = config.update_interval;
    recipeFolder.value = config.folder;

    nextTick(() => {
        writeChanges.value = true;
    });
};

// Vue lifecycle
onMounted(() => {
    subscribe(SHOW_SETTINGS_EVENT, handleShowSettings);
});

onBeforeUnmount(() => {
    unsubscribe(SHOW_SETTINGS_EVENT, handleShowSettings);
});
</script>

<script>
export default {
    name: 'SettingsDialog',
};
</script>

<style scoped>
.settings-info-blocks__legend {
    margin-bottom: 10px;
}
</style>

<style>
#app-settings input[type='text'],
#app-settings input[type='number'],
#app-settings .button.disable {
    display: block;
    width: 100%;
}
</style>
