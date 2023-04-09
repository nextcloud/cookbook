<template>
    <NcModal :title="title" @close="$close">
        <div class="modal__wrapper">
            <div v-if="!multiline" class="modal__content">{{ content }}</div>
            <p v-else v-for="line in content" class="modal__content">{{ line }}</p>
            <div class="modal__button-bar">
                <NcButton type="primary" @click="$close">{{
                    t("cookbook", "Dismiss")
                }}</NcButton>
            </div>
        </div>
    </NcModal>
</template>

<script>
import NcModal from "@nextcloud/vue/dist/Components/NcModal"
import NcButton from "@nextcloud/vue/dist/Components/NcButton"

export default {
    name: "SimpleAlertModal",
    components: {
        NcModal,
        NcButton,
    },
    computed: {
        multiline() {
            return Array.isArray(this.content)
        },
    },
}
</script>

<style scoped>
.modal__wrapper {
    display: flex;
    flex-direction: column;
    padding: 25px;
    gap: 25px;
}

.modal__content {
    text-align: center;
}

.modal__button-bar {
    display: flex;
    justify-content: flex-end;
}
</style>
