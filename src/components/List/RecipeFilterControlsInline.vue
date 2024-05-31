<template>
    <div class="container">
        <div class="form-group">
            <RecipeSortSelect
                v-model="localOrderBy"
                aria-label="t('cookbook', 'Show settings for filtering recipe list')"
                :label="t('cookbook', 'Order')"
                class="mr-4"
                :title="t('cookbook', 'Show filter settings')"
                @input="submitOrderBy"
            />
        </div>
        <div class="form-group">
            <NcTextField
                class="input"
                :value.sync="searchTerm"
                :label="t('cookbook', 'Filter name')"
                :placeholder="t('cookbook', 'Search term')"
                :aria-placeholder="t('cookbook', 'Search term')"
                trailing-button-icon="close"
                :show-trailing-button="searchTerm !== ''"
                @trailing-button-click="clearSearchTerm"
                @input="submitNameFilter"
                ><SearchIcon :size="20"
            /></NcTextField>
        </div>

        <div v-if="!hiddenSections['categories']" class="form-group">
            <div class="d-flex flex-row align-items-center">
                <NcSelect
                    v-model="selectedCategories"
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
                    @input="submitFilters"
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
                    @update="submitFilters"
                />
            </div>
        </div>

        <div class="form-group d-flex flex-row">
            <div class="d-flex flex-row align-items-center">
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
                    :label-outside="true"
                    style="max-width: 25%"
                    @input="submitFilters"
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
                    @update="submitFilters"
                />
            </div>
            <!--        Keep button together in a line with the last input so it does not get lonely -->
            <NcButton type="tertiary" @click="clearFilters">
                {{
                    // TRANSLATORS Button text for applying recipe-filter values
                    t('cookbook', 'Clear')
                }}
            </NcButton>
        </div>
    </div>
</template>

<script setup>
import AndIcon from 'vue-material-design-icons/SetCenter.vue';
import OrIcon from 'vue-material-design-icons/SetAll.vue';
import SearchIcon from 'vue-material-design-icons/Magnify.vue';
import { NcButton, NcSelect, NcTextField } from '@nextcloud/vue';
import { computed, defineEmits, defineProps, ref } from 'vue';
import useRecipeFilterControls from '../../composables/useRecipeFilterControls';
import RecipeSortSelect from './RecipeSortSelect.vue';
import ToggleIconButton from '../Utilities/ToggleIconButton.vue';

const emit = defineEmits(['close', 'input']);

const props = defineProps({
    value: {
        type: Object,
        default: () => ({
            filters: { categories: null, keywords: null },
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
     * List of sections that should be hidden from the filters list, e.g., `['categories', 'keywords']`
     */
    preappliedFilters: { type: Array, default: () => [] },
    isLoading: { type: Boolean, default: false },
    isVisible: { type: Boolean, default: false },
    recipes: { type: Array, default: () => [] },
});

const localOrderBy = ref(props.orderBy);

const {
    uniqueCategories,
    selectedCategories,
    uniqueKeywords,
    selectedKeywords,
    hiddenSections,
    searchTerm,
    localFiltersValue,
    categoriesOperatorToggleValue,
    keywordsOperatorToggleValue,
    store,
} = useRecipeFilterControls(props);

const emittedValue = computed(() => ({
    filters: localFiltersValue.value,
    orderBy: localOrderBy.value,
}));

function submitFilters() {
    emit('input', emittedValue.value);
    store.dispatch('setRecipeFilters', searchTerm.value);
    emit('close');
}

function submitOrderBy() {
    emit('input', emittedValue.value);
}

function clearSearchTerm() {
    searchTerm.value = '';
    store.dispatch('setRecipeFilters', searchTerm.value);
}

function clearFilters() {
    selectedCategories.value = [];
    selectedKeywords.value = [];
    clearSearchTerm();
    submitFilters();
}

function submitNameFilter() {
    store.dispatch('setRecipeFilters', searchTerm.value);
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
