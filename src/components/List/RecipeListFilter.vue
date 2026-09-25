<!--
SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors

SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later
-->

<template>
    <div>
        <RecipeFilterControlsInline
            v-if="showFiltersInRecipeList && !isMobile"
            :value="inlineControlsValue"
            :preapplied-filters="props.preappliedFilters"
            :recipes="recipes"
            :is-loading="isLoading"
            :is-visible="isVisible"
        />
        <!-- @input="handleInlineControlsValueUpdated"
			@close="() => (isFilterControlsVisible = false)" -->
        <div
            v-if="isMobile && showFiltersInRecipeList"
            id="recipes-submenu"
            class="recipes-submenu-container"
        >
            <!-- <RecipeSortSelect
				v-if="recipes.length > 0"
				v-model:value="orderBy"
				class="mr-4"
				:title="t('cookbook', 'Show filter settings')"
				aria-label="t('cookbook', 'Show settings for filtering recipe list')"
			/> -->
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
            v-model="filter"
            :preapplied-filters="props.preappliedFilters"
            :recipes="recipes"
            :is-loading="isLoading"
            :is-visible="isFilterControlsModalVisible"
            @close="() => (isFilterControlsModalVisible = false)"
        />
    </div>
</template>

<script setup lang="ts">
import { defineEmits, defineProps, defineModel, ref } from 'vue';
import RecipeFilterControlsInline from './RecipeFilterControlsInline.vue';
import RecipeFilterControlsModal from './RecipeFilterControlsModal.vue';
import FilterIcon from 'vue-material-design-icons/FilterVariant.vue';
import { NcButton } from '@nextcloud/vue';
import { useIsMobile } from '@nextcloud/vue/composables/useIsMobile';
import { useLegacyStore } from '../../store';
import { useRoute } from 'vue-router';
import { Filter } from 'cookbook/types/RecipeListFilter';

const isMobile = useIsMobile();
const t = window.t;

const filter = defineModel<Filter>('filter', {
    type: Object,
    required: true,
});

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

// TODO remve this when the inline controls are fully implemented and integrated with the filter model
const inlineControlsValue = ref({});

const isFilterControlsModalVisible = ref(false);

function toggleFilterControlsModalVisible() {
    isFilterControlsModalVisible.value = !isFilterControlsModalVisible.value;
}
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
