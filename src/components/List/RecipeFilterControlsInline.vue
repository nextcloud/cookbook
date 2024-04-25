<template>
    <div class="container">
        <div
            v-if="!hideFilterTypes.includes(FilterType.SearchFilter)"
            class="form-group w-full"
        >
            <NcTextField
                class="input"
                :value.sync="searchTerm"
                :label="t('cookbook', 'Filter recipes')"
                :placeholder="t('cookbook', 'Search term')"
                :aria-placeholder="t('cookbook', 'Search term')"
                trailing-button-icon="close"
                :show-trailing-button="searchTerm !== ''"
                @trailing-button-click="clearSearchTerm"
                @input="onSearchInputUpdated"
                @keyup="onTextInputKeyUp"
                ><SearchIcon :size="20"
            /></NcTextField>
        </div>
        <div class="flex gap-2">
            <div class="form-group form-group--inline">
                <RecipeSortSelect
                    v-model="localOrderBy"
                    aria-label="t('cookbook', 'Show settings for filtering recipe list')"
                    :label="t('cookbook', 'Order')"
                    class="mr-4"
                    :title="t('cookbook', 'Show filter settings')"
                    @input="submitOrderBy"
                />
            </div>

            <div
                v-if="!hideFilterTypes.includes(FilterType.CategoriesFilter)"
                class="form-group form-group--inline"
            >
                <div class="flex flex-row align-items-center">
                    <NcSelect
                        v-model="selectedCategories"
                        class="input input--with-operator"
                        input-id="categoriesFilterInput"
                        :options="uniqueCategories"
                        :loading="isLoading"
                        :close-on-select="false"
                        :multiple="true"
                        :no-wrap="true"
                        :placeholder="t('cookbook', 'All categories')"
                        :aria-label="t('cookbook', 'Categories')"
                        :aria-placeholder="t('cookbook', 'All categories')"
                        @input="onCategoriesSelectionUpdated"
                    >
                        <template #list-header>
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
                        @update="onCategoriesSelectionUpdated"
                    />
                </div>
            </div>

            <div
                v-if="!hideFilterTypes.includes(FilterType.KeywordsFilter)"
                class="form-group form-group--inline flex flex-row"
            >
                <div class="flex flex-row align-items-center">
                    <NcSelect
                        v-model="selectedKeywords"
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
                        style="max-width: 25%"
                        @input="onKeywordsSelectionUpdated"
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
                        @update="onKeywordsSelectionUpdated"
                    />
                </div>
                <!--        Keep button together in a line with the last input so it does not get lonely -->
                <NcButton
                    v-if="localFiltersValue.length > 0"
                    type="tertiary"
                    @click="clearFilters"
                >
                    {{
                        // TRANSLATORS Button text for applying recipe-filter values
                        t('cookbook', 'Clear')
                    }}
                </NcButton>
                <NcButton v-if="isFormDirty" type="secondary" @click="submit">
                    {{
                        // TRANSLATORS Button text for applying recipe-filter values
                        t('cookbook', 'Apply')
                    }}
                </NcButton>
            </div>
        </div>
    </div>
</template>

<script setup>
import AndIcon from 'vue-material-design-icons/SetCenter.vue';
import OrIcon from 'vue-material-design-icons/SetAll.vue';
import SearchIcon from 'vue-material-design-icons/Magnify.vue';
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js';
import NcSelect from '@nextcloud/vue/dist/Components/NcSelect.js';
import NcTextField from '@nextcloud/vue/dist/Components/NcTextField.js';
import { computed, inject, ref, watch } from 'vue';
import ToggleIconButton from 'cookbook/components/Utilities/ToggleIconButton.vue';
import parseSearchString from 'cookbook/js/utils/parseSearchString';
import useRecipeFilterControls from 'cookbook/composables/useRecipeFilterControls';
import FilterType from 'cookbook/js/Enums/FilterType';
import RecipeSortSelect from './RecipeSortSelect.vue';

const Store = inject('Store');
const RecipeFilters = computed(() => Store.state.recipeFilters);

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
    onCategoriesSelectionUpdated,
    onKeywordsSelectionUpdated,
    onSearchInputUpdated,
    submitFilters,
    updateFiltersUI,
} = useRecipeFilterControls(props, Store);

const emittedValue = computed(() => ({
    filters: localFiltersValue.value,
    orderBy: localOrderBy.value,
}));

const lastSearchFieldValue = ref(searchTerm.value);

const isFormDirty = computed(() => {
    // Compares local filters to store filter to determine if something has changed locally in the form by the user

    // Early return if local and store filter don't have same amount of filters
    if (RecipeFilters.value.length !== localFiltersValue.value.length) {
        return true;
    }

    // Create copy of localFilters
    const localFilters = [...localFiltersValue.value];

    for (const filter of RecipeFilters.value) {
        let filterNotFound = true;
        for (let i = 0; i < localFilters.length && filterNotFound; i++) {
            const filter2 = localFilters[i];

            if (filter.equals(filter2)) {
                // Remove filter2 from list, since it has already been compared
                localFilters.splice(i, 1);
                filterNotFound = false;
            }
        }
        if (filterNotFound) return true;
    }
    return false;
});

function submit() {
    emit('input', emittedValue.value);
    lastSearchFieldValue.value = searchTerm.value;
    submitFilters();
    emit('close');
}

function submitOrderBy() {
    emit('input', emittedValue.value);
}

function clearSearchTerm() {
    searchTerm.value = '';
    Store.dispatch('setRecipeFilters', parseSearchString(searchTerm.value));
}

function clearFilters() {
    selectedCategories.value = [];
    selectedKeywords.value = [];
    clearSearchTerm();
    submit();
}

// function onSearchFieldUpdate() {
//     // Update list of filters and filter inputs accordingly
//     // localFiltersValue.value = parseSearchString(searchTerm.value);
//     // Store.dispatch('setRecipeFilters', parseSearchString(searchTerm.value));
// }

function onTextInputKeyUp(evt) {
    if (evt.key === 'Enter') {
        submit();
    }
}

// Update local filters and search input when filters are updated globally.
watch(
    () => Store.state.recipeFilters,
    (flt) => {
        localFiltersValue.value = flt;
        updateFiltersUI(flt);
    },
);
</script>

<style lang="scss" scoped>
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
    margin-bottom: 1rem;

    .form-group {
        min-width: 150px;
        margin: 0.25rem 0;

        .input-field {
            margin-top: 0;
        }
    }
}

.input--with-operator {
    margin-inline-end: 0.25rem;
}
</style>
