<template>
    <Content app-name="cookbook">
        <!-- <AppNavi class="app-navigation" /> -->
        <AppContent>
            <div class="main">
                <div class="dialog">
                    <div class="message">
                        {{ t('cookbook', 'Cannot access recipe folder.') }}
                    </div>
                    <div class="explanation">
                        {{
                            t('cookbook', 'You are logged in with a guest account. Therefore, you are not allowed to generate arbitrary files and folders on this nextcloud instance. To be able to use the cookbook app as a guest account, you need to specify a folder where all recipes are stored. You will need write permission to this folder.')
                        }}
                    </div>
                    <div class="explanation">
                        <button
                            @click.prevent="selectFolder"
                        >
                            {{ t('cookbook', 'Select recipe folder') }}
                        </button>
                    </div>

                </div>
            </div>
        </AppContent>
    </Content>
</template>

<script>

import Content from '@nextcloud/vue/dist/Components/Content'
import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import axios from '@nextcloud/axios'

export default {
    name: "InvalidGuest",
    components: {
        Content,
        AppContent
    },
    methods: {
        selectFolder: function (e){
            OC.dialogs.filepicker(
                t('cookbook', 'Path to your recipe collection'),
                (path) => {
                    this.$store
                        .dispatch('updateRecipeDirectory', {dir: path})
                        .then(function(){
                            location.reload()
                        })
                },
                false, // Single result
                'httpd/unix-directory', // Desired MIME type
                true // Make modal dialog
            )
        },
    },
}
</script>

<style lang="scss" scoped>
div.main {
    height: 100%;
    display: flex;
    flex-direction: colummn;
    justify-content: center;
    align-items: center;

    div.dialog {
        width: 50%;
        height: 40%;

        padding: 20px;
        border: 1px solid;
        border-radius: 15px;
        
        div {
            margin: 20px 5px;
        }
        div.message {
            font-weight: bold;
            font-size: x-large;
        }

    }
}
</style>