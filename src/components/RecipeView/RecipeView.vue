<template>
    <div class="wrapper">
        <div v-if="isLoading || store.loadingRecipe" class="loading-indicator">
            <LoadingIndicator :size="40" :delay="800" />
        </div>
        <div v-else>
            <div
                v-if="$store.state.recipe"
                class="header w-full relative position-md-absolute flex d-md-grid flex-col flex-nowrap gap-4 gap-md-8"
                :class="{ responsive: $store.state.recipe.image }"
            >
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

                <div class="meta w-full max-w-md-full self-md-start">
                    <h2 class="heading">{{ $store.state.recipe.name }}</h2>
                    <div class="details">
                        <div
                            v-if="recipe.keywords && recipe.keywords.length > 0"
                            class="section"
                        >
                            <ul>
                                <RecipeKeyword
                                    v-for="(keyword, idx) in recipe.keywords"
                                    :key="'keyw' + idx"
                                    :name="keyword"
                                    :title="
                                        // prettier-ignore
                                        t('cookbook','Search recipes with this keyword')
                                    "
                                    @keyword-clicked="keywordClicked(keyword)"
                                />
                            </ul>
                        </div>

                        <p class="dates section">
                            <span
                                v-if="showCreatedDate"
                                class="date"
                                :title="t('cookbook', 'Date created')"
                            >
                                <span class="icon-calendar-dark date-icon" />
                                <span class="date-text">{{
                                    recipe.dateCreated
                                }}</span>
                            </span>
                            <span
                                v-if="showModifiedDate"
                                class="date"
                                :title="t('cookbook', 'Last modified')"
                            >
                                <span class="icon-rename date-icon" />
                                <span class="date-text">{{
                                    recipe.dateModified
                                }}</span>
                            </span>
                        </p>

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
                        <div class="times flex flex-wrap gap-4 mx-0 mt-2">
                            <RecipeTimer
                                v-if="
                                    recipe.timerTotal &&
                                    visibleInfoBlocks['total-time']
                                "
                                :value="recipe.timerTotal"
                                :timer="false"
                                :label="
                                    //TRANSLATORS Short for total time
                                    t('cookbook', 'Total')
                                "
                                class="time"
                            />
                            <RecipeTimer
                                v-if="
                                    recipe.timerPrep &&
                                    visibleInfoBlocks['preparation-time']
                                "
                                :value="recipe.timerPrep"
                                :timer="false"
                                :label="
                                    //TRANSLATORS Short for preparation time
                                    t('cookbook', 'Prep')
                                "
                                class="time"
                            />
                            <RecipeTimer
                                v-if="
                                    recipe.timerCook &&
                                    visibleInfoBlocks['cooking-time']
                                "
                                :value="recipe.timerCook"
                                :timer="true"
                                :label="
                                    //TRANSLATORS Short for cooking time
                                    t('cookbook', 'Cook')
                                "
                                class="time"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="$store.state.recipe" class="content">
                <section class="container">
                    <section class="ingredients">
                        <h3
                            v-if="scaledIngredients.length"
                            class="section-title"
                        >
                            <span>{{ t('cookbook', 'Ingredients') }}</span>
                            <div class="inline-flex h-0 align-items-center">
                                <NcButton
                                    v-if="scaledIngredients.length"
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
                            </div>
                        </h3>
                        <ul v-if="scaledIngredients.length">
                            <RecipeIngredient
                                v-for="(ingredient, idx) in scaledIngredients"
                                :key="'ingr' + idx"
                                :ingredient="ingredient"
                                :ingredient-has-correct-syntax="
                                    ingredientsWithValidSyntax[idx]
                                "
                                :recipe-ingredients-have-subgroups="
                                    recipeIngredientsHaveSubgroups
                                "
                                :class="
                                    ingredientsWithValidSyntax[idx]
                                        ? ''
                                        : 'ingredient-highlighted'
                                "
                            />
                        </ul>

                        <div
                            v-if="!ingredientsSyntaxCorrect"
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
                        <ul>
                            <RecipeTool
                                v-for="(tool, idx) in recipe.tools"
                                :key="'tool' + idx"
                                :tool="tool"
                            />
                        </ul>
                    </section>

                    <section v-if="showNutritionData" class="nutrition">
                        <h3>{{ t('cookbook', 'Nutrition Information') }}</h3>
                        <ul class="nutrition-items">
                            <recipe-nutrition-info-item
                                v-if="
                                    'servingSize' in recipe.nutrition &&
                                    !isNullOrEmpty(
                                        recipe.nutrition['servingSize'],
                                    )
                                "
                                :title="t('cookbook', 'Serving Size')"
                                :data="recipe.nutrition['servingSize']"
                            />
                            <recipe-nutrition-info-item
                                v-if="
                                    'calories' in recipe.nutrition &&
                                    !isNullOrEmpty(recipe.nutrition['calories'])
                                "
                                :title="t('cookbook', 'Energy')"
                                :data="recipe.nutrition['calories']"
                            />
                            <recipe-nutrition-info-item
                                v-if="
                                    'sugarContent' in recipe.nutrition &&
                                    !isNullOrEmpty(
                                        recipe.nutrition['sugarContent'],
                                    )
                                "
                                :title="t('cookbook', 'Sugar')"
                                :data="recipe.nutrition['sugarContent']"
                            />
                            <recipe-nutrition-info-item
                                v-if="
                                    'carbohydrateContent' in recipe.nutrition &&
                                    !isNullOrEmpty(
                                        recipe.nutrition['carbohydrateContent'],
                                    )
                                "
                                :title="t('cookbook', 'Carbohydrate')"
                                :data="recipe.nutrition['carbohydrateContent']"
                            />
                            <recipe-nutrition-info-item
                                v-if="
                                    'cholesterolContent' in recipe.nutrition &&
                                    !isNullOrEmpty(
                                        recipe.nutrition['cholesterolContent'],
                                    )
                                "
                                :title="t('cookbook', 'Cholesterol')"
                                :data="recipe.nutrition['cholesterolContent']"
                            />
                            <recipe-nutrition-info-item
                                v-if="
                                    'fiberContent' in recipe.nutrition &&
                                    !isNullOrEmpty(
                                        recipe.nutrition['fiberContent'],
                                    )
                                "
                                :title="t('cookbook', 'Fiber')"
                                :data="recipe.nutrition['fiberContent']"
                            />
                            <recipe-nutrition-info-item
                                v-if="
                                    'proteinContent' in recipe.nutrition &&
                                    !isNullOrEmpty(
                                        recipe.nutrition['proteinContent'],
                                    )
                                "
                                :title="t('cookbook', 'Protein')"
                                :data="recipe.nutrition['proteinContent']"
                            />
                            <recipe-nutrition-info-item
                                v-if="
                                    'sodiumContent' in recipe.nutrition &&
                                    !isNullOrEmpty(
                                        recipe.nutrition['sodiumContent'],
                                    )
                                "
                                :title="t('cookbook', 'Sodium')"
                                :data="recipe.nutrition['sodiumContent']"
                            />
                            <recipe-nutrition-info-item
                                v-if="
                                    'fatContent' in recipe.nutrition &&
                                    !isNullOrEmpty(
                                        recipe.nutrition['fatContent'],
                                    )
                                "
                                :title="t('cookbook', 'Fat total')"
                                :data="recipe.nutrition['fatContent']"
                            />
                            <recipe-nutrition-info-item
                                v-if="
                                    'saturatedFatContent' in recipe.nutrition &&
                                    !isNullOrEmpty(
                                        recipe.nutrition['saturatedFatContent'],
                                    )
                                "
                                :title="t('cookbook', 'Saturated Fat')"
                                :data="recipe.nutrition['saturatedFatContent']"
                            />
                            <recipe-nutrition-info-item
                                v-if="
                                    'unsaturatedFatContent' in
                                        recipe.nutrition &&
                                    !isNullOrEmpty(
                                        recipe.nutrition[
                                            'unsaturatedFatContent'
                                        ],
                                    )
                                "
                                :title="t('cookbook', 'Unsaturated Fat')"
                                :data="
                                    recipe.nutrition['unsaturatedFatContent']
                                "
                            />
                            <recipe-nutrition-info-item
                                v-if="
                                    'transFatContent' in recipe.nutrition &&
                                    !isNullOrEmpty(
                                        recipe.nutrition['transFatContent'],
                                    )
                                "
                                :title="t('cookbook', 'Trans Fat')"
                                :data="recipe.nutrition['transFatContent']"
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
import {
    onBeforeRouteUpdate,
    useRoute,
    useRouter,
} from 'vue-router/composables';

import api from 'cookbook/js/utils/api-interface';
import helpers from 'cookbook/js/helper';
import normalizeMarkdown from 'cookbook/js/title-rename';
import { showSimpleAlertModal } from 'cookbook/js/modals';
import yieldCalculator from 'cookbook/js/yieldCalculator';
import ContentCopyIcon from 'icons/ContentCopy.vue';
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js';
import { showError, showSuccess } from '@nextcloud/dialogs';
import RecipeInstructions from 'cookbook/components/RecipeView/Instructions/RecipeInstructions.vue';
import { useStore } from '../../store';
import emitter from '../../bus';
import { parseDateTime } from '../../composables/dateTimeHandling';

import LoadingIndicator from '../Utilities/LoadingIndicator.vue';
import RecipeImages from './RecipeImages.vue';
import RecipeIngredient from './RecipeIngredient.vue';
import RecipeKeyword from '../RecipeKeyword.vue';
import RecipeNutritionInfoItem from './RecipeNutritionInfoItem.vue';
import RecipeTimer from './RecipeTimer.vue';
import RecipeTool from './RecipeTool.vue';
import RecipeYield from './RecipeYield.vue';

const route = useRoute();
const router = useRouter();
const store = useStore();
const log = getCurrentInstance().proxy.$log;

/**
 * @type {import('vue').Ref<boolean>}
 */
const isLoading = ref(false);
/**
 * @type {string}
 */
const headerPrefix = '## ';
/**
 * @type {import('vue').Ref<string>}
 */
const parsedDescription = ref('');
/**
 * @type {import('vue').Ref<Array.<string>>}
 */
const parsedIngredients = ref([]);
/**
 * @type {import('vue').Ref<number>}
 */
const recipeYield = ref(0);

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

    if (store.state.recipe.nutrition) {
        if (store.state.recipe.nutrition instanceof Array) {
            tmpRecipe.nutrition = {};
        } else if (
            store.state.recipe.nutrition['@type'] === 'NutritionInformation'
        ) {
            tmpRecipe.nutrition = store.state.recipe.nutrition;
        } else {
            tmpRecipe.nutrition = store.state.recipe.nutrition;
        }
    } else {
        tmpRecipe.nutrition = {};
    }

    return tmpRecipe;
});

const recipeIngredientsHaveSubgroups = computed(() => {
    if (recipe.value.ingredients && recipe.value.ingredients.length > 0) {
        for (let idx = 0; idx < recipe.value.ingredients.length; ++idx) {
            if (recipe.value.ingredients[idx].startsWith(headerPrefix)) {
                return true;
            }
        }
    }
    return false;
});

const showCreatedDate = computed(() => recipe.value.dateCreated);

const showModifiedDate = computed(() => {
    if (!recipe.value.dateModified) {
        return false;
    }
    return !(
        store.state.recipe.dateCreated &&
        store.state.recipe.dateModified &&
        store.state.recipe.dateCreated === store.state.recipe.dateModified
    );
});

const visibleInfoBlocks = computed(
    () => store.state.config?.visibleInfoBlocks ?? {},
);

const showNutritionData = computed(
    () =>
        visibleInfoBlocks.value['nutrition-information'] &&
        recipe.value.nutrition &&
        recipe.value.nutrition['@type'] === 'NutritionInformation' &&
        !recipe.value.nutrition.isUndefined,
);

const scaledIngredients = computed(() =>
    yieldCalculator.recalculateIngredients(
        parsedIngredients.value,
        recipeYield.value,
        store.state.recipe.recipeYield,
    ),
);

const ingredientsWithValidSyntax = computed(() =>
    parsedIngredients.value.map(yieldCalculator.isValidIngredientSyntax),
);

const ingredientsSyntaxCorrect = computed(() =>
    ingredientsWithValidSyntax.value.every((x) => x),
);

// ===================
// Methods
// ===================
const isNullOrEmpty = (str) =>
    !str || (typeof str === 'string' && str.trim().length === 0);

/**
 * Callback for click on keyword
 */
const keywordClicked = (keyword) => {
    if (keyword) {
        router.push(`/tags/${keyword}`);
    }
};

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

function showCopySuccess(item) {
    showSuccess(t('cookbook', '{item} copied to clipboard', { item }));
}
function showCopyError(item) {
    showError(t('cookbook', 'Copying {item} to clipboard failed', { item }));
}

const copyIngredientsToClipboard = () => {
    const ingredientsToCopy = scaledIngredients.value.join('\n');

    if (navigator.clipboard) {
        navigator.clipboard
            .writeText(ingredientsToCopy)
            .then(() => {
                log.info('JSON array copied to clipboard');
                showCopySuccess(t('cookbook', 'Ingredients'));
            })
            .catch((err) => {
                log.error('Failed to copy JSON array: ', err);
                showCopyError(t('cookbook', 'ingredients'));
            });
    } else {
        // fallback solution
        const input = document.createElement('textarea');
        input.style.position = 'absolute';
        input.style.left = '-1000px';
        input.style.top = '-1000px';
        input.value = ingredientsToCopy;
        document.body.appendChild(input);
        input.select();
        try {
            const successful = document.execCommand('copy');
            if (successful) {
                log.info('JSON array copied to clipboard');
                showCopySuccess(t('cookbook', 'Ingredients'));
            } else {
                log.error('Failed to copy JSON array');
                showCopyError(t('cookbook', 'ingredients'));
            }
        } catch (err) {
            log.error('Failed to copy JSON array: ', err);
            showCopyError(t('cookbook', 'ingredients'));
        }
        document.body.removeChild(input);
    }
};

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

            if (r.ingredients) {
                parsedIngredients.value = r.ingredients.map(() =>
                    t('cookbook', 'Loading…'),
                );
                r.ingredients.forEach((ingredient, idx) => {
                    normalizeMarkdown(ingredient)
                        .then((x) => {
                            parsedIngredients.value.splice(idx, 1, x);
                        })
                        .catch((ex) => {
                            log.error(ex);
                        });
                });
            } else {
                parsedIngredients.value = [];
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
        font-size: 2.5rem;
        font-weight: 250;
        line-height: 2.8rem;
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
}

.loading-indicator {
    display: flex;
    justify-content: center;
    padding: 3rem 0;
}

:deep(.print-only) {
    display: none;
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
    @media (min-width: 768px) {
        grid-template-columns: 1fr 2fr;
        .image {
            max-height: initial;
        }
    }

    .image {
        max-height: 66.6vh;
    }
}

.heading {
    margin-top: 12px;
}

.dates {
    font-size: 0.9em;
}

.date {
    margin-right: 1.5em;
}

.date-icon {
    display: inline-block;
    margin-right: 0.2em;
    margin-bottom: 0.2em;
    background-size: 1em;
    vertical-align: middle;
}

.date-text {
    vertical-align: middle;
}

.copy-ingredients {
    float: right;
}

.ingredient-highlighted {
    font-style: italic;
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

.times {
    .time {
        max-width: 100%;
        flex-basis: 0;
        flex-grow: 1;
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

.nutrition-items {
    list-style-type: none;
}

main {
    width: 70%;
    flex-basis: calc(100% - 22rem);
    float: left;
    text-align: justify;
}

@media screen and (max-width: 1199px) {
    main {
        width: 100%;
        flex-basis: 100%;
    }
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

    grid-template-columns: 1fr 1em 2fr;
    grid-template-rows: 100% 100% 100% 1fr;

    .ingredients {
        grid-column: 1/2;
        grid-row: 1/2;
    }

    .tools {
        grid-column: 1/2;
        grid-row: 2/3;
    }

    .nutrition {
        grid-column: 1/2;
        grid-row: 3/4;
    }

    main {
        grid-column: 3/4;
        grid-row: 1/5;
    }

    @media screen and (max-width: 850px), print {
        grid-template-columns: 1fr;

        .ingredients {
            // grid-column: 1/2;
            grid-row: 1/2;
        }

        .tools {
            // grid-column: 1/2;
            grid-row: 2/3;
        }

        .nutrition {
            // grid-column: 1/2;
            grid-row: 4/5;
        }

        main {
            width: 100%;
            grid-column: 1/2;
            grid-row: 3/4;
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
