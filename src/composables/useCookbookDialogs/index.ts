import { ref } from 'vue';

const isDialogOpen = ref(false);
const resolvePromise = ref<null | ((value: any) => void)>(null);
const buttons = ref({});
const title = ref('');
const message = ref('');

export function useCookbookDialogs() {
    function show(_title: string, _message: string, _buttons = {}) {
        title.value = _title;
        message.value = _message;
        buttons.value = _buttons;
        isDialogOpen.value = true;
        return new Promise((resolve) => {
            resolvePromise.value = resolve;
        });
    }
    function close() {
        isDialogOpen.value = false;
        resolvePromise.value?.(null);
    }
    return {
        isDialogOpen,
        buttons,
        show,
        close,
        dialogTitle: title,
        dialogMessage: message,
    };
}
