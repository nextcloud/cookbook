<template>
    <div class="kw-container">
        <div class="kw" :class="{ max: isMaximized }">
            <transition-group
                v-if="uniqKeywords.length > 0"
                class="keywords"
                name="keyword-list"
                tag="ul"
            >
                <RecipeKeyword
                    v-for="keywordObj in selectedKeywordsWithCount"
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
        <div
            v-if="uniqKeywords.length > 0"
            class="settings-buttons"
        >
            <button
                class="settings-button ordering-button"
                :title="orderButtonTitle"
                @click="toggleOrderCriterium"
            >
                {{ orderButtonText }}
            </button>
            <button
                class="settings-button"
                :class="toggleSizeIcon"
                :title="t('cookbook', 'Toggle keyword area size')"
                @click="toggleCloudSize"
            />
        </div>
    </div>
</template>

<script>
import RecipeKeyword from "./RecipeKeyword.vue"

export default {
    name: "RecipeListKeywordCloud",
    components: {
        RecipeKeyword,
    },
    props: {
        /** String-array of all available keywords */
        keywords: {
            type: Array,
            default: () => [],
        },
        /** String-array of selected keywords */
        value: {
            type: Array,
            default: () => [],
        },
        filteredRecipes: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            isMaximized: false,
            isOrderedAlphabetically: false,
            selectedKeywordsBuffer: [],
        }
    },
    computed: {
        /** Text shown on the button for ordering the keywords */
        orderButtonText() {
            return this.isOrderedAlphabetically ? "321" : "ABC"
        },
        /** Title of the button for ordering the keywords */
        orderButtonTitle() {
            return this.isOrderedAlphabetically
                ? t("cookbook", "Order keywords by number of recipes")
                : t("cookbook", "Order keywords alphabetically")
        },
        /**
         * Which icon to show for the size-toggle button
         */
        toggleSizeIcon() {
            return this.isMaximized ? "icon-triangle-n" : "icon-triangle-s"
        },
        /**
         * An array of sorted and unique keywords over all the recipes
         */
        uniqKeywords() {
            function uniqFilter(value, index, self) {
                return self.indexOf(value) === index
            }
            const rawKWs = [...this.keywords]
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
                    count: $this.keywords.filter((kw2) => kw === kw2).length,
                }))
                .sort((k1, k2) => {
                    if (this.isOrderedAlphabetically) {
                        return k1.name.toLowerCase() > k2.name.toLowerCase()
                            ? 1
                            : -1
                    }
                    // else: order by number of recipe with this keyword (decreasing)
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
         * An array of keyword objects that are currently in use for filtering
         */
        selectedKeywordsWithCount() {
            return this.keywordsWithCount.filter((kw) =>
                this.selectedKeywordsBuffer.includes(kw.name)
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
        /**
         * An array of those keyword objects that are currently not in use for filtering
         */
        unselectedKeywords() {
            return this.keywordsWithCount.filter(
                (kw) => !this.selectedKeywordsWithCount.includes(kw)
            )
        },
    },
    watch: {
        /**
         * Watch array of selected keywords for changes
         */
        value: {
            handler() {
                this.selectedKeywordsBuffer = this.value.slice()
            },
            deep: true,
        },
    },
    methods: {
        /**
         * Callback for click on keyword, add to or remove from list
         */
        keywordClicked(keyword) {
            const index = this.selectedKeywordsBuffer.indexOf(keyword.name)
            if (index > -1) {
                this.selectedKeywordsBuffer.splice(index, 1)
            } else {
                this.selectedKeywordsBuffer.push(keyword.name)
            }
            this.$emit("input", this.selectedKeywordsBuffer)
        },
        toggleCloudSize() {
            this.isMaximized = !this.isMaximized
        },
        toggleOrderCriterium() {
            this.isOrderedAlphabetically = !this.isOrderedAlphabetically
        },
    },
}
</script>

<style lang="scss" scoped>
.kw-container {
    position: relative;
    display: flex;
}

.kw {
    width: 100%;
    max-height: 6.7em;
    padding: 0.1em;
    margin-bottom: 1em;
    overflow-x: hidden;
    overflow-y: scroll;
}

.kw.max {
    max-height: 100vh;
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

.settings-buttons {
    display: flex;
    align-items: flex-start;
    padding: 0.5rem;

    .settings-button {
        min-height: 8px;
        font-size: 8px;
        vertical-align: bottom;
    }

    .ordering-button {
        padding: 2px 6px;
    }
}
</style>
