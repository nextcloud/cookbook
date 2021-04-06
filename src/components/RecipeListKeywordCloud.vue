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
        <button class="resize-button"
                :class="toggleSizeIcon"
                @click="toggleCloudSize"></button>
    </div>
</template>

<script>
import RecipeKeyword from "./RecipeKeyword.vue"

export default {
    name: "RecipeListKeywordCloud",
    components: {
      RecipeKeyword
    },
    props: {
        keywords: {
            type: Array,
            default: () => [],
        },
        /** List of selected keywords */
        value: {
            type: Array,
            default: () => [],
        },
        filteredRecipes: {
            type: Array,
            default: () => [],
        },
    },
    data(){
        return {
            isMaximized: false,
            selectedKeywordsBuffer: []
        }
    },
    computed: {
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
    watch:{
        value: {
            handler() {
                this.selectedKeywordsBuffer = this.value.slice()
            },
            deep: true,
        }
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
        }
    }
}
</script>

<style scoped>

.kw-container {
    position: relative;
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

.resize-button {
    position: absolute;
    right: 10px;
    bottom: -8px;
}
</style>