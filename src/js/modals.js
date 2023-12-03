import { create } from 'vue-modal-dialogs';

import SimpleAlertModal from '../components/Modals/SimpleAlertModal.vue';
import SimpleConfirmModal from '../components/Modals/SimpleConfirmModal.vue';

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
