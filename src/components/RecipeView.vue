<template>
    <div class="wrapper">
        <div
            v-if="$store.state.recipe"
            class="header"
            :class="{ responsive: $store.state.recipe.image }"
        >
            <div v-if="$store.state.recipe.image" class="image">
                <RecipeImages />
            </div>

            <div class="meta">
                <h2>{{ $store.state.recipe.name }}</h2>
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
                        :markdown="recipe.description"
                        class="markdown-description"
                    />
                    <p v-if="$store.state.recipe.url">
                        <strong>{{ t("cookbook", "Source") }}: </strong
                        ><a
                            target="_blank"
                            :href="$store.state.recipe.url"
                            class="source-url"
                            >{{ $store.state.recipe.url }}</a
                        >
                    </p>
                    <p>
                        <strong>{{ t("cookbook", "Servings") }}: </strong
                        >{{ $store.state.recipe.recipeYield }}
                    </p>
                </div>
                <div class="times">
                    <RecipeTimer
                        v-if="recipe.timerPrep"
                        :value="recipe.timerPrep"
                        :phase="'prep'"
                        :timer="false"
                        :label="'Preparation time'"
                    />
                    <RecipeTimer
                        v-if="recipe.timerCook"
                        :value="recipe.timerCook"
                        :phase="'prep'"
                        :timer="true"
                        :label="'Cooking time'"
                    />
                    <RecipeTimer
                        v-if="recipe.timerTotal"
                        :value="recipe.timerTotal"
                        :phase="'total'"
                        :timer="false"
                        :label="'Total time'"
                    />
                </div>
            </div>
        </div>

        <div v-if="$store.state.recipe" class="content">
            <section>
                <aside>
                    <section>
                        <h3 v-if="recipe.ingredients.length">
                            {{ t("cookbook", "Ingredients") }}
                        </h3>
                        <ul v-if="recipe.ingredients.length">
                            <RecipeIngredient
                                v-for="(ingredient, idx) in recipe.ingredients"
                                :key="'ingr' + idx"
                                :ingredient="ingredient"
                                :recipe-ingredients-have-subgroups="
                                    recipeIngredientsHaveSubgroups
                                "
                            />
                        </ul>
                    </section>

                    <section>
                        <h3 v-if="recipe.tools.length">
                            {{ t("cookbook", "Tools") }}
                        </h3>
                        <ul v-if="recipe.tools.length">
                            <RecipeTool
                                v-for="(tool, idx) in recipe.tools"
                                :key="'tool' + idx"
                                :tool="tool"
                            />
                        </ul>
                    </section>

                    <section v-if="showNutritionData">
                        <h3>{{ t("cookbook", "Nutrition Information") }}</h3>
                        <ul class="nutrition-items">
                            <recipe-nutrition-info-item
                                v-if="
                                    'servingSize' in recipe.nutrition &&
                                    !isNullOrEmpty(
                                        recipe.nutrition['servingSize']
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
                                        recipe.nutrition['sugarContent']
                                    )
                                "
                                :title="t('cookbook', 'Sugar')"
                                :data="recipe.nutrition['sugarContent']"
                            />
                            <recipe-nutrition-info-item
                                v-if="
                                    'carbohydrateContent' in recipe.nutrition &&
                                    !isNullOrEmpty(
                                        recipe.nutrition['carbohydrateContent']
                                    )
                                "
                                :title="t('cookbook', 'Carbohydrate')"
                                :data="recipe.nutrition['carbohydrateContent']"
                            />
                            <recipe-nutrition-info-item
                                v-if="
                                    'cholesterolContent' in recipe.nutrition &&
                                    !isNullOrEmpty(
                                        recipe.nutrition['cholesterolContent']
                                    )
                                "
                                :title="t('cookbook', 'Cholesterol')"
                                :data="recipe.nutrition['cholesterolContent']"
                            />
                            <recipe-nutrition-info-item
                                v-if="
                                    'fiberContent' in recipe.nutrition &&
                                    !isNullOrEmpty(
                                        recipe.nutrition['fiberContent']
                                    )
                                "
                                :title="t('cookbook', 'Fiber')"
                                :data="recipe.nutrition['fiberContent']"
                            />
                            <recipe-nutrition-info-item
                                v-if="
                                    'proteinContent' in recipe.nutrition &&
                                    !isNullOrEmpty(
                                        recipe.nutrition['proteinContent']
                                    )
                                "
                                :title="t('cookbook', 'Protein')"
                                :data="recipe.nutrition['proteinContent']"
                            />
                            <recipe-nutrition-info-item
                                v-if="
                                    'sodiumContent' in recipe.nutrition &&
                                    !isNullOrEmpty(
                                        recipe.nutrition['sodiumContent']
                                    )
                                "
                                :title="t('cookbook', 'Sodium')"
                                :data="recipe.nutrition['sodiumContent']"
                            />
                            <recipe-nutrition-info-item
                                v-if="
                                    'fatContent' in recipe.nutrition &&
                                    !isNullOrEmpty(
                                        recipe.nutrition['fatContent']
                                    )
                                "
                                :title="t('cookbook', 'Fat total')"
                                :data="recipe.nutrition['fatContent']"
                            />
                            <recipe-nutrition-info-item
                                v-if="
                                    'saturatedFatContent' in recipe.nutrition &&
                                    !isNullOrEmpty(
                                        recipe.nutrition['saturatedFatContent']
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
                                        ]
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
                                        recipe.nutrition['transFatContent']
                                    )
                                "
                                :title="t('cookbook', 'Trans Fat')"
                                :data="recipe.nutrition['transFatContent']"
                            />
                        </ul>
                    </section>
                </aside>
                <main v-if="recipe.instructions.length">
                    <h3>{{ t("cookbook", "Instructions") }}</h3>
                    <ol class="instructions">
                        <RecipeInstruction
                            v-for="(instruction, idx) in recipe.instructions"
                            :key="'instr' + idx"
                            :instruction="instruction"
                        />
                    </ol>
                </main>
            </section>
        </div>
    </div>
</template>

<script>
import axios from "@nextcloud/axios"
import moment from "@nextcloud/moment"

import RecipeImages from "./RecipeImages.vue"
import RecipeIngredient from "./RecipeIngredient.vue"
import RecipeInstruction from "./RecipeInstruction.vue"
import RecipeKeyword from "./RecipeKeyword.vue"
import RecipeNutritionInfoItem from "./RecipeNutritionInfoItem.vue"
import RecipeTimer from "./RecipeTimer.vue"
import RecipeTool from "./RecipeTool.vue"

export default {
    name: "RecipeView",
    components: {
        RecipeImages,
        RecipeIngredient,
        RecipeInstruction,
        RecipeKeyword,
        RecipeNutritionInfoItem,
        RecipeTimer,
        RecipeTool,
    },
    /**
     * This is one tricky feature of Vue router. If different paths lead to
     * the same component (such as '/recipe/xxx' and '/recipe/yyy)',
     * the component will not automatically reload. So we have to manually
     * reload the page contents.
     * This can also be used to confirm that the user wants to leave the page
     * if there are unsaved changes.
     */
    beforeRouteUpdate(to, from, next) {
        // beforeRouteUpdate is called when the static route stays the same
        next()
        // Check if we should reload the component content
        if (this.$window.shouldReloadContent(from.fullPath, to.fullPath)) {
            this.setup()
        }
    },
    data() {
        return {
            headerPrefix: "## ",
        }
    },
    computed: {
        recipe() {
            const recipe = {
                description: "",
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
            }

            if (this.$store.state.recipe.description) {
                recipe.description = this.convertRecipeReferences(
                    window.escapeHTML(this.$store.state.recipe.description)
                )
            }

            if (this.$store.state.recipe.recipeIngredient) {
                recipe.ingredients = Object.values(
                    this.$store.state.recipe.recipeIngredient
                ).map((i) => this.convertRecipeReferences(window.escapeHTML(i)))
            }

            if (this.$store.state.recipe.recipeInstructions) {
                recipe.instructions = Object.values(
                    this.$store.state.recipe.recipeInstructions
                ).map((i) => this.convertRecipeReferences(window.escapeHTML(i)))
            }

            if (this.$store.state.recipe.keywords) {
                recipe.keywords = String(
                    this.$store.state.recipe.keywords
                ).split(",")
            }

            if (this.$store.state.recipe.cookTime) {
                const cookT =
                    this.$store.state.recipe.cookTime.match(/PT(\d+?)H(\d+?)M/)
                const hh = parseInt(cookT[1], 10)
                const mm = parseInt(cookT[2], 10)
                if (hh > 0 || mm > 0) {
                    recipe.timerCook = { hours: hh, minutes: mm }
                }
            }

            if (this.$store.state.recipe.prepTime) {
                const prepT =
                    this.$store.state.recipe.prepTime.match(/PT(\d+?)H(\d+?)M/)
                const hh = parseInt(prepT[1], 10)
                const mm = parseInt(prepT[2], 10)
                if (hh > 0 || mm > 0) {
                    recipe.timerPrep = { hours: hh, minutes: mm }
                }
            }

            if (this.$store.state.recipe.totalTime) {
                const totalT =
                    this.$store.state.recipe.totalTime.match(/PT(\d+?)H(\d+?)M/)
                const hh = parseInt(totalT[1], 10)
                const mm = parseInt(totalT[2], 10)
                if (hh > 0 || mm > 0) {
                    recipe.timerTotal = { hours: hh, minutes: mm }
                }
            }

            if (this.$store.state.recipe.tool) {
                recipe.tools = this.$store.state.recipe.tool.map((i) =>
                    this.convertRecipeReferences(window.escapeHTML(i))
                )
            }

            if (this.$store.state.recipe.dateCreated) {
                const date = this.parseDateTime(
                    this.$store.state.recipe.dateCreated
                )
                recipe.dateCreated =
                    date != null ? date.format("L, LT").toString() : null
            }

            if (this.$store.state.recipe.dateModified) {
                const date = this.parseDateTime(
                    this.$store.state.recipe.dateModified
                )
                recipe.dateModified =
                    date != null ? date.format("L, LT").toString() : null
            }

            if (this.$store.state.recipe.nutrition) {
                if (this.$store.state.recipe.nutrition instanceof Array) {
                    recipe.nutrition = {}
                } else {
                    recipe.nutrition = this.$store.state.recipe.nutrition
                }
            } else {
                recipe.nutrition = {}
            }

            return recipe
        },
        recipeIngredientsHaveSubgroups() {
            if (this.recipe.ingredients && this.recipe.ingredients.length > 0) {
                for (let idx = 0; idx < this.recipe.ingredients.length; ++idx) {
                    if (
                        this.recipe.ingredients[idx].startsWith(
                            this.headerPrefix
                        )
                    ) {
                        return true
                    }
                }
            }
            return false
        },
        showCreatedDate() {
            return this.recipe.dateCreated
        },
        showModifiedDate() {
            if (!this.recipe.dateModified) {
                return false
            }
            return !(
                this.$store.state.recipe.dateCreated &&
                this.$store.state.recipe.dateModified &&
                this.$store.state.recipe.dateCreated ===
                    this.$store.state.recipe.dateModified
            )
        },
        showNutritionData() {
            return (
                this.recipe.nutrition &&
                !(this.recipe.nutrition instanceof Array) &&
                Object.keys(this.recipe.nutrition).length > 0
            )
        },
    },
    mounted() {
        this.setup()
        // Register data load method hook for access from the controls components
        this.$root.$off("reloadRecipeView")
        this.$root.$on("reloadRecipeView", () => {
            this.setup()
        })
    },
    methods: {
        convertRecipeReferences(text) {
            const re = /(^|\s|[,._+&?!-])#r\/(\d+)(?=$|\s|[.,_+&?!-])/g
            const converted = text.replace(
                re,
                `$1<a class="recipe-reference-inline" href="${this.$window.baseUrl}/#/recipe/$2">#$2</a>`
            )
            return converted
        },
        isNullOrEmpty(str) {
            return !str || (typeof str === "string" && str.trim().length === 0)
        },
        /**
         * Callback for click on keyword
         */
        keywordClicked(keyword) {
            if (keyword) {
                this.$router.push(`/tags/${keyword}`)
            }
        },
        /* The schema.org standard requires the dates formatted as Date (https://schema.org/Date)
         * or DateTime (https://schema.org/DateTime). This follows the ISO 8601 standard.
         */
        parseDateTime(dt) {
            if (!dt) return null
            const date = moment(dt, moment.ISO_8601)
            if (!date.isValid()) {
                return null
            }
            return date
        },
        setup() {
            // Make the control row show that a recipe is loading
            if (!this.$store.state.recipe) {
                this.$store.dispatch("setLoadingRecipe", { recipe: -1 })

                // Make the control row show that the recipe is reloading
            } else if (
                this.$store.state.recipe.id ===
                parseInt(this.$route.params.id, 10)
            ) {
                this.$store.dispatch("setReloadingRecipe", {
                    recipe: this.$route.params.id,
                })

                // Make the control row show that a new recipe is loading
            } else {
                this.$store.dispatch("setLoadingRecipe", {
                    recipe: this.$route.params.id,
                })
            }

            const $this = this

            axios
                .get(
                    `${this.$window.baseUrl}/api/recipes/${this.$route.params.id}`
                )
                .then((response) => {
                    const recipe = response.data
                    // Store recipe data in vuex
                    $this.$store.dispatch("setRecipe", { recipe })

                    // Always set the active page last!
                    $this.$store.dispatch("setPage", { page: "recipe" })
                })
                .catch(() => {
                    if ($this.$store.state.loadingRecipe) {
                        // Reset loading recipe
                        $this.$store.dispatch("setLoadingRecipe", { recipe: 0 })
                    }

                    if ($this.$store.state.reloadingRecipe) {
                        // Reset reloading recipe
                        $this.$store.dispatch("setReloadingRecipe", {
                            recipe: 0,
                        })
                    }

                    $this.$store.dispatch("setPage", { page: "recipe" })

                    // eslint-disable-next-line no-alert
                    alert(t("cookbook", "Loading recipe failed"))
                })
        },
    },
}
</script>

<style scoped>
.wrapper {
    width: 100%;
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
        content: "";
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
    margin: 1rem 2rem;
    border-radius: 3px;
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
    content: "";
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
aside ul li input[type="checkbox"] {
    display: inline-block;
    width: 1rem;
    height: auto;
    padding: 0;
    margin: 0 0.5rem 0 0;
    line-height: 1rem;
    vertical-align: middle;
}

.markdown-description >>> ol > li {
    list-style-type: numbered;
}

.markdown-description >>> ul > li {
    list-style-type: disc;
}

.markdown-description >>> ol > li,
.markdown-description >>> ul > li {
    margin-left: 20px;
}

.markdown-description >>> a {
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
    margin: -6px 1rem 1rem 0;
    background-color: var(--color-background-dark);
    background-position: center;
    background-repeat: no-repeat;
    border-radius: 50%;
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
    content: "✔";
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
