<template>
    <NcModal v-if="isVisible" @close="closeModal">
        <div class="modal__content">
            <h2>{{ t('cookbook', 'Recipe filters') }}</h2>

            <div class="form-group">
                <NcSelect
                    v-model="selectedCategories"
                    :options="uniqueCategories"
                    :loading="isLoading"
                    :close-on-select="false"
                    :multiple="true"
                    :no-wrap="true"
                    :placeholder="t('cookbook', 'All categories')"
                    :aria-placeholder="t('cookbook', 'All categories')"
                />
            </div>

            <div class="form-group">
                <NcSelect
                    v-model="selectedKeywords"
                    :options="uniqueKeywords"
                    :loading="isLoading"
                    :close-on-select="false"
                    :multiple="true"
                    :no-wrap="true"
                    :placeholder="t('cookbook', 'All keywords')"
                    :aria-placeholder="t('cookbook', 'All keywords')"
                />
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
import { computed, defineEmits, defineProps, ref } from 'vue';
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js';
import NcModal from '@nextcloud/vue/dist/Components/NcModal.js';
import NcSelect from '@nextcloud/vue/dist/Components/NcSelect.js';

const props = defineProps({
    value: {
        type: Object,
        default() {
            return { categories: [], keywords: [] };
        },
    },
    fieldLabel: { type: String, default: '' },
    isLoading: { type: Boolean, default: false },
    isVisible: { type: Boolean, default: false },
    recipes: { type: Array, default: () => [] },
});

const emit = defineEmits(['close', 'input']);

/**
 * @type {import('vue').Ref<Array>}
 */
const selectedCategories = ref([]);

/**
 * @type {import('vue').Ref<Array>}
 */
const selectedKeywords = ref([]);

const localValue = computed(() => ({
    categories: selectedCategories.value,
    keywords: selectedKeywords.value,
}));

/**
 * An array of all categories in the recipes. These are neither sorted nor unique
 */
const rawCategories = computed(() => {
    const categoriesArray = props.recipes.map((r) => {
        if (!('category' in r)) {
            return [];
        }
        if (r.category != null) {
            return r.category.split(',');
        }
        return [];
    });
    return [].concat(...categoriesArray);
});

/**
 * A unique set of all categories in the recipes.
 */
const uniqueCategories = computed(() => [...new Set(rawCategories.value)]);

/**
 * An array of all keywords in the recipes. These are neither sorted nor unique
 */
const rawKeywords = computed(() => {
    const keywordsArray = props.recipes.map((r) => {
        if (!('keywords' in r)) {
            return [];
        }
        if (r.keywords != null) {
            return r.keywords.split(',');
        }
        return [];
    });
    return [].concat(...keywordsArray);
});

/**
 * A unique set of all keywords in all recipes.
 */
const uniqueKeywords = computed(() => [...new Set(rawKeywords.value)]);

function clearFilters() {
    selectedCategories.value = [];
    selectedKeywords.value = [];
}

function closeModal() {
    emit('close');
}

function submitFilters() {
    emit('input', localValue.value);
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
        margin: calc(var(--default-grid-baseline) * 2) 0;
        display: flex;
        flex-direction: column;
        align-items: flex-start;

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
