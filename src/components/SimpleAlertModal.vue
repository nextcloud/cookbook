<template>
    <Modal :title="title" @close="$close">
        <div class="modal__wrapper">
            <div class="modal__content">{{ content }}</div>
            <div class="modal__button-bar">
                <NcButton type="primary" @click="$close">{{
                    t("cookbook", "Dismiss")
                }}</NcButton>
            </div>
        </div>
    </Modal>
</template>

<script>
import Modal from "@nextcloud/vue/dist/Components/Modal"
import NcButton from "@nextcloud/vue/dist/Components/Button"

export default {
    name: "SimpleAlertModal",
    components: {
        Modal,
        NcButton,
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
