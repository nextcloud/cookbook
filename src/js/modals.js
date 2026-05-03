// import { create } from 'vue-modal-dialogs';
// TODO Add real alternative

import { useCookbookDialogs } from 'cookbook/composables/useCookbookDialogs';

import SimpleAlertModal from '../components/Modals/SimpleAlertModal.vue';
import SimpleConfirmModal from '../components/Modals/SimpleConfirmModal.vue';

const { show } = useCookbookDialogs();

export function showSimpleAlertModal(msg) {
    //alert('showSimpleAlertModal', msg);
    show(
        'showSimpleAlertModal title',
        msg,
        [
            {
                label: 'OK',
                variant: 'primary',
            },
        ]
    ).then(() => {
        console.log('Modal closed');
    });
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
