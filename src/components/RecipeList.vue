<template>
    <div>
        <div class="kw">
            <transition-group
                v-if="uniqKeywords.length > 0"
                class="keywords"
                name="keyword-list"
                tag="ul"
            >
                <RecipeKeyword
                    v-for="keywordObj in selectedKeywords"
                    :key="keywordObj.name"
                    :name="keywordObj.name"
                    :count="keywordObj.count"
                    :title="t('cookbook', 'Toggle keyword')"
                    class="keyword active"
                    @keyword-clicked="keywordClicked(keywordObj)"
                />
                <RecipeKeyword
                    v-for="keywordObj in selectableKeywords"
                    :key="keywordObj.name"
                    :name="keywordObj.name"
                    :count="keywordObj.count"
                    :title="t('cookbook', 'Toggle keyword')"
                    class="keyword"
                    @keyword-clicked="keywordClicked(keywordObj)"
                />
                <RecipeKeyword
                    v-for="keywordObj in unavailableKeywords"
                    :key="keywordObj.name"
                    :name="keywordObj.name"
                    :count="keywordObj.count"
                    :title="
                        // prettier-ignore
                        t('cookbook','Keyword not contained in visible recipes')
                    "
                    class="keyword disabled"
                    @keyword-clicked="keywordClicked(keywordObj)"
                />
            </transition-group>
        </div>
        <div id="recipes-submenu" class="recipes-submenu-container">
            <Multiselect
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
                <router-link :to="'/recipe/' + recipeObj.recipe.recipe_id">
                    <lazy-picture
                        v-if="recipeObj.recipe.imageUrl"
                        class="recipe-thumbnail"
                        :lazy-src="recipeObj.recipe.imageUrl"
                        :blurred-preview-src="
                            recipeObj.recipe.imagePlaceholderUrl
                        "
                        :width="105"
                        :height="105"
                    />
                    <div class="recipe-info-container">
                        <span class="recipe-title">{{
                            recipeObj.recipe.name
                        }}</span>
                        <div
                            v-if="orderBy"
                            class="recipe-info-container-bottom"
                        >
                            <span
                                v-if="
                                    formatDateTime(
                                        recipeObj.recipe.dateCreated
                                    ) != null
                                "
                                class="recipe-date"
                                >{{
                                    formatDateTime(recipeObj.recipe.dateCreated)
                                }}
                            </span>
                            <span
                                v-if="
                                    recipeObj.recipe.dateModified !==
                                        recipeObj.recipe.dateCreated &&
                                    formatDateTime(
                                        recipeObj.recipe.dateModified
                                    ) != null
                                "
                                class="recipe-date"
                                >{{
                                    formatDateTime(
                                        recipeObj.recipe.dateModified
                                    )
                                }}
                            </span>
                        </div>
                    </div>
                </router-link>
            </li>
        </ul>
    </div>
</template>

<script>
import moment from "@nextcloud/moment"
import Multiselect from "@nextcloud/vue/dist/Components/Multiselect"
import LazyPicture from "./LazyPicture.vue"
import RecipeKeyword from "./RecipeKeyword.vue"

export default {
    name: "RecipeList",
    components: {
        LazyPicture,
        Multiselect,
        RecipeKeyword,
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
         * An array of sorted and unique keywords over all the recipes
         */
        uniqKeywords() {
            function uniqFilter(value, index, self) {
                return self.indexOf(value) === index
            }
            const rawKWs = [...this.rawKeywords]
            return rawKWs.sort().filter(uniqFilter)
        },
        /**
         * An array of objects that contain the keywords plus a count of recipes associated with these keywords
         */
        keywordsWithCount() {
            const $this = this
            return this.uniqKeywords
                .map((kw) => ({
                    name: kw,
                    count: $this.rawKeywords.filter((kw2) => kw === kw2).length,
                }))
                .sort((k1, k2) => {
                    if (k1.count !== k2.count) {
                        // Distinguish by number
                        return k2.count - k1.count
                    }
                    // Distinguish by keyword name
                    return k1.name.toLowerCase() > k2.name.toLowerCase()
                        ? 1
                        : -1
                })
        },
        /**
         * An array of keyword objects that are currently in use for filtering
         */
        selectedKeywords() {
            return this.keywordsWithCount.filter((kw) =>
                this.keywordFilter.includes(kw.name)
            )
        },
        /**
         * An array of those keyword objects that are currently not in use for filtering
         */
        unselectedKeywords() {
            return this.keywordsWithCount.filter(
                (kw) => !this.selectedKeywords.includes(kw)
            )
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
                    return keywords.includes(kw.name)
                }

                return $this.selectedKeywords
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
        /**
         * An array of keywords that are yet unselected but some visible recipes are associated
         */
        selectableKeywords() {
            if (this.unselectedKeywords.length === 0) {
                return []
            }

            const $this = this
            return this.unselectedKeywords.filter((kw) =>
                $this.filteredRecipes
                    .map(
                        (r) =>
                            r.keywords &&
                            r.keywords.split(",").includes(kw.name)
                    )
                    .reduce((l, r) => l || r, false)
            )
        },
        /**
         * An array of known keywords that are not associated with any visible recipe
         */
        unavailableKeywords() {
            return this.unselectedKeywords.filter(
                (kw) => !this.selectableKeywords.includes(kw)
            )
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
            return this.recipes.map(makeObject, this)
        },
    },
    mounted() {
        this.$root.$off("applyRecipeFilter")
        this.$root.$on("applyRecipeFilter", (value) => {
            this.filters = value
        })
    },
    methods: {
        /**
         * Callback for click on keyword, add to or remove from list
         */
        keywordClicked(keyword) {
            const index = this.keywordFilter.indexOf(keyword.name)
            if (index > -1) {
                this.keywordFilter.splice(index, 1)
            } else {
                this.keywordFilter.push(keyword.name)
            }
        },
        /* The schema.org standard requires the dates formatted as Date (https://schema.org/Date)
         * or DateTime (https://schema.org/DateTime). This follows the ISO 8601 standard.
         */
        formatDateTime(dt) {
            if (!dt) return null
            const date = moment(dt, moment.ISO_8601)
            if (!date.isValid()) {
                return null
            }
            return date.format("L, LT").toString()
        },
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
.kw {
    width: 100%;
    max-height: 6.7em;
    padding: 0.1em;
    margin-bottom: 1em;
    overflow-x: hidden;
    overflow-y: scroll;
}

.keywords {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    padding: 0.5rem 1rem;
}

.keyword {
    display: inline-block;
}

.recipes-submenu-container {
    padding-left: 16px;
    margin-bottom: 0.75ex;
}

.recipe-sorting-dropdown {
    width: 300px;
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

.recipes li {
    width: 300px;
    max-width: 100%;
    margin: 0.5rem 1rem 1rem;
}
.recipes li a {
    display: block;
    height: 105px;
    border-radius: 3px;
    box-shadow: 0 0 3px #aaa;
}
.recipes li a:hover {
    box-shadow: 0 0 5px #888;
}

.recipes li .recipe-thumbnail {
    position: relative;
    overflow: hidden;
    width: 105px;
    height: 105px;
    background-color: #bebdbd;
    border-radius: 3px 0 0 3px;
    float: left;
}

.recipes li span {
    display: block;
}

.recipe-info-container {
    display: flex;
    height: 100%;
    flex-direction: column;
    padding: 0.5rem;
}

.recipe-title {
    overflow: hidden;
    flex-grow: 1;
    font-weight: 500;
    line-height: 2.6ex;
    text-overflow: ellipsis;
}

.recipe-date {
    height: 2.7ex;
    color: var(--color-text-lighter);
    font-size: 10px;
    line-height: 2ex;
}
</style>
