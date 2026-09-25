<!--
SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors

SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later
-->

<template>
    <div>
        <RecipeFilterControlsInline
            v-if="showFiltersInRecipeList && !isMobile"
            v-model:search-term="searchTerm"
            v-model:filter-categories="filterCategories"
            v-model:categories-operator-use-and="filterCategoriesTypeUseAnd"
            v-model:filter-keywords="filterKeywords"
            v-model:keywords-operator-use-and="filterKeywordsTypeUseAnd"
            v-model:sorting="orderBy"
            :preapplied-filters="props.preappliedFilters"
            :recipes="recipes"
            :is-loading="isLoading"
            :is-visible="isVisible"
            :unique-categories="uniqueCategories"
            :hidden-sections="hiddenSections"
            :unique-keywords="uniqueKeywords"
            @reset-filters="clearFilters"
        />
        <!-- @input="handleInlineControlsValueUpdated"
			@close="() => (isFilterControlsVisible = false)" -->
        <div
            v-if="isMobile && showFiltersInRecipeList"
            id="recipes-submenu"
            class="recipes-submenu-container"
        >
            <RecipeSortSelect
                v-if="recipes.length > 0"
                v-model:value="orderBy"
                class="mr-4"
                :title="t('cookbook', 'Show filter settings')"
                aria-label="t('cookbook', 'Show settings for filtering recipe list')"
            />
            <NcButton
                :variant="'secondary'"
                aria-label="t('cookbook', 'Show settings for filtering recipe list')"
                :title="t('cookbook', 'Show filter settings')"
                @click="toggleFilterControlsModalVisible"
            >
                <template #icon>
                    <FilterIcon :size="20" />
                </template>
            </NcButton>
        </div>
        <RecipeFilterControlsModal
            v-if="isMobile && showFiltersInRecipeList"
            v-model:search-term="searchTerm"
            v-model:filter-categories="filterCategories"
            v-model:categories-operator-use-and="filterCategoriesTypeUseAnd"
            v-model:filter-keywords="filterKeywords"
            v-model:keywords-operator-use-and="filterKeywordsTypeUseAnd"
            :preapplied-filters="props.preappliedFilters"
            :recipes="recipes"
            :is-loading="isLoading"
            :is-visible="isFilterControlsModalVisible"
            :unique-categories="uniqueCategories"
            :hidden-sections="hiddenSections"
            :unique-keywords="uniqueKeywords"
            @close="() => (isFilterControlsModalVisible = false)"
            @reset-filters="clearFilters"
        />
    </div>
</template>

<script setup lang="ts">
import { defineProps, defineModel, ref, computed } from 'vue';
import RecipeFilterControlsInline from './RecipeFilterControlsInline.vue';
import RecipeFilterControlsModal from './RecipeFilterControlsModal.vue';
import FilterIcon from 'vue-material-design-icons/FilterVariant.vue';
import NcButton from '@nextcloud/vue/components/NcButton';
import { useIsMobile } from '@nextcloud/vue/composables/useIsMobile';
import RecipeSortSelect from './RecipeSortSelect.vue';

import {
    RecipeCategoriesFilter as CategoriesFilter,
    RecipeKeywordsFilter as KeywordsFilter,
    RecipeNamesFilter as NamesFilter,
} from 'cookbook/js/RecipeFilters';
import type { Recipe } from '../../types/Recipe';

const isMobile = useIsMobile();
const t = window.t;

const sorting = defineModel('sorting', {
    type: Object,
    default: () => ({}),
});

const props = defineProps({
    preappliedFilters: {
        type: Array,
        default: () => [],
    },
    recipes: {
        type: Array,
        default: () => [],
    },
    isLoading: {
        type: Boolean,
        default: false,
    },
    isVisible: {
        type: Boolean,
        default: false,
    },
    showFiltersInRecipeList: {
        type: Boolean,
        default: true,
    },
});

const searchTerm = defineModel('searchTerm', {
    type: String,
    required: true,
});
const filterCategories = defineModel('filterCategories', {
    type: Array,
    required: true,
});
const filterCategoriesTypeUseAnd = defineModel('filterCategoriesTypeUseAnd', {
    type: Boolean,
    required: true,
});
const filterKeywords = defineModel('filterKeywords', {
    type: Array,
    required: true,
});
const filterKeywordsTypeUseAnd = defineModel('filterKeywordsTypeUseAnd', {
    type: Boolean,
    required: true,
});

const orderBy = defineModel('orderBy', { type: Object, required: true });

// TODO remve this when the inline controls are fully implemented and integrated with the filter model
const inlineControlsValue = ref({});

const isFilterControlsModalVisible = ref(false);

function toggleFilterControlsModalVisible() {
    isFilterControlsModalVisible.value = !isFilterControlsModalVisible.value;
}

const caseInsensitiveSort = (a: string, b: string) => {
    const aUpper = a.toUpperCase();
    const bUpper = b.toUpperCase();
    if (aUpper < bUpper) return -1;
    if (bUpper < aUpper) return 1;
    return 0;
};

/**
 * An array of all categories in the recipes. These are neither sorted nor unique
 */
const rawCategories = computed(() => {
    const categoriesArray = (props.recipes as Recipe[]).map((r) => {
        if (!('category' in r)) {
            return [];
        }
        if (r.category != null) {
            return r.category.split(',');
        }
        return [];
    });
    return ([] as string[]).concat(...categoriesArray);
});

/**
 * List of sections with their visible state.
 * @type {import('vue').ComputedRef<{categories: boolean, keywords: boolean, names: boolean}>}
 */
const hiddenSections = computed(() => ({
    categories: props.preappliedFilters.some(
        (f) => f instanceof CategoriesFilter,
    ),
    keywords: props.preappliedFilters.some((f) => f instanceof KeywordsFilter),
    names: props.preappliedFilters.some((f) => f instanceof NamesFilter),
}));

/**
 * A unique set of all categories in the recipes.
 * @type {import('vue').ComputedRef<Array<string>>}
 */
const uniqueCategories = computed(() =>
    [...new Set(rawCategories.value)].sort(caseInsensitiveSort),
);

function clearFilters() {
    searchTerm.value = '';
    filterCategories.value = [];
    filterCategoriesTypeUseAnd.value = false;
    filterKeywords.value = [];
    filterKeywordsTypeUseAnd.value = true;
}

/**
 * An array of all keywords in the recipes. These are neither sorted nor unique
 */
const rawKeywords = computed(() => {
    const keywordsArray = (props.recipes as Recipe[]).map((r) => {
        if (!('keywords' in r)) {
            return [];
        }
        if (typeof r.keywords === 'string') {
            return r.keywords.split(',');
        }
        if (Array.isArray(r.keywords)) {
            return r.keywords;
        }
        return [];
    });
    return ([] as string[]).concat(...keywordsArray);
});

/**
 * A unique and sorted set of all keywords in all recipes.
 */
const uniqueKeywords = computed(() =>
    [...new Set(rawKeywords.value)].sort(caseInsensitiveSort),
);
</script>

<script lang="ts">
export default {
    name: 'RecipeListFilter',
};
</script>

<style>
/* stylelint-disable selector-class-pattern */
#recipes-submenu .multiselect .multiselect__tags {
    border: 0;
}
/* stylelint-enable selector-class-pattern */
</style>

<style scoped>
.mr-4 {
    margin-inline-end: 1rem;
}

.pt-2 {
    padding-top: 0.5rem;
}

.loading-indicator {
    display: flex;
    justify-content: center;
    padding: 3rem 0;
}

.recipes-submenu-container {
    display: flex;
    padding: 0.5rem 16px 16px;
    margin-bottom: 0.75ex;
}

.recipes {
    display: flex;
    width: 100%;
    flex-direction: row;
    flex-wrap: wrap;
}
</style>
