<template>
    <div>
        <div v-if="loading" class="loading-indicator">
            <LoadingIndicator :delay="800" :size="40" />
        </div>
        <div v-else>
            <div v-if="recipeObjects.length === 0">
                <EmptyList />
            </div>
            <RecipeListKeywordCloud
                v-if="showTagCloudInRecipeList"
                v-model="keywordFilter"
                :keywords="rawKeywords"
                :filtered-recipes="filteredRecipes"
            />
            <div id="recipes-submenu" class="recipes-submenu-container">
                <NcSelect
                    v-if="recipes.length > 0"
                    v-model="orderBy"
                    class="recipes-sorting-dropdown"
                    :clearable="false"
                    :multiple="false"
                    :searchable="false"
                    :placeholder="t('cookbook', 'Select order')"
                    :options="recipeOrderingOptions"
                >
                    <template #option="option">
                        <div class="ordering-selection-entry">
                            <TriangleSmallUpIcon v-if="option.iconUp" :size="20" />
                            <TriangleSmallDownIcon v-if="! option.iconUp" :size="20" />
                            <span class="option__title">{{
                                option.label
                            }}</span>
                        </div>
                    </template>
                </NcSelect>
            </div>
            <ul class="recipes">
                <li
                    v-for="recipeObj in recipeObjects"
                    v-show="recipeObj.show"
                    :key="recipeObj.recipe.recipe_id"
                >
                    <RecipeCard :recipe="recipeObj.recipe" />
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import TriangleSmallUpIcon from 'vue-material-design-icons/TriangleSmallUp.vue';
import TriangleSmallDownIcon from 'vue-material-design-icons/TriangleSmallDown.vue';

import NcSelect from '@nextcloud/vue/dist/Components/NcSelect.js';
import { useStore } from '../../store';
import { normalize as normalizeString } from '../../js/string-utils';
import EmptyList from './EmptyList.vue';
import LoadingIndicator from '../Utilities/LoadingIndicator.vue';
import RecipeCard from './RecipeCard.vue';
import RecipeListKeywordCloud from './RecipeListKeywordCloud.vue';

const store = useStore();

const props = defineProps({
    loading: {
        type: Boolean,
        default: false,
    },
    recipes: {
        type: Array,
        default: () => [],
        required: true,
    },
});

// todo: Find out why this was here
/**
 * String-based filters applied to the list
 * @type {import('vue').Ref<string>}
 */
// const filters = ref('');
/**
 * All keywords to filter the recipes for (conjunctively)
 * @type {import('vue').Ref<Array>}
 */
const keywordFilter = ref([]);
/**
 * @type {import('vue').Ref<Array>}
 */
const recipeOrderingOptions = ref([
    {
        label: t('cookbook', 'Name'),
        icon: 'icon-triangle-n',
        iconUp: true,
        recipeProperty: 'name',
        order: 'ascending',
    },
    {
        label: t('cookbook', 'Name'),
        icon: 'icon-triangle-s',
        iconUp: false,
        recipeProperty: 'name',
        order: 'descending',
    },
    {
        label: t('cookbook', 'Creation date'),
        icon: 'icon-triangle-n',
        iconUp: true,
        recipeProperty: 'dateCreated',
        order: 'ascending',
    },
    {
        label: t('cookbook', 'Creation date'),
        icon: 'icon-triangle-s',
        iconUp: false,
        recipeProperty: 'dateCreated',
        order: 'descending',
    },
    {
        label: t('cookbook', 'Modification date'),
        icon: 'icon-triangle-n',
        iconUp: true,
        recipeProperty: 'dateModified',
        order: 'ascending',
    },
    {
        label: t('cookbook', 'Modification date'),
        icon: 'icon-triangle-s',
        iconUp: false,
        recipeProperty: 'dateModified',
        order: 'descending',
    },
]);
const orderBy = ref(recipeOrderingOptions.value[0]);

onMounted(() => {
    store.dispatch('clearRecipeFilters');
});

// ===================
// Methods
// ===================

/* Sort recipes according to the property of the recipe ascending or
 * descending
 */
const sortRecipes = (recipes, recipeProperty, order) => {
    const rec = JSON.parse(JSON.stringify(recipes));
    return rec.sort((r1, r2) => {
        if (order !== 'ascending' && order !== 'descending') return 0;
        if (order === 'ascending') {
            if (
                recipeProperty === 'dateCreated' ||
                recipeProperty === 'dateModified'
            ) {
                return (
                    new Date(r1[recipeProperty]) - new Date(r2[recipeProperty])
                );
            }
            if (recipeProperty === 'name') {
                return r1[recipeProperty].localeCompare(r2[recipeProperty]);
            }
            if (!Number.isNaN(r1[recipeProperty] - r2[recipeProperty])) {
                return r1[recipeProperty] - r2[recipeProperty];
            }
            return 0;
        }

        if (
            recipeProperty === 'dateCreated' ||
            recipeProperty === 'dateModified'
        ) {
            return new Date(r2[recipeProperty]) - new Date(r1[recipeProperty]);
        }
        if (recipeProperty === 'name') {
            return r2[recipeProperty].localeCompare(r1[recipeProperty]);
        }
        if (!Number.isNaN(r2[recipeProperty] - r1[recipeProperty])) {
            return r2[recipeProperty] - r1[recipeProperty];
        }
        return 0;
    });
};

// ===================
// Computed properties
// ===================
/**
 * An array of all keywords in all recipes. These are neither sorted nor unique
 */
const rawKeywords = computed(() => {
    const keywordArray = props.recipes.map((r) => {
        if (!('keywords' in r)) {
            return [];
        }
        if (r.keywords != null) {
            return r.keywords.split(',');
        }
        return [];
    });
    return [].concat(...keywordArray);
});

/**
 * An array of all recipes that are part in all filtered keywords
 * @returns {Array}
 */
const recipesFilteredByKeywords = computed(() =>
    props.recipes.filter((r) => {
        if (keywordFilter.value.length === 0) {
            // No filtering, keep all
            return true;
        }

        if (r.keywords === null) {
            return false;
        }

        function keywordInRecipePresent(kw, r2) {
            if (!r2.keywords) {
                return false;
            }
            const keywords = r2.keywords.split(',');
            return keywords.includes(kw);
        }

        return keywordFilter.value
            .map((kw) => keywordInRecipePresent(kw, r))
            .reduce((l, rec) => l && rec);
    }),
);

/**
 * An array of the finally filtered recipes, that is both filtered for keywords as well as string-based name filtering
 */
const filteredRecipes = computed(() => {
    let ret = recipesFilteredByKeywords.value;
    if (store.state.recipeFilters) {
        ret = ret.filter((r) => {
            const normalizedRecipeName = normalizeString(r.name);
            const normalizedFilter = normalizeString(store.state.recipeFilters);
            return normalizedRecipeName.includes(normalizedFilter);
        });
    }
    return ret;
});

// Recipes ordered ascending by name
const recipesNameAsc = computed(() =>
    sortRecipes(props.recipes, 'name', 'ascending'),
);

// Recipes ordered descending by name
const recipesNameDesc = computed(() =>
    sortRecipes(props.recipes, 'name', 'descending'),
);

// Recipes ordered ascending by creation date
const recipesDateCreatedAsc = computed(() =>
    sortRecipes(props.recipes, 'dateCreated', 'ascending'),
);

// Recipes ordered descending by creation date
const recipesDateCreatedDesc = computed(() =>
    sortRecipes(props.recipes, 'dateCreated', 'descending'),
);

// Recipes ordered ascending by modification date
const recipesDateModifiedAsc = computed(() =>
    sortRecipes(props.recipes, 'dateModified', 'ascending'),
);

// Recipes ordered descending by modification date
const recipesDateModifiedDesc = computed(() =>
    sortRecipes(props.recipes, 'dateModified', 'descending'),
);

// An array of recipe objects of all recipes with links to the recipes and a property if the recipe is to be shown
const recipeObjects = computed(() => {
    function makeObject(rec) {
        return {
            recipe: rec,
            show: filteredRecipes.value
                .map((r) => r.recipe_id)
                .includes(rec.recipe_id),
        };
    }

    if (
        orderBy.value === null ||
        (orderBy.value.order !== 'ascending' &&
            orderBy.value.order !== 'descending')
    ) {
        return props.recipes.map(makeObject);
    }
    if (orderBy.value.recipeProperty === 'dateCreated') {
        if (orderBy.value.order === 'ascending') {
            return recipesDateCreatedAsc.value.map(makeObject);
        }
        return recipesDateCreatedDesc.value.map(makeObject);
    }
    if (orderBy.value.recipeProperty === 'dateModified') {
        if (orderBy.value.order === 'ascending') {
            return recipesDateModifiedAsc.value.map(makeObject);
        }
        return recipesDateModifiedDesc.value.map(makeObject);
    }
    if (orderBy.value.recipeProperty === 'name') {
        if (orderBy.value.order === 'ascending') {
            return recipesNameAsc.value.map(makeObject);
        }
        return recipesNameDesc.value.map(makeObject);
    }
    return props.recipes.map(makeObject);
});

const showTagCloudInRecipeList = computed(
    () => store.state.localSettings.showTagCloudInRecipeList,
);
</script>

<script>
export default {
    name: 'RecipeList',
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
.loading-indicator {
    display: flex;
    justify-content: center;
    padding: 3rem 0;
}

.recipes-submenu-container {
    padding-left: 16px;
    margin-bottom: 0.75ex;
}

.ordering-item-icon {
    margin-right: 0.5em;
}

.recipes {
    display: flex;
    width: 100%;
    flex-direction: row;
    flex-wrap: wrap;
}

.p-4 {
    padding: 1.5rem !important;
}

.ordering-selection-entry {
    display: flex;
    align-items: baseline;
}
</style>
