<template>
    <a v-on:click="clicked" ref="link"><li>{{ keyword }}</li></a>
</template>

<script>
export default {
    name: 'RecipeKeyword',
    props: ['keyword'],
    data () {
        return {
        }
    },
    computed: {
    },
    methods: {
        clicked() {
            if(!this.$refs.link.classList.contains('disabled')) {
                this.$emit('keyword-clicked')
            }
        }
    }
}
</script>

<style scoped>

li {
    display: inline-block;
    margin-right: .3em;
    margin-bottom: .3em;
    padding: 0px .5em;
    border: 1px solid var(--color-border-dark);
    border-radius: var(--border-radius-pill);

    /* prevent text selection - doesn't look good */
    -webkit-user-select: none; /* Safari */        
    -moz-user-select: none; /* Firefox */
    -ms-user-select: none; /* IE10+/Edge */
    user-select: none; /* Standard */
}

.active li {
    background-color: var(--color-primary);
    color: var(--color-primary-text);
}

.disabled li {
    background-color: #FFF;
    border-color: var(--color-border);
    color: var(--color-border);
}

.disabled li:hover {
    border-color: var(--color-border);
    cursor: default;
}

li:hover, .active li:hover {
    border: 1px solid var(--color-primary);
}
</style>
