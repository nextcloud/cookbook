<template>
    <div>
        <RecipeListKeywordCloud v-model="keywordFilter" :keywords="rawKeywords" :filteredRecipes="filteredRecipes"/>
        <div id="recipes-submenu" class="recipes-submenu-container">
            <Multiselect
                v-if="recipes.length > 0"
                v-model="orderBy"
                class="recipes-sorting-dropdown"
                :multiple="false"
                :searchable="false"
                :placeholder="t('cookbook', 'Select order')"
                :options="recipeOrderingOptions"
            >
                <template
                    slot="placeholder"
                    class="recipe-sorting-item-placeholder"
                >
                    <span class="icon-triangle-n" style="margin-right: -8px" />
                    <span class="ordering-item-icon icon-triangle-s" />
                    {{ t("cookbook", "Select order") }}
                </template>
                <template #singleLabel="props">
                    <span
                        class="ordering-item-icon"
                        :class="props.option.icon"
                    />
                    <span class="option__title">{{ props.option.label }}</span>
                </template>
                <template #option="props">
                    <span
                        class="ordering-item-icon"
                        :class="props.option.icon"
                    />
                    <span class="option__title">{{ props.option.label }}</span>
                </template>
            </Multiselect>
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
</template>

<script>
import moment from "@nextcloud/moment"
import Multiselect from "@nextcloud/vue/dist/Components/Multiselect"
import RecipeCard from "./RecipeCard.vue"
import RecipeListKeywordCloud from "./RecipeListKeywordCloud.vue"

export default {
    name: "RecipeList",
    components: {
        Multiselect,
        RecipeCard,
        RecipeListKeywordCloud
    },
    props: {
        recipes: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            // String-based filters applied to the list
            filters: "",
            // All keywords to filter the recipes for (conjunctively)
            keywordFilter: [],
            orderBy: null,
            recipeOrderingOptions: [
                {
                    label: t("cookbook", "Name"),
                    icon: "icon-triangle-n",
                    recipeProperty: "name",
                    order: "ascending",
                },
                {
                    label: t("cookbook", "Name"),
                    icon: "icon-triangle-s",
                    recipeProperty: "name",
                    order: "descending",
                },
                {
                    label: t("cookbook", "Creation date"),
                    icon: "icon-triangle-n",
                    recipeProperty: "dateCreated",
                    order: "ascending",
                },
                {
                    label: t("cookbook", "Creation date"),
                    icon: "icon-triangle-s",
                    recipeProperty: "dateCreated",
                    order: "descending",
                },
                {
                    label: t("cookbook", "Modification date"),
                    icon: "icon-triangle-n",
                    recipeProperty: "dateModified",
                    order: "ascending",
                },
                {
                    label: t("cookbook", "Modification date"),
                    icon: "icon-triangle-s",
                    recipeProperty: "dateModified",
                    order: "descending",
                },
            ],
        }
    },
    computed: {
        /**
         * An array of all keywords in all recipes. These are neither sorted nor unique
         */
        rawKeywords() {
            const keywordArray = this.recipes.map((r) => {
                if (!("keywords" in r)) {
                    return []
                }
                if (r.keywords != null) {
                    return r.keywords.split(",")
                }
                return []
            })
            return [].concat(...keywordArray)
        },
        /**
         * An array of all recipes that are part in all filtered keywords
         */
        recipesFilteredByKeywords() {
            const $this = this
            return this.recipes.filter((r) => {
                if ($this.keywordFilter.length === 0) {
                    // No filtering, keep all
                    return true
                }

                if (r.keywords === null) {
                    return false
                }

                function keywordInRecipePresent(kw, r2) {
                    if (!r2.keywords) {
                        return false
                    }
                    const keywords = r2.keywords.split(",")
                    return keywords.includes(kw)
                }

                return $this.keywordFilter
                    .map((kw) => keywordInRecipePresent(kw, r))
                    .reduce((l, rec) => l && rec)
            })
        },
        /**
         * An array of the finally filtered recipes, that is both filtered for keywords as well as string-based name filtering
         */
        filteredRecipes() {
            let ret = this.recipesFilteredByKeywords
            const $this = this

            if (this.filters) {
                ret = ret.filter((r) =>
                    r.name.toLowerCase().includes($this.filters.toLowerCase())
                )
            }

            return ret
        },
        // Recipes ordered ascending by name
        recipesNameAsc() {
            return this.sortRecipes(this.recipes, "name", "ascending")
        },
        // Recipes ordered descending by name
        recipesNameDesc() {
            return this.sortRecipes(this.recipes, "name", "descending")
        },
        // Recipes ordered ascending by creation date
        recipesDateCreatedAsc() {
            return this.sortRecipes(this.recipes, "dateCreated", "ascending")
        },
        // Recipes ordered descending by creation date
        recipesDateCreatedDesc() {
            return this.sortRecipes(this.recipes, "dateCreated", "descending")
        },
        // Recipes ordered ascending by modification date
        recipesDateModifiedAsc() {
            return this.sortRecipes(this.recipes, "dateModified", "ascending")
        },
        // Recipes ordered descending by modification date
        recipesDateModifiedDesc() {
            return this.sortRecipes(this.recipes, "dateModified", "descending")
        },
        // An array of recipe objects of all recipes with links to the recipes and a property if the recipe is to be shown
        recipeObjects() {
            function makeObject(rec) {
                return {
                    recipe: rec,
                    show: this.filteredRecipes
                        .map((r) => r.recipe_id)
                        .includes(rec.recipe_id),
                }
            }

            if (
                this.orderBy === null ||
                (this.orderBy.order !== "ascending" &&
                    this.orderBy.order !== "descending")
            ) {
                return this.recipes.map(makeObject, this)
            }
            if (this.orderBy.recipeProperty === "dateCreated") {
                if (this.orderBy.order === "ascending") {
                    return this.recipesDateCreatedAsc.map(makeObject, this)
                }
                return this.recipesDateCreatedDesc.map(makeObject, this)
            }
            if (this.orderBy.recipeProperty === "dateModified") {
                if (this.orderBy.order === "ascending") {
                    return this.recipesDateModifiedAsc.map(makeObject, this)
                }
                return this.recipesDateModifiedDesc.map(makeObject, this)
            }
            if (this.orderBy.recipeProperty === "name") {
                if (this.orderBy.order === "ascending") {
                    return this.recipesNameAsc.map(makeObject, this)
                }
                return this.recipesNameDesc.map(makeObject, this)
            }
            return this.recipes.map(makeObject, this)
        },
    },
    mounted() {
        this.$root.$off("applyRecipeFilter")
        this.$root.$on("applyRecipeFilter", (value) => {
            this.filters = value
        })
        // Set default order for recipe
        // eslint-disable-next-line prefer-destructuring
        this.orderBy = this.recipeOrderingOptions[0]
    },
    methods: {
        /* Sort recipes according to the property of the recipe ascending or
         * descending
         */
        sortRecipes(recipes, recipeProperty, order) {
            const rec = JSON.parse(JSON.stringify(recipes))
            return rec.sort((r1, r2) => {
                if (order !== "ascending" && order !== "descending") return 0
                if (order === "ascending") {
                    if (
                        recipeProperty === "dateCreated" ||
                        recipeProperty === "dateModified"
                    ) {
                        return (
                            new Date(r1[recipeProperty]) -
                            new Date(r2[recipeProperty])
                        )
                    }
                    if (recipeProperty === "name") {
                        return r1[recipeProperty].localeCompare(
                            r2[recipeProperty]
                        )
                    }
                    if (
                        !Number.isNaN(r1[recipeProperty] - r2[recipeProperty])
                    ) {
                        return r1[recipeProperty] - r2[recipeProperty]
                    }
                    return 0
                }

                if (
                    recipeProperty === "dateCreated" ||
                    recipeProperty === "dateModified"
                ) {
                    return (
                        new Date(r2[recipeProperty]) -
                        new Date(r1[recipeProperty])
                    )
                }
                if (recipeProperty === "name") {
                    return r2[recipeProperty].localeCompare(r1[recipeProperty])
                }
                if (!Number.isNaN(r2[recipeProperty] - r1[recipeProperty])) {
                    return r2[recipeProperty] - r1[recipeProperty]
                }
                return 0
            })
        },
    },
}
</script>

<style>
/* stylelint-disable selector-class-pattern */
#recipes-submenu .multiselect .multiselect__tags {
    border: 0;
}
/* stylelint-enable selector-class-pattern */
</style>

<style scoped>

.recipes-submenu-container {
    padding-left: 16px;
    margin-bottom: 0.75ex;
}

.recipe-sorting-item-placeholder {
    display: block;
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
</style>
