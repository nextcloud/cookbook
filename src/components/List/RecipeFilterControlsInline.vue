<!--
SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors

SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later
-->

<template>
    <div class="container">
        <div class="form-group">
            <RecipeSortSelect
                v-model:value="sorting"
                :aria-label="
                    t('cookbook', 'Show settings for filtering recipe list')
                "
                :label="t('cookbook', 'Order')"
                class="mr-4"
                :title="t('cookbook', 'Show filter settings')"
            />
        </div>
        <div class="form-group">
            <NcTextField
                v-model="searchTerm"
                class="input"
                :label="t('cookbook', 'Filter name')"
                :placeholder="t('cookbook', 'Search term')"
                :aria-placeholder="t('cookbook', 'Search term')"
                trailing-button-icon="close"
                :show-trailing-button="searchTerm !== ''"
                @trailing-button-click="clearSearchTerm"
                ><SearchIcon :size="20"
            /></NcTextField>
        </div>

        <div v-if="!hiddenSections['categories']" class="form-group">
            <div class="d-flex flex-row align-items-center">
                <NcSelect
                    v-model="filterCategories"
                    class="input input--with-operator"
                    input-id="categoriesFilterInput"
                    :options="uniqueCategories"
                    :loading="isLoading"
                    :label-outside="true"
                    :close-on-select="false"
                    :multiple="true"
                    :no-wrap="true"
                    :placeholder="t('cookbook', 'All categories')"
                    :aria-label="t('cookbook', 'Categories')"
                    :aria-placeholder="t('cookbook', 'All categories')"
                >
                    <template #list-header>
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
            </div>
        </div>

        <div class="form-group d-flex flex-row">
            <div class="d-flex flex-row align-items-center">
                <NcSelect
                    v-model="filterKeywords"
                    class="input input--with-operator"
                    input-id="keywordsFilterInput"
                    :options="uniqueKeywords"
                    :loading="isLoading"
                    :close-on-select="false"
                    :multiple="true"
                    :no-wrap="true"
                    :placeholder="t('cookbook', 'All keywords')"
                    :aria-label="t('cookbook', 'Keywords')"
                    :aria-placeholder="t('cookbook', 'All keywords')"
                    :label-outside="true"
                    style="max-width: 25%"
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
                            'Show recipes containing any selected keyword',
                        ),
                    }"
                    :unchecked-icon="OrIcon"
                    :unchecked-icon-props="{
                        title: t(
                            'cookbook',
                            'Show recipes containing all selected keywords',
                        ),
                    }"
                />
            </div>
            <!--        Keep button together in a line with the last input so it does not get lonely -->
            <NcButton variant="tertiary" @click="clearFilters">
                {{
                    /* TRANSLATORS Button text for applying recipe-filter values */
                    t('cookbook', 'Clear')
                }}
            </NcButton>
        </div>
    </div>
</template>

<script setup lang="ts">
import AndIcon from 'vue-material-design-icons/SetCenter.vue';
import OrIcon from 'vue-material-design-icons/SetAll.vue';
import SearchIcon from 'vue-material-design-icons/Magnify.vue';
import NcButton from '@nextcloud/vue/components/NcButton';
import NcSelect from '@nextcloud/vue/components/NcSelect';
import NcTextField from '@nextcloud/vue/components/NcTextField';
import RecipeSortSelect from './RecipeSortSelect.vue';
import ToggleIconButton from '../Utilities/ToggleIconButton.vue';

const t = window.t;
const n = window.n;

const emit = defineEmits(['close', 'input', 'reset-filters']);

const props = defineProps({
    value: {
        type: Object,
        default: () => ({
            filters: { categories: null, keywords: null },
            orderBy: {
                label: window.t('cookbook', 'Name'),
                iconUp: true,
                recipeProperty: 'name',
                order: 'ascending',
            },
        }),
    },
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

const sorting = defineModel('sorting', {
    type: Object,
    required: true,
});

function clearSearchTerm() {
    searchTerm.value = '';
}

function clearFilters() {
    emit('reset-filters');
}
</script>

<style lang="scss" scoped>
//@media (min-width: 1200px) {

//}

.d-flex {
    display: flex;
}

.flex-row {
    flex-direction: row;
}

.justify-end {
    justify-content: end;
}

.align-items-center {
    align-items: center;
}

.mt-4 {
    margin-top: 1rem;
}

.self-end {
    align-self: end;
}

@media (min-width: 1200px) {
    .modal__content {
        margin: 50px;
    }
}

.container {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    align-items: center;
    padding: 16px;

    .form-group {
        min-width: 150px;
        margin: 0.25rem;

        .input-field {
            margin-top: 0;
        }
    }
}

.input--with-operator {
    margin-inline-end: 0.25rem;
}
</style>
