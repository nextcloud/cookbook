import { onMounted, ref } from 'vue';

export const DelayedDisplayProps = {
    /** Delay in milliseconds before the component is displayed */
    delay: {
        type: Number,
        default: 0,
    },
};

export default function useDelayedDisplay(delay = 800) {
    /**
     * @type {import('vue').Ref<boolean>}
     */
    const isVisible = ref(false);

    // Vue lifecycle
    onMounted(() => {
        window.setTimeout(() => {
            isVisible.value = true;
        }, delay);
    });

    return { isVisible };
}
