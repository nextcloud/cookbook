<template>
    <NcModal :title="title" @close="$close">
        <div class="modal__wrapper">
            <div class="modal__content">{{ content }}</div>
            <div class="modal__button-bar">
                <NcButton type="primary" @click="$close">{{
                    t('cookbook', 'Dismiss')
                }}</NcButton>
            </div>
        </div>
    </NcModal>
</template>

<script setup>
import NcModal from '@nextcloud/vue/dist/Components/NcModal.js';
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js';
</script>

<script>
export default {
    name: 'SimpleAlertModal',
};
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
