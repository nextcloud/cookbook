<!--
SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors

SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later
-->

<template>
    <NcDialog
        :name="t('cookbook', 'Import recipe from JSON')"
        size="normal"
        :open="true"
        @update:open="emit('close')"
    >
        <NcTextArea
            v-model="json"
            :label="t('cookbook', 'schema.org recipe JSON')"
            :placeholder="placeholder"
            :error="!!errorMessage"
            :helper-text="errorMessage"
            resize="vertical"
            rows="12"
        />
        <template #actions>
            <NcButton @click="emit('close')">
                {{ t('cookbook', 'Cancel') }}
            </NcButton>
            <NcButton
                variant="primary"
                :disabled="!json.trim() || isImporting"
                @click="importRecipe"
            >
                {{ t('cookbook', 'Import') }}
            </NcButton>
        </template>
    </NcDialog>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import NcButton from '@nextcloud/vue/components/NcButton';
import NcDialog from '@nextcloud/vue/components/NcDialog';
import NcTextArea from '@nextcloud/vue/components/NcTextArea';

import api from 'cookbook/js/api-interface';
import helpers from 'cookbook/js/helper';
import parseRecipeJson from 'cookbook/js/utils/parseRecipeJson';
import { useLegacyStore } from '../../store';
import type { RequestError } from '../../types/RequestError';

const emit = defineEmits<{ close: [] }>();

const t = window.t;
const legacyStore = useLegacyStore();

const placeholder =
    '{ "@context": "https://schema.org", "@type": "Recipe", "name": "…" }';

const json = ref('');
const errorMessage = ref('');
const isImporting = ref(false);

const parseRecipe = (): object | null => {
    try {
        const recipe = parseRecipeJson(json.value);
        if (!recipe) {
            errorMessage.value = t(
                'cookbook',
                'The JSON is not a schema.org recipe',
            );
        }
        return recipe;
    } catch {
        errorMessage.value = t('cookbook', 'The text is not valid JSON');
        return null;
    }
};

const importRecipe = async () => {
    errorMessage.value = '';
    const recipe = parseRecipe();
    if (!recipe) {
        return;
    }

    isImporting.value = true;
    try {
        const id = (await api.recipes.create(recipe)).data;
        legacyStore.setAppNavigationRefreshRequired({ isRequired: true });
        emit('close');
        helpers.goTo(`/recipe/${id}`);
    } catch (e) {
        errorMessage.value =
            (e as RequestError).response?.data?.msg ||
            t('cookbook', 'The server reported an error. Please check.');
    } finally {
        isImporting.value = false;
    }
};
</script>
