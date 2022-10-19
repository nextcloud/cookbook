<template>
    <Modal :title="title" @close="$close(false)">
        <div class="modal__wrapper">
            <div class="modal__content">{{ content }}</div>
            <div class="modal__button-bar">
                <NcButton @click="$close(false)">{{
                    t("cookbook", "Cancel")
                }}</NcButton>
                <NcButton type="primary" @click="$close(true)">{{
                    t("cookbook", "OK")
                }}</NcButton>
            </div>
        </div>
    </Modal>
</template>

<script>
import Modal from "@nextcloud/vue/dist/Components/Modal"
import NcButton from "@nextcloud/vue/dist/Components/Button"

export default {
    name: "SimpleConfirmModal",
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
    gap: 15px;
}
</style>
