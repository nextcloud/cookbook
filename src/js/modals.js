// import { create } from 'vue-modal-dialogs';
// TODO Add real alternative

import SimpleAlertModal from '../components/Modals/SimpleAlertModal.vue';
import SimpleConfirmModal from '../components/Modals/SimpleConfirmModal.vue';

export function showSimpleAlertModal(msg) {
    alert('showSimpleAlertModal', msg);
}

export function showSimpleConfirmModal(msg) {
    return confirm('showSimpleConfirmModal', msg);
}

/*
export const showSimpleAlertModal = create(
    SimpleAlertModal,
    'content',
    'title',
);
export const showSimpleConfirmModal = create(
    SimpleConfirmModal,
    'content',
    'title',
);
*/
