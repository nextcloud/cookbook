<template>
    <div class="wrapper flex justify-center">
        <div v-if="isLoading || store.loadingRecipe" class="loading-indicator">
            <LoadingIndicator :size="40" :delay="800" />
        </div>
        <div v-else class="wrapper-inner">
            <div v-if="$store.state.recipe" class="relative w-full">
                <div
                    v-if="$store.state.recipe.image"
                    class="image w-full max-w-md-full self-md-stretch relative"
                >
                    <RecipeImages
                        v-if="$store.state.recipe.image"
                        :image="$store.state.recipe.imageUrl"
                        :is-printed="$store.state.recipe.printImage"
                    />
                </div>
                <div
                    class="title-container absolute"
                    style="
                        bottom: 15px;
                        left: 0;
                        /*background: var(--color-main-text);*/
                        padding: 0.25rem 0.6rem 0.3rem;
                    "
                >
                    <h2 class="heading m-0">{{ $store.state.recipe.name }}</h2>
                </div>
            </div>

            <div
                v-if="$store.state.recipe"
                class="header w-full relative position-md-absolute flex d-md-grid flex-col flex-nowrap gap-4 gap-md-8"
            >
                <div
                    v-if="recipe.ingredients.length"
                    class="w-full max-w-md-full self-md-stretch relative"
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
                            @syntax-validity-changed="
                                (valid) => (isIngredientsSyntaxValid = valid)
                            "
                        />

                        <div
                            v-if="!isIngredientsSyntaxValid"
                            class="ingredient-parsing-error print-hidden"
                        >
                            <hr />
                            <span class="icon-error" />
                            {{
                                // prettier-ignore
                                t("cookbook", "The ingredient cannot be recalculated due to incorrect syntax. Please ensure the syntax follows this format: amount unit ingredient and that a specific number of portions is set for this function to work correctly. Examples: 200 g carrots or 1 pinch of salt.")
                            }}
                        </div>
                    </section>
                </div>

                <div class="meta w-full max-w-md-full self-md-start">
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
                            <strong>{{ t('cookbook', 'Source') }}: </strong
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
            </div>

            <div v-if="$store.state.recipe" class="content">
                <section class="container">
                    <section
                        v-if="
                            visibleInfoBlocks.tools &&
                            recipe.tools &&
                            recipe.tools.length > 0
                        "
                        class="tools"
                    >
                        <h3>
                            {{ t('cookbook', 'Tools') }}
                        </h3>
                        <ul class="pl-2">
                            <RecipeTool
                                v-for="(tool, idx) in recipe.tools"
                                :key="'tool' + idx"
                                :tool="tool"
                            />
                        </ul>
                    </section>

                    <main v-if="recipe.instructions.length">
                        <h3>{{ t('cookbook', 'Instructions') }}</h3>
                        <RecipeInstructions
                            :instructions="recipe.instructions"
                        />
                    </main>
                </section>
            </div>
        </div>
        <!-- RecipeView container -->
    </div>
</template>

<script setup>
import { computed, getCurrentInstance, onMounted, ref, watch } from 'vue';
import { onBeforeRouteUpdate, useRoute } from 'vue-router/composables';

import NcButton from '@nextcloud/vue/dist/Components/NcButton.js';
import ContentCopyIcon from 'icons/ContentCopy.vue';
import api from 'cookbook/js/utils/api-interface';
import helpers from 'cookbook/js/helper';
import normalizeMarkdown from 'cookbook/js/title-rename';
import { showSimpleAlertModal } from 'cookbook/js/modals';
import emitter from '../../bus';
import { useStore } from '../../store';
import { parseDateTime } from '../../composables/dateTimeHandling';

import LoadingIndicator from '../Utilities/LoadingIndicator.vue';
import RecipeImages from './RecipeImages.vue';
import RecipeIngredients from './Ingredients/RecipeIngredients.vue';
import RecipeInstructions from './Instructions/RecipeInstructions.vue';
import RecipeKeywords from './RecipeKeywords.vue';
import RecipeTimes from './Timers/RecipeTimes.vue';
import RecipeTool from './RecipeTool.vue';
import RecipeYield from './RecipeYield.vue';

const route = useRoute();
const store = useStore();
const log = getCurrentInstance().proxy.$log;

/**
 * @type {import('vue').Ref<boolean>}
 */
const isLoading = ref(false);
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
/**
 * If the syntax of all ingredient items is valid in the sense that the amount can be recalculated with the yield.
 * @type {import('vue').Ref<boolean>}
 */
const isIngredientsSyntaxValid = ref(true);

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
        log.debug('Recipe is null');
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

const setup = async () => {
    isLoading.value = true;

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
        const tmpRecipe = await api.recipes.get(route.params.id);

        // Store recipe data in vuex
        store.dispatch('setRecipe', { recipe: tmpRecipe });

        // Always set the active page last!
        store.dispatch('setPage', { page: 'recipe' });
    } catch (ex) {
        if (store.state.loadingRecipe) {
            // Reset loading recipe
            store.dispatch('setLoadingRecipe', { recipe: 0 });
        }

        if (store.state.reloadingRecipe) {
            // Reset reloading recipe
            store.dispatch('setReloadingRecipe', { recipe: 0 });
        }

        store.dispatch('setPage', { page: 'recipe' });

        await showSimpleAlertModal(t('cookbook', 'Loading recipe failed'));
    } finally {
        isLoading.value = false;
    }

    recipeYield.value = store.state.recipe.recipeYield;
};

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
onBeforeRouteUpdate((to, from, next) => {
    // beforeRouteUpdate is called when the static route stays the same
    next();
    // Check if we should reload the component content
    if (helpers.shouldReloadContent(from.fullPath, to.fullPath)) {
        setup();
    }
});

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
    font-size: 1.1rem;
    font-weight: 200;
}

.wrapper {
    width: 100%;

    /* Limit max recipe width on very large screens to maintain readability */
    @media (min-width: 1280px) {
        padding: calc((100cqw - 1280px) * 0.05) calc((100cqw - 1280px) * 0.1) 0;
    }

    .wrapper-inner {
        max-width: 1600px;
    }
}

.loading-indicator {
    display: flex;
    justify-content: center;
    padding: 3rem 0;
}

:deep(.print-only) {
    display: none;
}

.title-container {
    padding: 0.2rem 0.4rem;
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
    padding: 1rem;

    @media (min-width: 768px) {
        grid-template-columns: 1fr 2fr;
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
    padding: 0 1em;

    .details {
        .section {
            padding: 0;
            margin: 0.75em 0;
        }
    }
}

section {
    margin-bottom: 1rem;
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

.instructions {
    padding: 0;
    margin-top: 2rem;
    counter-reset: instruction-counter;
    list-style: none;
}

.instructions .instruction {
    margin-bottom: 2rem;
    clear: both;
    counter-increment: instruction-counter;
    cursor: pointer;
}

.instructions .instruction::before {
    display: block;
    width: 36px;
    height: 36px;
    border: 1px solid var(--color-border-dark);
    border-radius: 50%;
    margin: -6px 1rem 1rem 0;
    background-color: var(--color-background-dark);
    background-position: center;
    background-repeat: no-repeat;
    content: counter(instruction-counter);
    float: left;
    line-height: 36px;
    outline: none;
    text-align: center;
}

.instructions .instruction:hover::before {
    border-color: var(--color-primary-element);
}

.instructions .instruction.done::before {
    content: '✔';
}

.content > .container {
    display: grid;
    grid-template-columns: 1fr;
    grid-template-rows: 100% 100% 100% 1fr;

    .ingredients {
        grid-row: 1/2;
    }

    .tools {
        grid-row: 2/3;
    }

    main {
        width: 100%;
        float: left;
        grid-column: 1/2;
        grid-row: 3/4;
        text-align: justify;
    }

    @media screen and (min-width: 851px), print {
        grid-template-columns: 1fr 1em 2fr;

        .ingredients {
            grid-column: 1/2;
            grid-row: 1/2;
        }

        .tools {
            grid-column: 1/2;
            grid-row: 2/3;
        }

        main {
            grid-column: 3/4;
            grid-row: 1/5;
        }
    }
}

.ingredient-parsing-error span.icon-error {
    display: inline-block;
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
