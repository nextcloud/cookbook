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
        <div v-if="uniqKeywords.length > 0" class="settings-buttons">
            <button
                class="settings-button ordering-button"
                :title="orderButtonTitle"
                @click="toggleOrderCriterion"
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

<script setup>
import { computed, ref, watch } from 'vue';
import RecipeKeyword from '../RecipeKeyword.vue';

const emit = defineEmits(['input']);
const props = defineProps({
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
});

/**
 * @type {import('vue').Ref<boolean>}
 */
const isMaximized = ref(false);
/**
 * @type {import('vue').Ref<boolean>}
 */
const isOrderedAlphabetically = ref(false);
/**
 * @type {import('vue').Ref<Array.<string>>}
 */
const selectedKeywordsBuffer = ref([]);

// Computed properties
/** Text shown on the button for ordering the keywords */
const orderButtonText = computed(() => {
    return isOrderedAlphabetically.value ? '321' : 'ABC';
});
/** Title of the button for ordering the keywords */
const orderButtonTitle = computed(() => {
    return isOrderedAlphabetically.value
        ? t('cookbook', 'Order keywords by number of recipes')
        : t('cookbook', 'Order keywords alphabetically');
});
/**
 * Which icon to show for the size-toggle button
 */
const toggleSizeIcon = computed(() => {
    return isMaximized.value ? 'icon-triangle-n' : 'icon-triangle-s';
});
/**
 * An array of sorted and unique keywords over all the recipes
 */
const uniqKeywords = computed(() => {
    function uniqFilter(value, index, self) {
        return self.indexOf(value) === index;
    }
    const rawKWs = [...props.keywords];
    return rawKWs.sort().filter(uniqFilter);
});
/**
 * An array of objects that contain the keywords plus a count of recipes associated with these keywords
 */
const keywordsWithCount = computed(() => {
    return uniqKeywords.value
        .map((kw) => ({
            name: kw,
            count: props.keywords.filter((kw2) => kw === kw2).length,
        }))
        .sort((k1, k2) => {
            if (isOrderedAlphabetically.value) {
                return k1.name.toLowerCase() > k2.name.toLowerCase()
                    ? 1
                    : -1;
            }
            // else: order by number of recipe with this keyword (decreasing)
            if (k1.count !== k2.count) {
                // Distinguish by number
                return k2.count - k1.count;
            }
            // Distinguish by keyword name
            return k1.name.toLowerCase() > k2.name.toLowerCase()
                ? 1
                : -1;
        });
});
/**
 * An array of keywords that are yet unselected but some visible recipes are associated
 */
const selectableKeywords = computed(() => {
    if (unselectedKeywords.value.length === 0) {
        return [];
    }

    return unselectedKeywords.value.filter((kw) =>
        props.filteredRecipes
            .map(
                (r) =>
                    r.keywords &&
                    r.keywords.split(',').includes(kw.name),
            )
            .reduce((l, r) => l || r, false),
    );
});
/**
 * An array of keyword objects that are currently in use for filtering
 */
const selectedKeywordsWithCount = computed(() => {
    return keywordsWithCount.value.filter((kw) =>
        selectedKeywordsBuffer.value.includes(kw.name),
    );
});
/**
 * An array of known keywords that are not associated with any visible recipe
 */
const unavailableKeywords = computed(() => {
    return unselectedKeywords.value.filter(
        (kw) => !selectableKeywords.value.includes(kw),
    );
});
/**
 * An array of those keyword objects that are currently not in use for filtering
 */
const unselectedKeywords = computed(() => {
    return keywordsWithCount.value.filter(
        (kw) => !selectedKeywordsWithCount.value.includes(kw),
    );
});

// Watchers
/**
 * Watch array of selected keywords for changes
 */
watch(() => props.value,
    () => {
        selectedKeywordsBuffer.value = props.value.slice()
    },
    { deep: true }
);

// Methods
/**
 * Callback for click on keyword, add to or remove from list
 */
const keywordClicked = (keyword) => {
    const index = selectedKeywordsBuffer.value.indexOf(keyword.name)
    if (index > -1) {
        selectedKeywordsBuffer.value.splice(index, 1);
    } else {
        selectedKeywordsBuffer.value.push(keyword.name);
    }
    emit('input', selectedKeywordsBuffer.value);
};
const toggleCloudSize = () => {
    isMaximized.value = !isMaximized.value;
};
const toggleOrderCriterion = () => {
    isOrderedAlphabetically.value = !isOrderedAlphabetically.value;
};
</script>

<script>
export default {
    name: 'RecipeListKeywordCloud'
};
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
