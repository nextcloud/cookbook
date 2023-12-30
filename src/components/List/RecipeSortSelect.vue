<template>
    <NcSelect
        v-model="orderBy"
        class="recipes-sorting-dropdown mr-4"
        :clearable="false"
        :multiple="false"
        :searchable="false"
        :placeholder="t('cookbook', 'Select order')"
        :options="recipeOrderingOptions"
        @input="handleInput"
    >
        <template #option="option">
            <div class="ordering-selection-entry">
                <TriangleSmallUpIcon v-if="option.iconUp" :size="20" />
                <TriangleSmallDownIcon v-if="!option.iconUp" :size="20" />
                <span class="option__title">{{ option.label }}</span>
            </div>
        </template>
    </NcSelect>
</template>

<script setup>
import NcSelect from '@nextcloud/vue/dist/Components/NcSelect.js';
import TriangleSmallDownIcon from 'vue-material-design-icons/TriangleSmallDown.vue';
import TriangleSmallUpIcon from 'vue-material-design-icons/TriangleSmallUp.vue';
import { ref } from 'vue';

const emit = defineEmits(['input']);

/**
 * @type {import('vue').Ref<Array>}
 */
const recipeOrderingOptions = ref([
    {
        label: t('cookbook', 'Name'),
        iconUp: true,
        recipeProperty: 'name',
        order: 'ascending',
    },
    {
        label: t('cookbook', 'Name'),
        iconUp: false,
        recipeProperty: 'name',
        order: 'descending',
    },
    {
        label: t('cookbook', 'Creation date'),
        iconUp: true,
        recipeProperty: 'dateCreated',
        order: 'ascending',
    },
    {
        label: t('cookbook', 'Creation date'),
        iconUp: false,
        recipeProperty: 'dateCreated',
        order: 'descending',
    },
    {
        label: t('cookbook', 'Modification date'),
        iconUp: true,
        recipeProperty: 'dateModified',
        order: 'ascending',
    },
    {
        label: t('cookbook', 'Modification date'),
        iconUp: false,
        recipeProperty: 'dateModified',
        order: 'descending',
    },
]);

defineProps({
    value: {
        type: Object,
        default: () => ({
            label: t('cookbook', 'Name'),
            iconUp: true,
            recipeProperty: 'name',
            order: 'ascending',
        }),
        required: true,
    },
});

const orderBy = ref(recipeOrderingOptions.value[0]);

function handleInput() {
    emit('input', orderBy.value);
}
</script>

<style scoped>
.ordering-selection-entry {
    display: flex;
    align-items: baseline;
}
</style>
