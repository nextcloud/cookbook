<template>
    <li :class="{ done: isDone }" @click="toggleDone">
        <VueShowdown :markdown="instruction" class="markdown-instruction" />
    </li>
</template>

<script>
export default {
    name: "RecipeInstruction",
    props: {
        /* Instruction HTML string to display. Content should be sanitized.
         */
        instruction: {
            type: String,
            default: "",
        },
    },
    data() {
        return {
            isDone: false,
        }
    },
    methods: {
        toggleDone() {
            this.isDone = !this.isDone
        },
    },
}
</script>

<style scoped>
li {
    position: relative;
    padding-left: calc(36px + 1rem);
    margin-bottom: 2rem;
    clear: both;
    counter-increment: instruction-counter;
    cursor: pointer;
    white-space: pre-line;
}

li::before {
    position: absolute;
    top: 0;
    left: 0;
    width: 36px;
    height: 36px;
    border: 1px solid var(--color-border-dark);
    background-color: var(--color-background-dark);
    background-position: center;
    background-repeat: no-repeat;
    border-radius: 50%;
    content: counter(instruction-counter);
    line-height: 36px;
    outline: none;
    text-align: center;
}

li:hover::before {
    border-color: var(--color-primary-element);
}

.done::before {
    content: "✔";
}

li span,
li input[type="checkbox"] {
    display: inline-block;
    width: 1rem;
    height: auto;
    padding: 0;
    margin: 0 0.5rem 0 0;
    line-height: 1rem;
    vertical-align: middle;
}

.markdown-instruction:deep(ol > li) {
    list-style-type: numbered;
}

.markdown-instruction:deep(ul > li) {
    list-style-type: disc;
}

.markdown-instruction:deep(ol > li),
.markdown-instruction:deep(ul > li) {
    margin-left: 20px;
}

.markdown-instruction:deep(a) {
    text-decoration: underline;
}
</style>
