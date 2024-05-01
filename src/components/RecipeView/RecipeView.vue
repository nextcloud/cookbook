<template>
    <div class="wrapper flex justify-center" ref="recipeViewElement">
        <div v-if="isLoading || store.loadingRecipe" class="wrapper-inner">
            <RecipeViewLoadingSkeleton :delay="800" />
        </div>
        <div v-else-if="loadingFailed">
            <NcEmptyContent class="p-8">
                <template #icon>
                    <NoRecipeIcon />
                </template>
                <template #name>
                    <h1 class="empty-content__name">
                        {{ t('cookbook', 'Recipe not found') }}
                    </h1>
                </template>
                <template #description>
                    {{
                        t(
                            'cookbook',
                            'Either the recipe is unknown or loading has failed.',
                        )
                    }}
                </template>
            </NcEmptyContent>
        </div>
        <div v-else class="wrapper-inner">
            <div v-if="$store.state.recipe" class="relative w-full">
                <div
                    v-if="$store.state.recipe.image"
                    class="w-full md:max-w-full self-md-stretch relative"
                >
                    <RecipeImages
                        v-if="$store.state.recipe.image"
                        :images="[
                            $store.state.recipe.imageUrl[0],
                            $store.state.recipe.imageUrl[0],
                            $store.state.recipe.imageUrl[0],
                        ]"
                        :thumbnails="[
                            $store.state.recipe.imageUrl[0],
                            $store.state.recipe.imageUrl[0],
                            $store.state.recipe.imageUrl[0],
                        ]"
                        :is-printed="$store.state.recipe.printImage"
                        :recipe-name="$store.state.recipe.name"
                    />
                </div>
                <div class="title-container absolute">
                    <h2 class="heading m-0">{{ $store.state.recipe.name }}</h2>
                </div>
            </div>

            <div v-if="$store.state.recipe" class="header w-full relative">
                <div class="meta w-full md:max-w-full">
                    <div class="details">
                        <div
                            v-if="recipe.keywords && recipe.keywords.length > 0"
                            class="section"
                        >
                            <RecipeKeywords :keywords="recipe.keywords" />
                        </div>

                        <VueShowdown
                            :markdown="parsedDescription"
                            class="markdown-description section"
                        />
                        <p v-if="$store.state.recipe.url?.[0]" class="section">
                            <span class="label"
                                >{{ t('cookbook', 'Source') }}: </span
                            ><a
                                target="_blank"
                                :href="$store.state.recipe.url"
                                class="source-url"
                                >{{ $store.state.recipe.url?.[0] }}</a
                            >
                        </p>
                    </div>
                    <div
                        class="flex flex-row flex-wrap gap-4 gap-x-lg-16 gap-y-lg-8 justify-center align-items-center mt-6"
                    >
                        <div
                            v-if="$store.state.recipe.recipeYield != null"
                            class="d-flex flex-col align-items-center mt-2"
                        >
                            <h4>
                                {{ t('cookbook', 'Servings') }}
                            </h4>
                            <RecipeYield
                                v-model="recipeYield"
                                :original-yield="
                                    $store.state.recipe.recipeYield
                                "
                            />
                        </div>
                        <RecipeTimes
                            :cook-time-visible="
                                visibleInfoBlocks['cooking-time']
                            "
                            :timer-cook="recipe.timerCook"
                            :prep-time-visible="
                                visibleInfoBlocks['preparation-time']
                            "
                            :timer-prep="recipe.timerPrep"
                            :total-time-visible="
                                visibleInfoBlocks['total-time']
                            "
                            :timer-total="recipe.timerTotal"
                        />
                    </div>
                </div>

                <div
                    v-if="recipe.ingredients.length"
                    class="supplies w-full md:max-w-full"
                >
                    <section class="ingredients">
                        <h3
                            v-if="recipe.ingredients.length"
                            class="section-title"
                        >
                            <span>{{ t('cookbook', 'Ingredients') }}</span>
                            <span class="inline-flex h-0 align-items-center">
                                <NcButton
                                    v-if="recipe.ingredients.length"
                                    class="copy-ingredients print-hidden"
                                    :type="'tertiary'"
                                    aria-label="t('cookbook', 'Copy ingredients to the clipboard')"
                                    :title="t('cookbook', 'Copy ingredients')"
                                    @click="copyIngredientsToClipboard"
                                >
                                    <template #icon>
                                        <ContentCopyIcon :size="20" />
                                    </template>
                                </NcButton>
                            </span>
                        </h3>

                        <RecipeIngredients
                            ref="recipeIngredients"
                            :ingredients="recipe.ingredients"
                            :supplies="recipe.supply"
                            :current-yield="recipeYield"
                            :original-yield="store.state.recipe.recipeYield"
                        />
                    </section>
                    <section
                        v-if="
                            visibleInfoBlocks.tools &&
                            recipe?.tools &&
                            recipe.tools.length > 0
                        "
                        class="tools"
                    >
                        <h3>
                            {{ t('cookbook', 'Tools') }}
                        </h3>

                        <ul v-if="recipe.tools.length > 0" class="tools mb-4">
                            <RecipeInstructionsTool
                                v-for="(tool, idx) in recipe.tools"
                                :key="`tool-${idx}`"
                                :tool="tool"
                            />
                        </ul>
                    </section>
                </div>
            </div>

            <div v-if="$store.state.recipe" class="content">
                <main v-if="recipe.instructions.length">
                    <h3>{{ t('cookbook', 'Instructions') }}</h3>
                    <RecipeInstructions :instructions="recipe.instructions" />
                </main>
            </div>
        </div>
        <!-- RecipeView container -->
    </div>
</template>

<script setup>
import {
    computed,
    getCurrentInstance,
    inject,
    onMounted,
    provide,
    ref,
    watch,
} from 'vue';
import { useRoute } from 'vue-router/composables';

import NcButton from '@nextcloud/vue/dist/Components/NcButton.js';
import NcEmptyContent from '@nextcloud/vue/dist/Components/NcEmptyContent.js';
import ContentCopyIcon from 'icons/ContentCopy.vue';
import NoRecipeIcon from 'icons/FoodOff.vue';
import helpers from 'cookbook/js/helper';
import normalizeMarkdown from 'cookbook/js/title-rename';
import RecipeInstructionsTool from 'cookbook/components/RecipeView/Instructions/RecipeInstructionsTool.vue';
import emitter from 'cookbook/bus';
import { useStore } from 'cookbook/store';
import { parseDateTime } from 'cookbook/composables/dateTimeHandling';

import RecipeViewLoadingSkeleton from './RecipeViewLoadingSkeleton.vue';
import RecipeImages from './Images/RecipeImages.vue';
import RecipeIngredients from './Ingredients/RecipeIngredients.vue';
import RecipeInstructions from './Instructions/RecipeInstructions.vue';
import RecipeKeywords from './RecipeKeywords.vue';
import RecipeTimes from './Timers/RecipeTimes.vue';
import RecipeYield from './RecipeYield.vue';
import { findParentByClass } from 'cookbook/js/utils/domUtils';

const route = useRoute();
const store = useStore();
const log = getCurrentInstance().proxy.$log;

// DI
const RecipeRepository = inject('RecipeRepository');

const recipeViewElement = ref(null);

/**
 * The container element of the recipe view.
 * @type {import('vue').Ref<HTMLElement | null>}
 */
const recipeViewContainer = ref(null);
onMounted(() => {
    const visibleArea = findParentByClass(
        recipeViewElement.value,
        'splitpanes__pane-details',
    );
    if (visibleArea) {
        recipeViewContainer.value = visibleArea;
    } else {
        log.error('Could not find visible-area element for recipe view');
    }
});
provide('RecipeViewContainer', recipeViewContainer);

const props = defineProps({
    id: {
        type: Number,
        default: null,
        required: true,
    },
});

/**
 * @type {import('vue').Ref<boolean>}
 */
const isLoading = ref(false);
/**
 * True, if fetching the recipe data from the server failed.
 * @type {import('vue').Ref<boolean>}
 */
const loadingFailed = ref(false);
/**
 * @type {import('vue').Ref<string>}
 */
const parsedDescription = ref('');
/**
 * @type {import('vue').Ref<number>}
 */
const recipeYield = ref(0);
/**
 * Reference to the vue component showing the recipe ingredients.
 * @type {import('vue').Ref<RecipeIngredients>}
 */
const recipeIngredients = ref(null);

// ===================
// Computed properties
// ===================
const recipe = computed(() => {
    const tmpRecipe = {
        description: '',
        ingredients: [],
        instructions: [],
        keywords: [],
        timerCook: null,
        timerPrep: null,
        timerTotal: null,
        supply: [],
        tools: [],
        dateCreated: null,
        dateModified: null,
        nutrition: null,
    };

    if (store.state.recipe === null) {
        log.debug('Recipe in store is null');
        return tmpRecipe;
    }

    if (store.state.recipe.description) {
        tmpRecipe.description = helpers.escapeHTML(
            store.state.recipe.description,
        );
    }

    if (store.state.recipe.supply) {
        tmpRecipe.supply = Object.values(store.state.recipe.supply);
    }

    if (store.state.recipe.recipeIngredient) {
        tmpRecipe.ingredients = Object.values(
            store.state.recipe.recipeIngredient,
        ).map((i) => helpers.escapeHTML(i));
    }

    if (store.state.recipe.recipeInstructions) {
        tmpRecipe.instructions = Object.values(
            store.state.recipe.recipeInstructions,
        );
        // .map((i) => helpers.escapeHTML(i.text));
    }

    tmpRecipe.keywords = store.state.recipe.keywords;

    if (store.state.recipe.cookTime) {
        const cookT = store.state.recipe.cookTime.match(
            /PT(\d+?)H(\d+?)M(\d+?)S/,
        );
        const hh = parseInt(cookT[1], 10);
        const mm = parseInt(cookT[2], 10);
        const ss = parseInt(cookT[3], 10);
        if (hh > 0 || mm > 0 || ss > 0) {
            tmpRecipe.timerCook = { hours: hh, minutes: mm, seconds: ss };
        }
    }

    if (store.state.recipe.prepTime) {
        const prepT = store.state.recipe.prepTime.match(
            /PT(\d+?)H(\d+?)M(\d+?)S/,
        );
        const hh = parseInt(prepT[1], 10);
        const mm = parseInt(prepT[2], 10);
        const ss = parseInt(prepT[3], 10);
        if (hh > 0 || mm > 0 || ss > 0) {
            tmpRecipe.timerPrep = { hours: hh, minutes: mm, seconds: ss };
        }
    }

    if (store.state.recipe.totalTime) {
        const totalT = store.state.recipe.totalTime.match(
            /PT(\d+?)H(\d+?)M(\d+?)S/,
        );
        const hh = parseInt(totalT[1], 10);
        const mm = parseInt(totalT[2], 10);
        const ss = parseInt(totalT[3], 10);
        if (hh > 0 || mm > 0 || ss > 0) {
            tmpRecipe.timerTotal = { hours: hh, minutes: mm, seconds: ss };
        }
    }

    tmpRecipe.tools = store.state.recipe.tool;

    if (store.state.recipe.dateCreated) {
        const date = parseDateTime(store.state.recipe.dateCreated);
        tmpRecipe.dateCreated =
            date != null ? date.format('L, LT').toString() : null;
    }

    if (store.state.recipe.dateModified) {
        const date = parseDateTime(store.state.recipe.dateModified);
        tmpRecipe.dateModified =
            date != null ? date.format('L, LT').toString() : null;
    }

    tmpRecipe.nutrition = store.state.recipe.nutrition;

    return tmpRecipe;
});

const visibleInfoBlocks = computed(
    () => store.state.config?.visibleInfoBlocks ?? {},
);

// ===================
// Methods
// ===================

async function setup() {
    isLoading.value = true;
    loadingFailed.value = false;

    // Make the control row show that a recipe is loading
    if (!store.state.recipe) {
        store.dispatch('setLoadingRecipe', { recipe: -1 });

        // Make the control row show that the recipe is reloading
    } else if (store.state.recipe.id === parseInt(route.params.id, 10)) {
        store.dispatch('setReloadingRecipe', {
            recipe: route.params.id,
        });

        // Make the control row show that a new recipe is loading
    } else {
        store.dispatch('setLoadingRecipe', {
            recipe: route.params.id,
        });
    }

    try {
        // const tmpRecipe = await api.recipes.get(route.params.id);
        const tmpRecipe = await RecipeRepository.getRecipeById(props.id);

        // Store recipe data in vuex
        store.dispatch('setRecipe', { recipe: tmpRecipe });

        // Always set the active page last!
        store.dispatch('setPage', { page: 'recipe' });
    } catch (ex) {
        loadingFailed.value = true;

        if (store.state.loadingRecipe) {
            // Reset loading recipe
            store.dispatch('setLoadingRecipe', { recipe: 0 });
        }

        if (store.state.reloadingRecipe) {
            // Reset reloading recipe
            store.dispatch('setReloadingRecipe', { recipe: 0 });
        }

        store.dispatch('setPage', { page: 'recipe' });
    } finally {
        isLoading.value = false;
    }
    recipeYield.value = store.state.recipe?.recipeYield;
}
function copyIngredientsToClipboard() {
    recipeIngredients.value.copyIngredientsToClipboard();
}

// ===================
// Watchers
// ===================

watch(
    () => recipe.value,
    (r) => {
        log.debug('Recipe has been updated');
        if (r) {
            log.debug('Recipe', r);

            if (r.description) {
                parsedDescription.value = t('cookbook', 'Loading…');
                parsedDescription.value = r.description;
                normalizeMarkdown(r.description).then((x) => {
                    parsedDescription.value = x;
                });
            } else {
                parsedDescription.value = '';
            }
        }
    },
);

/**
 * (Re-)Load recipe data when the id has changed.
 */
watch(
    () => props.id,
    (newId, oldId) => {
        if (newId !== oldId) {
            setup();
        }
    },
);

// ===================
// Vue lifecycle
// ===================

/**
 * This is one tricky feature of Vue router. If different paths lead to
 * the same component (such as '/recipe/xxx' and '/recipe/yyy)',
 * the component will not automatically reload. So we have to manually
 * reload the page contents.
 * This can also be used to confirm that the user wants to leave the page
 * if there are unsaved changes.
 */
// onBeforeRouteUpdate((to, from, next) => {
//     // beforeRouteUpdate is called when the static route stays the same
//     next();
//     // Check if we should reload the component content
//     if (helpers.shouldReloadContent(from.fullPath, to.fullPath)) {
//         console.log(
//             `RecipeView: Route has changed to ${to.fullPath}, id is ${props.id}, setup()`,
//         );
//
//         setup();
//     }
// });

onMounted(() => {
    log.info('RecipeView mounted');
    setup();
    // Register data load method hook for access from the controls components
    emitter.off('reloadRecipeView');
    emitter.on('reloadRecipeView', () => {
        setup();
    });
});
</script>

<script>
export default {
    name: 'RecipeView',
};
</script>

<style lang="scss" scoped>
h2 {
    font-size: 2rem;
    font-weight: 250;
    line-height: 2.3rem;

    @media (min-width: 1200px) {
        font-size: 2rem;
        font-weight: 250;
        line-height: 2rem;
    }
}

h3 {
    margin: 12px 0 0.6em;
    font-size: 1.5rem;
    font-weight: 200;
    line-height: 1.8rem;

    @media (min-width: 1200px) {
        font-size: 1.4rem;
        font-weight: 250;
        line-height: 1.7rem;
    }
}

:deep(h4) {
    padding: 0.5rem 0.5rem 0.2rem;
    color: var(--color-text-lighter);
    font-size: 1.1rem;
    font-weight: 200;
}

.wrapper {
    width: 100%;
    margin-bottom: 30vh;

    /* Limit max recipe width on very large screens to maintain readability */
    @media (min-width: 1280px) {
        padding: 0 calc((100cqw - 1280px) * 0.1);
    }

    .wrapper-inner {
        display: flex;
        width: 100%;
        max-width: 1600px;
        flex-direction: column;
    }
}

:deep(.print-only) {
    display: none;
}

.title-container {
    bottom: 15px;
    left: 0;
    padding: 0.25rem 16px 0.3rem;
    background: black;
    filter: var(--background-invert-if-dark);
    .heading {
        margin: 0;
        color: white;
    }
}

@media print {
    .header {
        width: 100%;
    }

    .header > .meta {
        margin: 0 10px;
    }

    .header a::after {
        content: '';
    }

    :deep(.print-hidden) {
        display: none !important;
    }

    :deep(.print-only) {
        display: initial !important;
    }
}

.header {
    display: grid;

    padding: 16px;
    gap: 0.75rem;
    grid-template-areas:
        'meta'
        'supplies';

    grid-template-columns: 100%;
    grid-template-rows: auto;

    @media (min-width: 768px) {
        grid-template-areas: 'supplies meta';
        grid-template-columns: minmax(200px, 1fr) minmax(300px, 2fr);
    }
}

.copy-ingredients {
    float: right;
}

@media print {
    .ingredient-highlighted {
        font-style: initial;
    }
}

.description {
    font-style: italic;
    white-space: pre-line;
}

.meta {
    grid-area: meta;

    .details {
        .section {
            padding: 0;
            margin: 0.75em 0;

            .label {
                color: var(--color-text-lighter);
                font-weight: lighter;
            }
        }
    }
}

.supplies {
    grid-area: supplies;
}

section {
    margin-bottom: 1rem;
    padding: 0;
}

section::after {
    display: table;
    clear: both;
    content: '';
}

.content {
    width: 100%;
    flex-basis: 100%;
    padding: 1rem;
}

aside {
    flex-basis: 20rem;
    padding-right: 2rem;
}

.content aside {
    width: 30%;
    float: left;
}
@media screen and (max-width: 1199px) {
    .content aside {
        width: 100%;
        float: none;
    }
}

aside ul {
    list-style-type: none;
}

aside ul li {
    margin-bottom: 0.75ex;
    margin-left: 1em;
    line-height: 2.5ex;
}

aside ul li span,
aside ul li input[type='checkbox'] {
    display: inline-block;
    width: 1rem;
    height: auto;
    padding: 0;
    margin: 0 0.5rem 0 0;
    line-height: 1rem;
    vertical-align: middle;
}

.markdown-description :deep(ol > li) {
    list-style-type: numbered;
}

.markdown-description :deep(ul > li) {
    list-style-type: disc;
}

.markdown-description :deep(ol > li),
.markdown-description :deep(ul > li) {
    margin-left: 20px;
}

.markdown-description :deep(a) {
    text-decoration: underline;
}

.section-title {
    display: flex;
    align-items: center;
}

.tools {
    margin-bottom: 0;
    grid-row: 2/3;
}
</style>

<style>
.recipe-reference-inline {
    color: var(--color-text-maxcontrast);
    font-weight: 450;
}

.recipe-reference-inline:hover {
    color: var(--color-main-text);
}
</style>
