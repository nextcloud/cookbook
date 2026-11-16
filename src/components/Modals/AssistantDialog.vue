<!--
SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors

SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later
-->

<template>
    <NcDialog
        :open="open"
        :name="t('cookbook', 'Edit recipe with Nextcloud Assistant')"
        :no-close="isProcessing"
        :close-on-click-outside="!isProcessing"
        size="normal"
        @update:open="handleOpenChanged"
    >
        <div class="assistant-dialog">
            <p>
                {{
                    /* prettier-ignore */
                    t('cookbook', 'Describe what the Assistant should change. You can review the result in the editor before you save it.')
                }}
            </p>
            <NcTextArea
                ref="promptField"
                v-model="prompt"
                :label="t('cookbook', 'Instruction for the assistant')"
                :placeholder="
                    t('cookbook', 'E.g.: Convert all units to metric')
                "
                :disabled="isProcessing"
                resize="vertical"
                @keydown.ctrl.enter="submit"
            />
            <NcNoteCard v-if="errorMessage" type="error">
                {{ errorMessage }}
            </NcNoteCard>
            <div v-if="isProcessing" class="assistant-dialog__processing">
                <NcLoadingIcon :size="20" />
                <span>
                    {{
                        /* prettier-ignore */
                        t('cookbook', 'The Nextcloud Assistant is working on the recipe. This might take a while.')
                    }}
                </span>
            </div>
        </div>
        <template #actions>
            <NcButton variant="secondary" @click="cancel">
                {{ t('cookbook', 'Cancel') }}
            </NcButton>
            <NcButton variant="primary" :disabled="!canSubmit" @click="submit">
                <template #icon>
                    <CreationIcon :size="20" />
                </template>
                {{ t('cookbook', 'Edit recipe') }}
            </NcButton>
        </template>
    </NcDialog>
</template>

<script setup lang="ts">
import {
    computed,
    getCurrentInstance,
    onBeforeUnmount,
    onMounted,
    ref,
} from 'vue';

import NcButton from '@nextcloud/vue/components/NcButton';
import NcDialog from '@nextcloud/vue/components/NcDialog';
import NcLoadingIcon from '@nextcloud/vue/components/NcLoadingIcon';
import NcNoteCard from '@nextcloud/vue/components/NcNoteCard';
import NcTextArea from '@nextcloud/vue/components/NcTextArea';

import CreationIcon from 'icons/Creation.vue';

import api from 'cookbook/js/api-interface';

import type {
    AssistantRecipeUpdate,
    AssistantTaskState,
} from '../../types/Assistant';
import type { Recipe } from '../../types/Recipe';
import type { RequestError } from '../../types/RequestError';

const t = window.t;
const log = getCurrentInstance()?.proxy?.$log ?? console;

/** Delay between two polls for the result of a task in milliseconds */
const POLL_INTERVAL = 2000;
/** Maximum time to wait for the result of a task in milliseconds */
const MAX_WAIT = 5 * 60 * 1000;

const props = defineProps<{
    open: boolean;
    recipe: Recipe;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'applied', update: AssistantRecipeUpdate): void;
}>();

const promptField = ref<InstanceType<typeof NcTextArea> | null>(null);
const prompt = ref('');
const errorMessage = ref('');
const taskId = ref<number | null>(null);
const isProcessing = ref(false);

const canSubmit = computed(
    () => !isProcessing.value && prompt.value.trim() !== '',
);

onMounted(() => {
    promptField.value?.focus();
});

const sleep = (ms: number) =>
    new Promise<void>((resolve) => {
        window.setTimeout(resolve, ms);
    });

const getErrorMessage = (error: unknown): string =>
    (error as RequestError)?.response?.data?.msg ??
    t(
        'cookbook',
        'The request to the Nextcloud Assistant failed. See logs in your browser (press F12).',
    );

const stopProcessing = () => {
    isProcessing.value = false;
    taskId.value = null;
};

const cancelRunningTask = async () => {
    const id = taskId.value;
    stopProcessing();
    if (id === null) {
        return;
    }
    try {
        await api.assistant.cancelTask(id);
    } catch (error) {
        // The task may already be finished. Nothing to do about it.
        log.debug('Could not cancel the assistant task', error);
    }
};

const handleFinalState = (state: AssistantTaskState) => {
    stopProcessing();
    if (state.status === 'successful') {
        emit('applied', state.recipe ?? {});
        emit('update:open', false);
    } else if (state.status === 'failed') {
        errorMessage.value =
            state.error ??
            t(
                'cookbook',
                'The Nextcloud Assistant could not process the recipe.',
            );
    } else if (state.status === 'cancelled') {
        errorMessage.value = t(
            'cookbook',
            'The task of the Nextcloud Assistant was cancelled.',
        );
    }
};

/**
 * Poll the state of the task until it is finished or the maximum waiting
 * time is over. Stops silently once the task is no longer the current one,
 * i.e. it was cancelled.
 */
const waitForResult = async (id: number) => {
    const deadline = Date.now() + MAX_WAIT;
    while (Date.now() < deadline) {
        await sleep(POLL_INTERVAL);
        if (taskId.value !== id) {
            return;
        }
        let state: AssistantTaskState;
        try {
            state = (await api.assistant.getTask(id)).data;
        } catch (error) {
            if (taskId.value === id) {
                log.error(error);
                errorMessage.value = getErrorMessage(error);
                await cancelRunningTask();
            }
            return;
        }
        if (taskId.value !== id) {
            return;
        }
        if (state.status !== 'scheduled' && state.status !== 'running') {
            handleFinalState(state);
            return;
        }
    }

    await cancelRunningTask();
    errorMessage.value = t(
        'cookbook',
        'The Nextcloud Assistant did not answer in time.',
    );
};

const submit = async () => {
    if (!canSubmit.value) {
        return;
    }
    errorMessage.value = '';
    isProcessing.value = true;
    let id: number;
    try {
        id = (await api.assistant.createTask(props.recipe, prompt.value.trim()))
            .data.id;
    } catch (error) {
        log.error(error);
        errorMessage.value = getErrorMessage(error);
        stopProcessing();
        return;
    }
    taskId.value = id;
    if (!isProcessing.value) {
        // Cancelled or closed while the task was being created
        await cancelRunningTask();
        return;
    }
    await waitForResult(id);
};

const cancel = () => {
    if (isProcessing.value) {
        // Only abort the running task but keep the dialog open to allow a retry
        cancelRunningTask();
        return;
    }
    emit('update:open', false);
};

const handleOpenChanged = (isOpen: boolean) => {
    if (!isOpen) {
        cancelRunningTask();
    }
    emit('update:open', isOpen);
};

onBeforeUnmount(() => {
    cancelRunningTask();
});
</script>

<script lang="ts">
export default {
    name: 'AssistantDialog',
};
</script>

<style scoped>
.assistant-dialog {
    display: flex;
    flex-direction: column;
    padding: 0 12px 12px;
    gap: 12px;
}

.assistant-dialog__processing {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 8px;
}
</style>
