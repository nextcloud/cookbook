// import { create } from 'vue-modal-dialogs';
// TODO Add real alternative

import { useCookbookDialogs } from 'cookbook/composables/useCookbookDialogs';

import SimpleAlertModal from '../components/Modals/SimpleAlertModal.vue';
import SimpleConfirmModal from '../components/Modals/SimpleConfirmModal.vue';

const { show, setClosingValue } = useCookbookDialogs();

export function showSimpleAlertModal(msg) {
    return show(
        'showSimpleAlertModal title',
        msg,
        true,
        [
            {
                label: t('cookbook', 'Dismiss'),
                variant: 'primary',
            },
        ]
    );
}

export function showSimpleConfirmModal(msg) {
    return show(
        'showSimpleAlertModal title',
        msg,
        false,
        [
            {
                label: t('cookbook', 'Cancel'),
                variant: 'secondary',
                callback: () => {
                    setClosingValue(false);
                },
            },
            {
                label: t('cookbook', 'OK'),
                variant: 'primary',
                callback: () => {
                    setClosingValue(true);
                },
            },
        ]
    );
    // return confirm('showSimpleConfirmModal', msg);
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
