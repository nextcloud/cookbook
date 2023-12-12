<template>
    <!-- This component should ideally not have a conflicting name with AppNavigation from the nextcloud/vue package -->
    <NcAppNavigation>
        <router-link :to="'/recipe/create'">
            <NcAppNavigationNew
                class="create"
                :text="t('cookbook', 'Create recipe')"
            >
                <template #icon><plus-icon :size="20" /> </template>
            </NcAppNavigationNew>
        </router-link>

        <template #list>
            <NcActionInput
                class="download"
                :disabled="downloading ? 'disabled' : null"
                :icon="downloading ? 'icon-loading-small' : 'icon-download'"
                @submit="downloadRecipe"
                @update:value="updateUrl"
            >
                {{ t('cookbook', 'Download recipe from URL') }}
            </NcActionInput>

            <NcAppNavigationItem
                :name="t('cookbook', 'All recipes')"
                icon="icon-category-organization"
                :to="'/'"
            >
                <template #counter>
                    <nc-counter-bubble>{{
                        totalRecipeCount
                    }}</nc-counter-bubble>
                </template>
            </NcAppNavigationItem>

            <NcAppNavigationItem
                :name="t('cookbook', 'Uncategorized recipes')"
                icon="icon-category-organization"
                :to="'/category/_/'"
            >
                <template #counter>
                    <nc-counter-bubble>{{ uncatRecipes }}</nc-counter-bubble>
                </template>
            </NcAppNavigationItem>

            <NcAppNavigationCaption
                v-if="loading.categories || categories.length > 0"
                :name="t('cookbook', 'Categories')"
                :loading="loading.categories"
            >
            </NcAppNavigationCaption>

            <NcAppNavigationItem
                v-for="(cat, idx) in categories"
                :key="cat + idx"
                :ref="
                    (el) => {
                        categoryItemElements[idx] = el;
                    }
                "
                :name="cat.name"
                :icon="'icon-category-files'"
                :to="'/category/' + cat.name"
                :editable="true"
                :edit-label="t('cookbook', 'Rename')"
                :edit-placeholder="t('cookbook', 'Enter new category name')"
                @update:open="categoryOpen(idx)"
                @update:name="
                    (val) => {
                        categoryUpdateName(idx, val);
                    }
                "
            >
                <template #counter>
                    <nc-counter-bubble>{{ cat.recipeCount }}</nc-counter-bubble>
                </template>
            </NcAppNavigationItem>
        </template>

        <template #footer>
            <NcAppNavigationNew
                :text="t('cookbook', 'Cookbook settings')"
                :button-class="['create', 'icon-settings']"
                @click="handleOpenSettings"
            />
        </template>
    </NcAppNavigation>
</template>

<script setup>
import {
    computed,
    getCurrentInstance,
    nextTick,
    onMounted,
    ref,
    watch,
} from 'vue';

import { emit } from '@nextcloud/event-bus';
import NcActionInput from '@nextcloud/vue/dist/Components/NcActionInput.js';
import NcAppNavigation from '@nextcloud/vue/dist/Components/NcAppNavigation.js';
import NcAppNavigationCaption from '@nextcloud/vue/dist/Components/NcAppNavigationCaption.js';
import NcAppNavigationItem from '@nextcloud/vue/dist/Components/NcAppNavigationItem.js';
import NcAppNavigationNew from '@nextcloud/vue/dist/Components/NcAppNavigationNew.js';
import NcCounterBubble from '@nextcloud/vue/dist/Components/NcCounterBubble.js';

import PlusIcon from 'icons/Plus.vue';

import api from 'cookbook/js/api-interface';
import helpers from 'cookbook/js/helper';
import { showSimpleAlertModal } from 'cookbook/js/modals';

import emitter from '../bus';
import { SHOW_SETTINGS_EVENT } from '../composables/useSettingsDialog';
import { useStore } from '../store';

const log = getCurrentInstance().proxy.$log;
const store = useStore();

/**
 * References to the DOM elements of the categories in the App navigation.
 * @type {import('vue').Ref<Array.<HTMLElement | null>>}
 */
const categoryItemElements = ref([]);
/**
 * @type {import('vue').Ref<Array>}
 */
const categories = ref([]);
/**
 * @type {import('vue').Ref<boolean>}
 */
const downloading = ref(false);
/**
 * @type {import('vue').Ref<Array>}
 */
const isCategoryUpdating = ref([]);
const loading = ref({ categories: true });
/**
 * @type {import('vue').Ref<number>}
 */
const uncatRecipes = ref(0);
/**
 * @type {import('vue').Ref<string>}
 */
const importUrl = ref('');

// Computed properties
const totalRecipeCount = computed(() => {
    log.debug('Calling totalRecipeCount');
    let total = uncatRecipes.value;
    for (let i = 0; i < categories.value.length; i++) {
        total += categories.value[i].recipeCount;
    }
    return total;
});

// Computed property to watch the Vuex state. If there are more in the
// future, consider using the Vue mapState helper
const refreshRequired = computed(
    () => store.state.appNavigation.refreshRequired,
);

// Methods

/**
 * Opens a category
 */
const openCategory = async (idx) => {
    if (
        !categories.value[idx].recipes.length ||
        categories.value[idx].recipes[0].id
    ) {
        // Recipes have already been loaded
        return;
    }
    const cat = categories.value[idx];
    isCategoryUpdating.value[idx] = true;

    try {
        const response = await api.recipes.allInCategory(cat.name);
        cat.recipes = response.data;
    } catch (e) {
        cat.recipes = [];
        await showSimpleAlertModal(
            // prettier-ignore
            t('cookbook', 'Failed to load category {category} recipes',
                {
                    category: cat.name,
                }
            ),
        );
        if (e && e instanceof Error) {
            throw e;
        }
    } finally {
        isCategoryUpdating.value[idx] = false;
    }
};

/**
 * Updates the name of a category
 */
const categoryUpdateName = async (idx, newName) => {
    if (!categories.value[idx]) {
        return;
    }
    isCategoryUpdating.value[idx] = true;
    const oldName = categories.value[idx].name;

    try {
        await store.dispatch('updateCategoryName', {
            categoryNames: [oldName, newName],
        });
        categories.value[idx].name = newName;
        emitter.emit('categoryRenamed', [newName, oldName]);
    } catch (e) {
        await showSimpleAlertModal(
            // prettier-ignore
            t('cookbook','Failed to update name of category "{category}"',
            {
                category: oldName,
            }),
        );
        if (e && e instanceof Error) {
            throw e;
        }
    } finally {
        isCategoryUpdating.value[idx] = false;
    }
};

const updateUrl = (e) => {
    importUrl.value = e;
};

/**
 * Download and import the recipe at given URL
 */
const downloadRecipe = async () => {
    downloading.value = true;
    try {
        const response = await api.recipes.import(importUrl.value);
        const recipe = response.data;
        downloading.value = false;
        helpers.goTo(`/recipe/${recipe.id}`);
        // Refresh left navigation pane to display changes
        store.dispatch('setAppNavigationRefreshRequired', {
            isRequired: true,
        });
    } catch (e2) {
        downloading.value = false;

        if (e2.response) {
            if (e2.response.status >= 400 && e2.response.status < 500) {
                if (e2.response.status === 409) {
                    // There was a recipe found with the same name

                    await showSimpleAlertModal(e2.response.data.msg);
                } else {
                    await showSimpleAlertModal(e2.response.data);
                }
            } else {
                // eslint-disable-next-line no-console
                console.error(e2);
                await showSimpleAlertModal(
                    // prettier-ignore
                    t('cookbook','The server reported an error. Please check.'),
                );
            }
        } else {
            // eslint-disable-next-line no-console
            console.error(e2);
            await showSimpleAlertModal(
                // prettier-ignore
                t('cookbook', 'Could not query the server. This might be a network problem.'),
            );
        }
    }
};

/**
 * Fetch and display recipe categories
 */
const getCategories = async () => {
    log.debug('Calling getCategories');
    loading.value.categories = true;
    try {
        const response = await api.categories.getAll();
        const json = response.data || [];
        // Reset the old values
        uncatRecipes.value = 0;
        categories.value = [];
        isCategoryUpdating.value = [];

        for (let i = 0; i < json.length; i++) {
            if (json[i].name === '*') {
                uncatRecipes.value = parseInt(json[i].recipe_count, 10);
            } else {
                categories.value.push({
                    name: json[i].name,
                    recipeCount: parseInt(json[i].recipe_count, 10),
                    recipes: [
                        {
                            id: 0,
                            // prettier-ignore
                            name: t('cookbook','Loading category recipes …'),
                        },
                    ],
                });
                isCategoryUpdating.value.push(false);
            }
        }
        await nextTick();

        // Do not await in for loop
        const loadingCategoriesAwaitable = [];

        for (let i = 0; i < categories.value.length; i++) {
            // Reload recipes in open categories
            if (!categoryItemElements[i]) {
                // eslint-disable-next-line no-continue
                continue;
            }
            if (categoryItemElements[i][0].opened) {
                log.info(
                    `Reloading recipes in ${categoryItemElements[i][0].title}`,
                );
                loadingCategoriesAwaitable.push(openCategory(i));
            }
        }
        await Promise.all(loadingCategoriesAwaitable);

        // Refreshing component data has been finished
        store.dispatch('setAppNavigationRefreshRequired', {
            isRequired: false,
        });
    } catch (e) {
        await showSimpleAlertModal(t('cookbook', 'Failed to fetch categories'));
        if (e && e instanceof Error) {
            throw e;
        }
    } finally {
        loading.value.categories = false;
    }
};

const handleOpenSettings = () => {
    emit(SHOW_SETTINGS_EVENT, null);
};

// Watchers
// Register a method hook for navigation refreshing
watch(
    () => refreshRequired.value,
    (newVal, oldVal) => {
        if (newVal !== oldVal && newVal === true) {
            log.debug('Calling getCategories from refreshRequired');
            getCategories();
        }
    },
);

// Vue lifecycle
onMounted(() => {
    log.info('AppNavi mounted');
    getCategories();
});
</script>

<script>
export default {
    name: 'AppNavi',
};
</script>

<style scoped>
@media print {
    * {
        display: none !important;
    }
}
</style>
