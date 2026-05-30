import { ref } from 'vue';

const isDialogOpen = ref(false);
const resolvePromise = ref<null | ((value: any) => void)>(null);
const buttons = ref({});
const title = ref('');
const message = ref('');
const allowClose = ref(true);
let nextClosingValue = null;

export function useCookbookDialogs() {
    function show(_title: string, _message: string, _allowClose: boolean = true, _buttons = {}) {
        title.value = _title;
        message.value = _message;
        allowClose.value = _allowClose;
        buttons.value = _buttons;
        nextClosingValue = null;
        isDialogOpen.value = true;
        return new Promise((resolve) => {
            resolvePromise.value = resolve;
        });
    }
    function close() {
        isDialogOpen.value = false;
        resolvePromise.value?.(nextClosingValue);
    }
    function setClosingValue(value: any) {
        nextClosingValue = value;
    }
    return {
        isDialogOpen,
        buttons,
        show,
        close,
        setClosingValue,
        dialogTitle: title,
        dialogMessage: message,
        dialogAllowClose: allowClose,
    };
}
