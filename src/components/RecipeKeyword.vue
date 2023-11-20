<template>
    <a ref="link" @click="clicked">
        <li>
            <span>{{ name }}</span>
            <span v-if="count != null" class="count">({{ count }})</span>
        </li>
    </a>
</template>

<script setup>
import { ref } from 'vue';

const emit = defineEmits(['keyword-clicked']);

defineProps({
    name: {
        type: String,
        required: true,
    },
    count: {
        type: Number,
        default: null,
    },
});

/**
 * @type {import('vue').Ref<HTMLElement | null>}
 */
const link = ref(null);

const clicked = () => {
    if (!link.value.classList.contains('disabled')) {
        emit('keyword-clicked');
    }
};
</script>

<script>
export default {
    name: 'RecipeKeyword',
};
</script>

<style scoped>
li {
    display: inline-block;
    padding: 0 0.5em;
    border: 1px solid var(--color-border-dark);
    border-radius: var(--border-radius-pill);
    margin-right: 0.3em;
    margin-bottom: 0.3em;

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
