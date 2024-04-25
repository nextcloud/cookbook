<template>
    <div class="recipe-filter-controls flex align-items-center">
        <!-- Search Field -->
        <div
            v-if="!hideFilterTypes.includes(FilterType.SearchFilter)"
            class="form-group mr-2 flex-auto align-items-center"
        >
            <NcTextField
                :value.sync="searchTerm"
                :label="t('cookbook', 'Name')"
                :placeholder="t('cookbook', 'Search term')"
                :aria-placeholder="t('cookbook', 'Search term')"
                trailing-button-icon="close"
                :show-trailing-button="searchTerm !== ''"
                style="margin-block-start: 0"
                class="m-0"
                @trailing-button-click="clearSearchTerm"
                ><SearchIcon :size="20"
            /></NcTextField>
        </div>

        <!-- Filter Button -->
        <NcButton
            :type="'secondary'"
            aria-label="t('cookbook', 'Show settings for filtering recipe list')"
            :title="t('cookbook', 'Show filter settings')"
            @click="toggleFilterControls"
        >
            <template #icon>
                <FilterIcon :size="20" />
            </template>
        </NcButton>

        <NcModal v-if="isFilterControlsVisible" @close="closeModal">
            <div class="modal__content">
                <h2>{{ t('cookbook', 'Recipe filters') }}</h2>

                <RecipeSortSelect
                    v-if="recipes.length > 0"
                    v-model="localOrderBy"
                    class="mr-4"
                    :title="t('cookbook', 'Show order settings')"
                    aria-label="t('cookbook', 'Show settings for setting recipe order')"
                />

                <div
                    v-if="
                        !hideFilterTypes.includes(FilterType.CategoriesFilter)
                    "
                    class="form-group"
                >
                    <label for="categoriesFilterInput">{{
                        t('cookbook', 'Categories')
                    }}</label>
                    <NcSelect
                        v-model="selectedCategories"
                        input-id="categoriesFilterInput"
                        :options="uniqueCategories"
                        :loading="isLoading"
                        :close-on-select="false"
                        :multiple="true"
                        :no-wrap="true"
                        :placeholder="t('cookbook', 'All categories')"
                        :aria-placeholder="t('cookbook', 'All categories')"
                        ><template #list-header>
                            <li style="padding: 0.25rem; text-align: center">
                                {{
                                    n(
                                        'cookbook',
                                        '1 category selected',
                                        '{n} categories selected',
                                        selectedCategories.length,
                                        {
                                            n: `${selectedCategories.length.toString()}`,
                                        },
                                    )
                                }}
                            </li>
                        </template></NcSelect
                    >
                    <div class="flex">
                        <ToggleIconButton
                            v-model="categoriesOperatorToggleValue"
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
                            v-if="categoriesOperatorToggleValue"
                            class="operator-toggle-text"
                            @click="
                                categoriesOperatorToggleValue =
                                    !categoriesOperatorToggleValue
                            "
                            >{{
                                t(
                                    'cookbook',
                                    'Matching all selected categories',
                                )
                            }}</span
                        >
                        <span
                            v-else
                            class="operator-toggle-text"
                            @click="
                                categoriesOperatorToggleValue =
                                    !categoriesOperatorToggleValue
                            "
                            >{{
                                t('cookbook', 'Matching any selected category')
                            }}</span
                        >
                    </div>
                </div>

                <div
                    v-if="!hideFilterTypes.includes(FilterType.KeywordsFilter)"
                    class="form-group"
                >
                    <label for="keywordsFilterInput">{{
                        t('cookbook', 'Keywords')
                    }}</label>
                    <NcSelect
                        v-model="selectedKeywords"
                        input-id="keywordsFilterInput"
                        :options="uniqueKeywords"
                        :loading="isLoading"
                        :close-on-select="false"
                        :multiple="true"
                        :no-wrap="true"
                        :placeholder="t('cookbook', 'All keywords')"
                        :aria-placeholder="t('cookbook', 'All keywords')"
                    >
                        <template #list-header>
                            <li style="padding: 0.25rem; text-align: center">
                                {{
                                    n(
                                        'cookbook',
                                        '1 keyword selected',
                                        '{n} keywords selected',
                                        selectedKeywords.length,
                                        {
                                            n: `${selectedKeywords.length.toString()}`,
                                        },
                                    )
                                }}
                            </li>
                        </template></NcSelect
                    >
                    <div class="flex">
                        <ToggleIconButton
                            v-model="keywordsOperatorToggleValue"
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
                            v-if="keywordsOperatorToggleValue"
                            class="operator-toggle-text"
                            @click="
                                keywordsOperatorToggleValue =
                                    !keywordsOperatorToggleValue
                            "
                            >{{
                                t('cookbook', 'Matching all selected keywords')
                            }}</span
                        >
                        <span
                            v-else
                            class="operator-toggle-text"
                            @click="
                                keywordsOperatorToggleValue =
                                    !keywordsOperatorToggleValue
                            "
                            >{{
                                t('cookbook', 'Matching any selected keyword')
                            }}</span
                        >
                    </div>
                </div>
                <div class="flex flex-row justify-end mt-4">
                    <NcButton type="tertiary" @click="clearFilters">
                        {{
                            // TRANSLATORS Button text for applying recipe-filter values
                            t('cookbook', 'Clear')
                        }}
                    </NcButton>
                    <NcButton type="primary" class="self-end" @click="submit">
                        {{
                            // TRANSLATORS Button text for applying recipe-filter values
                            t('cookbook', 'Apply')
                        }}
                    </NcButton>
                </div>
            </div>
        </NcModal>
    </div>
</template>

<script setup>
import { computed, inject, nextTick, ref } from 'vue';
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js';
import NcModal from '@nextcloud/vue/dist/Components/NcModal.js';
import NcSelect from '@nextcloud/vue/dist/Components/NcSelect.js';
import NcTextField from '@nextcloud/vue/dist/Components/NcTextField.js';
import AndIcon from 'icons/SetCenter.vue';
import OrIcon from 'icons/SetAll.vue';
import FilterIcon from 'icons/FilterVariant.vue';
import SearchIcon from 'icons/Magnify.vue';
import RecipeSortSelect from 'cookbook/components/List/RecipeSortSelect.vue';
import ToggleIconButton from 'cookbook/components/Utilities/ToggleIconButton.vue';
import useRecipeFilterControls from 'cookbook/composables/useRecipeFilterControls';
import FilterType from 'cookbook/js/Enums/FilterType';

// DI
const Store = inject('Store');

const emit = defineEmits(['close', 'input']);

const props = defineProps({
    value: {
        type: Object,
        default: () => ({
            filters: [],
            orderBy: {
                label: t('cookbook', 'Name'),
                iconUp: true,
                recipeProperty: 'name',
                order: 'ascending',
            },
        }),
    },
    fieldLabel: { type: String, default: '' },
    /**
     * List of filter types for filters that should be hidden from the filters list.
     * @type {FilterType[]}
     */
    hideFilterTypes: {
        type: Array,
        default: () => [],
    },
    isLoading: { type: Boolean, default: false },
    recipes: { type: Array, default: () => [] },
});

const localOrderBy = ref(props.value.orderBy);

const {
    uniqueCategories,
    selectedCategories,
    uniqueKeywords,
    selectedKeywords,
    searchTerm,
    localFiltersValue,
    categoriesOperatorToggleValue,
    keywordsOperatorToggleValue,
    onUiControlsUpdated,
    submitFilters,
} = useRecipeFilterControls(props, Store);

/**
 * If the filter controls are visible
 * @type {import('vue').Ref<boolean>}
 */
const isFilterControlsVisible = ref(false);

// ==============================
// Computed
// ==============================
const emittedValue = computed(() => ({
    filters: localFiltersValue.value,
    orderBy: localOrderBy.value,
}));

// ==============================
// Methods
// ==============================
function clearSearchTerm() {
    searchTerm.value = '';
    Store.dispatch('clearRecipeFilters');
}

function clearFilters() {
    selectedCategories.value = [];
    selectedKeywords.value = [];
    clearSearchTerm();
}

/**
 * Hides the modal window.
 */
function closeModal() {
    isFilterControlsVisible.value = false;
    emit('close');
}

/**
 * Toggles the visibility of the filter modal.
 */
function toggleFilterControls() {
    isFilterControlsVisible.value = !isFilterControlsVisible.value;
}

/**
 * Submits the filters to update the list of recipes.
 */
async function submit() {
    onUiControlsUpdated();
    await nextTick();
    emit('input', emittedValue.value);
    submitFilters();
    closeModal();
}
</script>

<style lang="scss" scoped>
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
        padding-right: 30px;
        margin-bottom: 1rem;
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
