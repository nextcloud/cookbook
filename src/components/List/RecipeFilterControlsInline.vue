<template>
    <div class="container">
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
            <NcSelect
                class="input"
                v-model="selectedCategories"
                input-id="categoriesFilterInput"
                :options="uniqueCategories"
                :loading="isLoading"
                :close-on-select="false"
                :multiple="true"
                :no-wrap="true"
                :placeholder="t('cookbook', 'All categories')"
                :aria-label="t('cookbook', 'Categories')"
                :aria-placeholder="t('cookbook', 'All categories')"
                @input="submitFilters"
            />
        </div>

        <div class="form-group">
            <NcSelect
                class="input"
                :noWrap="false"
                v-model="selectedKeywords"
                input-id="keywordsFilterInput"
                :options="uniqueKeywords"
                :loading="isLoading"
                :close-on-select="false"
                :multiple="true"
                :no-wrap="true"
                :placeholder="t('cookbook', 'All keywords')"
                :aria-label="t('cookbook', 'Keywords')"
                :aria-placeholder="t('cookbook', 'All keywords')"
                @input="submitFilters"
                style="max-width: 25%"
            />
        </div>
        <div class="form-group">
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
import SearchIcon from 'vue-material-design-icons/Magnify.vue';
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js';
import NcSelect from '@nextcloud/vue/dist/Components/NcSelect.js';
import NcTextField from '@nextcloud/vue/dist/Components/NcTextField.js';
import { defineEmits, defineProps } from 'vue';
import useRecipeFilterControls from '../../composables/useRecipeFilterControls';

const emit = defineEmits(['close', 'input']);

const props = defineProps({
    value: {
        type: Object,
        default() {
            return { categories: [], keywords: [] };
        },
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

const {
    uniqueCategories,
    selectedCategories,
    uniqueKeywords,
    selectedKeywords,
    hiddenSections,
    searchTerm,
    localValue,
    store,
} = useRecipeFilterControls(props);

function submitFilters() {
    emit('input', localValue.value);
    store.dispatch('setRecipeFilters', searchTerm.value);
    emit('close');
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
    align-items: center;
    padding: 16px;

    .form-group {
        margin-right: 0.75rem;

        .input-field {
            margin-top: 0;
        }
    }
}
</style>
