<!--
SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors

SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later
-->

<template>
    <NcModal v-if="isVisible" @close="closeModal">
        <div class="modal__content">
            <h2>{{ t('cookbook', 'Recipe filters') }}</h2>

            <div class="form-group">
                <NcTextField
                    v-model="searchTerm"
                    :label="t('cookbook', 'Name')"
                    :placeholder="t('cookbook', 'Search term')"
                    :aria-placeholder="t('cookbook', 'Search term')"
                    trailing-button-icon="close"
                    :show-trailing-button="searchTerm !== ''"
                    @trailing-button-click="clearSearchTerm"
                    ><SearchIcon :size="20"
                /></NcTextField>
            </div>

            <div v-if="!hiddenSections['categories']" class="form-group">
                <label for="categoriesFilterInput">{{
                    t('cookbook', 'Categories')
                }}</label>
                <NcSelect
                    v-model="filterCategories"
                    input-id="categoriesFilterInput"
                    :options="uniqueCategories"
                    :loading="isLoading"
                    :close-on-select="false"
                    :multiple="true"
                    :no-wrap="true"
                    :placeholder="t('cookbook', 'All categories')"
                    :aria-label="t('cookbook', 'Categories')"
                    :aria-placeholder="t('cookbook', 'All categories')"
                    ><template #list-header>
                        <li style="padding: 0.25rem; text-align: center">
                            {{
                                n(
                                    'cookbook',
                                    '1 category selected',
                                    '{n} categories selected',
                                    filterCategories.length,
                                    {
                                        n: `${filterCategories.length.toString()}`,
                                    },
                                )
                            }}
                        </li>
                    </template></NcSelect
                >
                <div class="d-flex">
                    <ToggleIconButton
                        v-model="categoriesOperatorTypeUseAnd"
                        :checked-icon="AndIcon"
                        :icon-props="{
                            size: 25,
                            fillColor: 'var(--color-primary-light-text)',
                        }"
                        :checked-icon-props="{
                            title: t(
                                'cookbook',
                                'Show recipes containing any selected category',
                            ),
                        }"
                        :unchecked-icon="OrIcon"
                        :unchecked-icon-props="{
                            title: t(
                                'cookbook',
                                'Show recipes containing all selected categories',
                            ),
                        }"
                    />

                    <span
                        v-if="categoriesOperatorTypeUseAnd"
                        class="operator-toggle-text"
                        @click="
                            categoriesOperatorTypeUseAnd =
                                !categoriesOperatorTypeUseAnd
                        "
                        >{{
                            t('cookbook', 'Matching all selected categories')
                        }}</span
                    >
                    <span
                        v-else
                        class="operator-toggle-text"
                        @click="
                            categoriesOperatorTypeUseAnd =
                                !categoriesOperatorTypeUseAnd
                        "
                        >{{
                            t('cookbook', 'Matching any selected category')
                        }}</span
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="keywordsFilterInput">{{
                    t('cookbook', 'Keywords')
                }}</label>
                <NcSelect
                    v-model="filterKeywords"
                    input-id="keywordsFilterInput"
                    :options="uniqueKeywords"
                    :loading="isLoading"
                    :close-on-select="false"
                    :multiple="true"
                    :no-wrap="true"
                    :placeholder="t('cookbook', 'All keywords')"
                    :aria-label="t('cookbook', 'Keywords')"
                    :aria-placeholder="t('cookbook', 'All keywords')"
                >
                    <template #list-header>
                        <li style="padding: 0.25rem; text-align: center">
                            {{
                                n(
                                    'cookbook',
                                    '1 keyword selected',
                                    '{n} keywords selected',
                                    filterKeywords.length,
                                    {
                                        n: `${filterKeywords.length.toString()}`,
                                    },
                                )
                            }}
                        </li>
                    </template></NcSelect
                >
                <div class="d-flex">
                    <ToggleIconButton
                        v-model="keywordsOperatorTypeUseAnd"
                        :checked-icon="AndIcon"
                        :icon-props="{
                            size: 25,
                            fillColor: 'var(--color-primary-light-text)',
                        }"
                        :checked-icon-props="{
                            title: t(
                                'cookbook',
                                'Show recipes containing any selected category',
                            ),
                        }"
                        :unchecked-icon="OrIcon"
                        :unchecked-icon-props="{
                            title: t(
                                'cookbook',
                                'Show recipes containing all selected categories',
                            ),
                        }"
                    />

                    <span
                        v-if="keywordsOperatorTypeUseAnd"
                        class="operator-toggle-text"
                        @click="
                            keywordsOperatorTypeUseAnd =
                                !keywordsOperatorTypeUseAnd
                        "
                        >{{
                            t('cookbook', 'Matching all selected keywords')
                        }}</span
                    >
                    <span
                        v-else
                        class="operator-toggle-text"
                        @click="
                            keywordsOperatorTypeUseAnd =
                                !keywordsOperatorTypeUseAnd
                        "
                        >{{
                            t('cookbook', 'Matching any selected keyword')
                        }}</span
                    >
                </div>
            </div>
            <div class="d-flex flex-row justify-end mt-4">
                <NcButton variant="tertiary" @click="clearFilters">
                    {{
                        /* TRANSLATORS Button text for applying recipe-filter values */
                        t('cookbook', 'Clear')
                    }}
                </NcButton>
                <NcButton
                    variant="primary"
                    class="self-end"
                    @click="submitAndClose"
                >
                    {{
                        /* TRANSLATORS Button text for applying recipe-filter values */
                        t('cookbook', 'Apply')
                    }}
                </NcButton>
            </div>
        </div>
    </NcModal>
</template>

<script setup lang="ts">
import SearchIcon from 'vue-material-design-icons/Magnify.vue';
import NcButton from '@nextcloud/vue/components/NcButton';
import NcModal from '@nextcloud/vue/components/NcModal';
import NcSelect from '@nextcloud/vue/components/NcSelect';
import NcTextField from '@nextcloud/vue/components/NcTextField';
import AndIcon from 'vue-material-design-icons/SetCenter.vue';
import OrIcon from 'vue-material-design-icons/SetAll.vue';
import ToggleIconButton from '../Utilities/ToggleIconButton.vue';

const t = window.t;
const n = window.n;
const emit = defineEmits(['close', 'input', 'reset-filters']);

const props = defineProps({
    fieldLabel: { type: String, default: '' },
    /**
     * List of sections that should be hidden from the filters list, e.g., `['categories', 'keywords']`
     */
    preappliedFilters: { type: Array, default: () => [] },
    isLoading: { type: Boolean, default: false },
    isVisible: { type: Boolean, default: false },
    recipes: { type: Array, default: () => [] },
    uniqueCategories: { type: Array, default: () => [] },
    uniqueKeywords: { type: Array, default: () => [] },
    hiddenSections: { type: Object, default: () => ({}) },
});

const searchTerm = defineModel('search-term', {
    type: String,
    required: true,
});

const filterCategories = defineModel('filter-categories', {
    type: Array,
    required: true,
});

const categoriesOperatorTypeUseAnd = defineModel(
    'categories-operator-use-and',
    {
        type: Boolean,
        required: true,
    },
);

const filterKeywords = defineModel('filter-keywords', {
    type: Array,
    required: true,
});

const keywordsOperatorTypeUseAnd = defineModel('keywords-operator-use-and', {
    type: Boolean,
    required: true,
});

function clearSearchTerm() {
    searchTerm.value = '';
}

function clearFilters() {
    emit('reset-filters');
}

function closeModal() {
    emit('close');
}

function submitAndClose() {
    //     submitFilters();
    emit('close');
}
</script>

<style lang="scss" scoped>
.d-flex {
    display: flex;
}

.flex-row {
    flex-direction: row;
}

.justify-end {
    justify-content: end;
}

.mb-2 {
    margin-bottom: 0.5rem;
}

.mr-2 {
    margin-inline-end: 0.5rem;
}

.mt-4 {
    margin-top: 1rem;
}

.self-end {
    align-self: end;
}

.modal__content {
    display: flex;
    flex-direction: column;
    margin: 15px 20px 20px;

    h2 {
        text-align: center;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        margin: calc(var(--default-grid-baseline) * 2) 0;

        label {
            margin-bottom: 0.75em;
        }

        .select {
            width: 100%;
        }
    }
    .operator-toggle-text {
        display: flex;
        align-items: center;
        color: var(--text-muted);
    }
}

@media (min-width: 1200px) {
    .modal__content {
        margin: 50px;
    }
}

.container {
    display: flex;
    flex-direction: column;
    padding: 16px;

    .title {
        margin-bottom: 1rem;
        padding-inline-end: 30px;
    }

    .option {
        margin-bottom: 1rem;
    }
}
@media screen {
    .container {
        display: flex;
        padding: 16px;
    }
}
</style>
