<template>
    <div class="wrapper">
        <div v-if="isLoading || store.loadingRecipe" class="loading-indicator">
            <LoadingIndicator :size="40" :delay="800" />
        </div>
        <div v-else>
            <div
                v-if="$store.state.recipe"
                class="header"
                :class="{ responsive: $store.state.recipe.image }"
            >
                <div v-if="$store.state.recipe.image" class="image">
                    <RecipeImages />
                </div>

                <div class="meta">
                    <h2 class="heading">{{ $store.state.recipe.name }}</h2>
                    <div class="details">
                        <div v-if="recipe.keywords.length">
                            <ul v-if="recipe.keywords.length">
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

                        <p class="dates">
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
                            class="markdown-description"
                        />
                        <p v-if="$store.state.recipe.url">
                            <strong>{{ t('cookbook', 'Source') }}: </strong
                            ><a
                                target="_blank"
                                :href="$store.state.recipe.url"
                                class="source-url"
                                >{{ $store.state.recipe.url }}</a
                            >
                        </p>
                        <div>
                            <p v-if="$store.state.recipe.recipeYield != null">
                                <strong
                                    >{{ t('cookbook', 'Servings') }}:
                                </strong>
                                <span class="print-only">
                                    {{ recipeYield }}
                                </span>
                                <span class="print-hidden">
                                    <button
                                        :disabled="recipeYield === 1"
                                        @click="changeRecipeYield(false)"
                                    >
                                        <span class="icon-view-previous" />
                                    </button>
                                    <input
                                        v-model.number="recipeYield"
                                        type="number"
                                        min="0"
                                        class="recipeYieldInput"
                                    />
                                    <button @click="changeRecipeYield">
                                        <span class="icon-view-next" />
                                    </button>
                                    <button
                                        v-if="
                                            recipeYield !==
                                            $store.state.recipe.recipeYield
                                        "
                                        @click="restoreOriginalRecipeYield"
                                    >
                                        <span class="icon-history" />
                                    </button>
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="times">
                        <RecipeTimer
                            v-if="
                                recipe.timerPrep &&
                                visibleInfoBlocks['preparation-time']
                            "
                            :value="recipe.timerPrep"
                            :timer="false"
                            :label="t('cookbook', 'Preparation time (H:MM)')"
                        />
                        <RecipeTimer
                            v-if="
                                recipe.timerCook &&
                                visibleInfoBlocks['cooking-time']
                            "
                            :value="recipe.timerCook"
                            :timer="true"
                            :label="t('cookbook', 'Cooking time (H:MM)')"
                        />
                        <RecipeTimer
                            v-if="
                                recipe.timerTotal &&
                                visibleInfoBlocks['total-time']
                            "
                            :value="recipe.timerTotal"
                            :timer="false"
                            :label="t('cookbook', 'Total time (H:MM)')"
                        />
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
                            <NcButton
                                v-if="scaledIngredients.length"
                                class="copy-ingredients print-hidden"
                                :type="'tertiary'"
                                aria-label="Copy all ingredients to the clipboard"
                                :title="t('cookbook', 'Copy ingredients')"
                                @click="copyIngredientsToClipboard"
                            >
                                <template #icon>
                                    <ContentCopyIcon :size="20" />
                                </template>
                            </NcButton>
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
                                :style="{
                                    'font-style': ingredientsWithValidSyntax[
                                        idx
                                    ]
                                        ? 'normal'
                                        : 'italic',
                                }"
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
                                t("cookbook", "The ingredient cannot be recalculated due to incorrect syntax. Please change it to this syntax: amount unit ingredient. Examples: 200 g carrots or 1 pinch of salt")
                            }}
                        </div>
                    </section>

                    <section v-if="visibleInfoBlocks.tools" class="tools">
                        <h3 v-if="parsedTools.length">
                            {{ t('cookbook', 'Tools') }}
                        </h3>
                        <ul v-if="parsedTools.length">
                            <RecipeTool
                                v-for="(tool, idx) in parsedTools"
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

                    <main v-if="parsedInstructions.length">
                        <h3>{{ t('cookbook', 'Instructions') }}</h3>
                        <ol class="instructions">
                            <RecipeInstruction
                                v-for="(instruction, idx) in parsedInstructions"
                                :key="'instr' + idx"
                                :instruction="instruction"
                            />
                        </ol>
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

import api from 'cookbook/js/api-interface';
import helpers from 'cookbook/js/helper';
import normalizeMarkdown from 'cookbook/js/title-rename';
import { showSimpleAlertModal } from 'cookbook/js/modals';
import yieldCalculator from 'cookbook/js/yieldCalculator';
import ContentCopyIcon from 'icons/ContentCopy.vue';
import NcButton from '@nextcloud/vue/dist/Components/NcButton';
import { useStore } from '../../store';
import emitter from '../../bus';
import { parseDateTime } from '../../composables/dateTimeHandling';

import LoadingIndicator from '../Utilities/LoadingIndicator.vue';
import RecipeImages from './RecipeImages.vue';
import RecipeIngredient from './RecipeIngredient.vue';
import RecipeInstruction from './RecipeInstruction.vue';
import RecipeKeyword from '../RecipeKeyword.vue';
import RecipeNutritionInfoItem from './RecipeNutritionInfoItem.vue';
import RecipeTimer from './RecipeTimer.vue';
import RecipeTool from './RecipeTool.vue';

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
 * @type {import('vue').Ref<Array.<string>>}
 */
const parsedInstructions = ref([]);
/**
 * @type {import('vue').Ref<Array.<string>>}
 */
const parsedTools = ref([]);
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
        ).map((i) => helpers.escapeHTML(i));
    }

    if (store.state.recipe.keywords) {
        tmpRecipe.keywords = String(store.state.recipe.keywords).split(',');
    }

    if (store.state.recipe.cookTime) {
        const cookT = store.state.recipe.cookTime.match(/PT(\d+?)H(\d+?)M/);
        const hh = parseInt(cookT[1], 10);
        const mm = parseInt(cookT[2], 10);
        if (hh > 0 || mm > 0) {
            tmpRecipe.timerCook = { hours: hh, minutes: mm };
        }
    }

    if (store.state.recipe.prepTime) {
        const prepT = store.state.recipe.prepTime.match(/PT(\d+?)H(\d+?)M/);
        const hh = parseInt(prepT[1], 10);
        const mm = parseInt(prepT[2], 10);
        if (hh > 0 || mm > 0) {
            tmpRecipe.timerPrep = { hours: hh, minutes: mm };
        }
    }

    if (store.state.recipe.totalTime) {
        const totalT = store.state.recipe.totalTime.match(/PT(\d+?)H(\d+?)M/);
        const hh = parseInt(totalT[1], 10);
        const mm = parseInt(totalT[2], 10);
        if (hh > 0 || mm > 0) {
            tmpRecipe.timerTotal = { hours: hh, minutes: mm };
        }
    }

    if (store.state.recipe.tool) {
        tmpRecipe.tools = store.state.recipe.tool.map((i) =>
            helpers.escapeHTML(i),
        );
    }

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
        recipe.value.nutrition &&
        !(recipe.value.nutrition instanceof Array) &&
        Object.keys(recipe.value.nutrition).length > 1 &&
        visibleInfoBlocks.value['nutrition-information'],
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
        const response = await api.recipes.get(route.params.id);
        const tmpRecipe = response.data;
        // Store recipe data in vuex
        store.dispatch('setRecipe', { recipe: tmpRecipe });

        // Always set the active page last!
        store.dispatch('setPage', { page: 'recipe' });
    } catch {
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

const changeRecipeYield = (increase = true) => {
    recipeYield.value += increase ? 1 : -1;
};

const copyIngredientsToClipboard = () => {
    const ingredientsToCopy = scaledIngredients.value.join('\n');

    if (navigator.clipboard) {
        navigator.clipboard
            .writeText(ingredientsToCopy)
            .then(() => log.info('JSON array copied to clipboard'))
            .catch((err) => log.error('Failed to copy JSON array: ', err));
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
            } else {
                log.error('Failed to copy JSON array');
            }
        } catch (err) {
            log.error('Failed to copy JSON array: ', err);
        }
        document.body.removeChild(input);
    }
};

const restoreOriginalRecipeYield = () => {
    recipeYield.value = store.state.recipe.recipeYield;
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

            if (r.instructions) {
                parsedInstructions.value = r.instructions.map(() =>
                    t('cookbook', 'Loading…'),
                );
                r.instructions.forEach((instruction, idx) => {
                    normalizeMarkdown(instruction)
                        .then((x) => {
                            parsedInstructions.value.splice(idx, 1, x);
                        })
                        .catch((ex) => {
                            log.error(ex);
                        });
                });
            } else {
                parsedInstructions.value = [];
            }

            if (r.tools) {
                parsedTools.value = r.tools.map(() =>
                    t('cookbook', 'Loading…'),
                );
                r.tools.forEach((tool, idx) => {
                    normalizeMarkdown(tool)
                        .then((x) => {
                            parsedTools.value.splice(idx, 1, x);
                        })
                        .catch((ex) => {
                            log.error(ex);
                        });
                });
            } else {
                parsedTools.value = [];
            }
        }
    },
);

watch(
    () => recipeYield.value,
    () => {
        if (recipeYield.value < 0) {
            restoreOriginalRecipeYield();
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
.wrapper {
    width: 100%;
}

.loading-indicator {
    display: flex;
    justify-content: center;
    padding: 3rem 0;
}

.print-only {
    display: none;
}

@media print {
    .header {
        display: flex;
        flex-wrap: wrap;
    }

    .header > .image {
        flex: 600px 0 0;
    }

    .header > .meta {
        margin: 0 10px;
    }

    .header a::after {
        content: '';
    }

    .print-hidden {
        display: none !important;
    }

    .print-only {
        display: initial !important;
    }
}

@media only screen and (min-width: 1500px) {
    .header.responsive {
        display: flex;
    }

    .header.responsive > .image {
        flex: 700px 0 0;
    }
}

.meta {
    margin: 0 1rem;
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

.description {
    font-style: italic;
    white-space: pre-line;
}

.details p {
    margin: 0.5em 0;
}

.times {
    display: flex;
    margin-top: 10px;
}

@media (max-width: 991px) {
    .times {
        flex-direction: column;
    }
}

@media print {
    .times {
        flex-direction: row;
    }
}

.times > div {
    margin: 1rem 0.75rem;
}

.times .time {
    position: relative;
    flex-grow: 1;
    border: 1px solid var(--color-border-dark);
    border-radius: 3px;
    margin: 1rem 2rem;
    font-size: 1.2rem;
    text-align: center;
}

.times .time button {
    position: absolute;
    top: 0;
    left: 0;
    width: 36px;
    height: 36px;
    transform: translate(-50%, -50%);
}

.times .time h4 {
    padding: 0.5rem;
    border-bottom: 1px solid var(--color-border-dark);
    background-color: var(--color-background-dark);
    font-weight: bold;
}

.times .time p {
    padding: 0.5rem;
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

.recipeYieldInput {
    width: 75px;

    /* Chrome, Safari, Edge */
    &::-webkit-inner-spin-button,
    &::-webkit-outer-spin-button {
        margin: 0;
        -webkit-appearance: none;
    }

    /* Firefox */
    -moz-appearance: textfield;
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
