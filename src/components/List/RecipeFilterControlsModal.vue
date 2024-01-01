<template>
    <NcModal v-if="isVisible" @close="closeModal">
        <div class="modal__content">
            <h2>{{ t('cookbook', 'Recipe filters') }}</h2>

            <div class="form-group">
                <NcTextField
                    :value.sync="searchTerm"
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
            </div>

            <div class="form-group">
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
            </div>
            <div class="d-flex flex-row justify-end mt-4">
                <NcButton type="tertiary" @click="clearFilters">
                    {{
                        // TRANSLATORS Button text for applying recipe-filter values
                        t('cookbook', 'Clear')
                    }}
                </NcButton>
                <NcButton
                    type="primary"
                    class="self-end"
                    @click="submitFilters"
                >
                    {{
                        // TRANSLATORS Button text for applying recipe-filter values
                        t('cookbook', 'Apply')
                    }}
                </NcButton>
            </div>
        </div>
    </NcModal>
</template>

<script setup>
import SearchIcon from 'vue-material-design-icons/Magnify.vue';
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js';
import NcModal from '@nextcloud/vue/dist/Components/NcModal.js';
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
    localFiltersValue,
    store,
} = useRecipeFilterControls(props);

function clearSearchTerm() {
    searchTerm.value = '';
    store.dispatch('setRecipeFilters', searchTerm.value);
}

function clearFilters() {
    selectedCategories.value = [];
    selectedKeywords.value = [];
    clearSearchTerm();
}

function closeModal() {
    emit('close');
}

function submitFilters() {
    emit('input', localFiltersValue.value);
    store.dispatch('setRecipeFilters', searchTerm.value);
    emit('close');
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
