// import { create } from 'vue-modal-dialogs';
// TODO Add real alternative

import { useCookbookDialogs } from 'cookbook/composables/useCookbookDialogs';

const { show, setClosingValue } = useCookbookDialogs();

export function showSimpleAlertModal(title, msg) {
    return show(title, msg, true, [
        {
            label: t('cookbook', 'Dismiss'),
            variant: 'primary',
        },
    ]);
}

export function showSimpleConfirmModal(title, msg) {
    return show(title, msg, false, [
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
    ]);
    // return confirm('showSimpleConfirmModal', msg);
}
