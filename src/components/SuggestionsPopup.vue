<template>
    <div
        class="suggestions-popup"
        :style="{ left: `${offset.left}px`, top: `${offset.top}px` }"
    >
        <ul>
            <li
                v-for="(option, i) in suggestionOptions"
                :key="option.recipe_id"
                class="item"
                :class="{ focused: i === focusIndex }"
            >
                <a
                    class="link"
                    href="#"
                    :data-id="option.recipe_id"
                    @click="handleClick"
                >
                    {{ option.title }}
                </a>
            </li>
        </ul>
    </div>
</template>

<script>
export default {
    name: "SuggestionsPopup",
    props: {
        suggestionOptions: {
            type: Array,
            default: () => [],
        },
        offset: {
            type: Object,
            default: () => ({ top: 0, left: 0 }),
        },
        focusIndex: {
            type: Number,
            default: 0,
        },
    },
    methods: {
        handleClick(e) {
            e.preventDefault()
            e.stopPropagation()
            this.$parent.$emit("suggestions-selected", {
                recipe_id: e.target.getAttribute("data-id"),
            })
        },
    },
}
</script>

<style scoped>
.suggestions-popup {
    position: absolute;
    z-index: 2;
    border: 1px solid var(--color-background-darker);
    background-color: var(--color-main-background);
    border-radius: 5px;
}

.item {
    border-bottom: 1px solid var(--color-background-darker);
}

.link {
    display: block;
    width: 100%;
    padding: 0.2em;
}

.link:hover,
.focused > .link {
    background-color: var(--color-primary);
    color: var(--color-primary-text);
}
</style>
