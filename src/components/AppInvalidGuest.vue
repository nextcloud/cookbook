<template>
    <NcContent app-name="cookbook">
        <NcAppContent>
            <div class="main">
                <div class="dialog">
                    <div class="message">
                        {{ t("cookbook", "Cannot access recipe folder.") }}
                    </div>
                    <div>
                        {{
                            // prettier-ignore
                            t("cookbook", "You are logged in with a guest account. Therefore, you are not allowed to generate arbitrary files and folders on this Nextcloud instance. To be able to use the Cookbook app as a guest, you need to specify a folder where all recipes are stored. You will need write permission to this folder."                            )
                        }}
                    </div>
                    <div>
                        <button @click.prevent="selectFolder">
                            {{ t("cookbook", "Select recipe folder") }}
                        </button>
                    </div>
                </div>
            </div>
        </NcAppContent>
    </NcContent>
</template>

<script>
import NcContent from "@nextcloud/vue/dist/Components/NcContent"
import NcAppContent from "@nextcloud/vue/dist/Components/NcAppContent"

export default {
    name: "InvalidGuest",
    components: {
        // eslint-disable-next-line vue/no-reserved-component-names
        NcContent,
        NcAppContent,
    },
    methods: {
        selectFolder() {
            OC.dialogs.filepicker(
                t("cookbook", "Path to your recipe collection"),
                (path) => {
                    this.$store
                        .dispatch("updateRecipeDirectory", { dir: path })
                        .then(() => {
                            window.location.reload()
                        })
                },
                false, // Single result
                ["httpd/unix-directory"], // Desired MIME type
                true, // Make modal dialog
            )
        },
    },
}
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
