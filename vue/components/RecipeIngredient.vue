<template>
    <li :class="{ 'header': isHeader() }">{{ displayIngredient }}</li>
</template>

<script>
export default {

    props: ['ingredient'],
    data () {
        return {
            headerPrefix: "## ",
        }
    },
    computed: {
        displayIngredient: function() {
            if (this.isHeader()) {
                return this.ingredient.substring(this.headerPrefix.length)
            }
            return this.ingredient
        },
    },
    methods: {
        isHeader: function() {
            if (this.ingredient.startsWith(this.headerPrefix)) {
                return true
            }
            return false
        }
    }

}
</script>

<style scoped>

li {
    margin-left: 1.25em;
}
    li.header {
        position: relative;
        left: -1.25em;
        margin-top: 0.25em;
        list-style-type: none;
        font-variant: small-caps;
    }

</style>
