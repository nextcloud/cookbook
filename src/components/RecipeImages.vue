<template>
    <div
        v-if="$store.state.recipe.image"
        :class="{
            collapsed: collapsed,
            printable: $store.state.recipe.printImage,
        }"
    >
        <img :src="$store.state.recipe.imageUrl" @click="toggleCollapsed()" />
    </div>
</template>

<script>
export default {
    name: "RecipeImages",
    data() {
        return {
            collapsed: true,
        }
    },
    methods: {
        toggleCollapsed: function () {
            this.collapsed = !this.collapsed
        },
    },
    mounted() {},
}
</script>

<style scoped>
div {
    display: block;
    width: 100%;
    margin-bottom: 1rem;
    text-align: center;
}
img {
    width: 100%;
    max-width: 950px;
    background-color: #bebdbd;
    cursor: pointer;
}
div.collapsed {
    overflow: hidden;
    height: 40vh;
}
div.collapsed img {
    display: block;
    margin: 0 auto;
    margin-top: 20vh;
    transform: translateY(-50%);
}

@media print {
    div:not(.printable) {
        display: none !important;
    }
}
</style>
