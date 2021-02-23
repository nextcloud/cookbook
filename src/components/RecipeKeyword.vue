<template>
    <a v-on:click="clicked" ref="link">
        <li>
            <span>{{ name }}</span>
            <span v-if="count != null" class="count">({{ count }})</span>
        </li>
    </a>
</template>

<script>
export default {
    name: "RecipeKeyword",
    props: {
        name: {
            type: String,
            required: true,
        },
        count: {
            type: Number,
            default: null,
        },
    },
    data() {
        return {}
    },
    computed: {},
    methods: {
        clicked() {
            if (!this.$refs.link.classList.contains("disabled")) {
                this.$emit("keyword-clicked")
            }
        },
    },
}
</script>

<style scoped>
li {
    display: inline-block;
    padding: 0 0.5em;
    border: 1px solid var(--color-border-dark);
    margin-right: 0.3em;
    margin-bottom: 0.3em;
    border-radius: var(--border-radius-pill);

    /* prevent text selection - doesn't look good */
    user-select: none; /* Standard */
}

li .count {
    margin-left: 0.35em;
    color: var(--color-text-light);
    font-size: 0.8em;
}

.active li {
    background-color: var(--color-primary);
    color: var(--color-primary-text);
}

.active li .count {
    color: var(--color-primary-text);
}

.disabled li {
    border-color: var(--color-border);
    background-color: #fff;
    color: var(--color-border);
}

.disabled li .count {
    color: var(--color-border);
}

li:hover,
.active li:hover {
    border: 1px solid var(--color-primary);
}

.disabled li:hover {
    border-color: var(--color-border);
    cursor: default;
}

.disabled :hover {
    cursor: default;
}
</style>
