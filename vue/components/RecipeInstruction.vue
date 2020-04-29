<template>
    <li :class="{ 'instruction': true, 'done': isDone }" @click="toggleDone">{{ instruction }}</li>
</template>

<script>
export default {
    name: 'RecipeInstruction',
    props: ['instruction'],
    data () {
        return {
            isDone: false
        }
    },
    methods: {
        toggleDone: function() {
            this.isDone = !this.isDone
        },
    },

}
</script>

<style scoped>

li {
    margin-left: 1em;
    cursor: pointer;
    counter-increment: instruction-counter;
    clear: both;
    margin-bottom: 2rem;
    /* I find this more convenient than a ln2br-function */
    white-space: pre-line;
}
    li:before {
        content: counter(instruction-counter);
        float: left;
        margin: 0 1rem 1rem 0;
        height: 36px;
        width: 36px;
        border-radius: 50%;
        border: 1px solid var(--color-border-dark);
        outline: none;
        background-repeat: no-repeat;
        background-position: center;
        background-color: var(--color-background-dark);
        line-height: 36px;
        text-align: center;
        margin-top: -6px;
    }
        li:hover::before {
            border-color: var(--color-primary-element);
        }
        li.done::before {
            content: '✔';
        }
    li span,
    li input[type="checkbox"] {
        line-height: 1rem;
        margin: 0 0.5rem 0 0;
        padding: 0;
        height: auto;
        width: 1rem;
        display: inline-block;
        vertical-align: middle;
    }

</style>
