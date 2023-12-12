<template>
    <NcContent app-name="cookbook">
        <NcAppContent>
            <div class="main">
                <div class="dialog">
                    <div class="message">
                        {{ t('cookbook', 'Cannot access recipe folder.') }}
                    </div>
                    <div>
                        {{
                            // prettier-ignore
                            t("cookbook", "You are logged in with a guest account. Therefore, you are not allowed to generate arbitrary files and folders on this Nextcloud instance. To be able to use the Cookbook app as a guest, you need to specify a folder where all recipes are stored. You will need write permission to this folder."                            )
                        }}
                    </div>
                    <div>
                        <button @click.prevent="selectFolder">
                            {{ t('cookbook', 'Select recipe folder') }}
                        </button>
                    </div>
                </div>
            </div>
        </NcAppContent>
    </NcContent>
</template>

<script setup>
import NcContent from '@nextcloud/vue/dist/Components/NcContent.js';
import NcAppContent from '@nextcloud/vue/dist/Components/NcAppContent.js';
import { getFilePickerBuilder, FilePickerType } from '@nextcloud/dialogs';

const selectFolder = () => {
    const filePicker = getFilePickerBuilder(
        t('cookbook', 'Path to your recipe collection'),
    )
        .addMimeTypeFilter('httpd/unix-directory')
        .setType(FilePickerType.Choose)
        .build();
    filePicker.pick().then((path) => {
        this.$store
            .dispatch('updateRecipeDirectory', { dir: path })
            .then(() => {
                window.location.reload();
            });
    });
};
</script>

<script>
export default {
    name: 'InvalidGuest',
};
</script>

<style lang="scss" scoped>
.main {
    display: flex;
    height: 100%;
    flex-direction: column;
    align-items: center;
    justify-content: center;

    .dialog {
        width: 50%;
        padding: 20px;
        border: 1px solid;
        border-radius: 15px;

        @media screen and (max-width: 800px) {
            width: 90%;
        }

        div {
            margin: 20px 5px;
        }

        .message {
            font-size: x-large;
            font-weight: bold;
        }
    }
}
</style>
