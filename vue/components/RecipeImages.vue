<template>
    <header v-if="$store.state.recipe.image" :class="{ 'collapsed': collapsed, 'printable': $store.state.recipe.print_image }">
        <img :src="$store.state.recipe.image_url" @click="toggleCollapsed()">
    </header>
</template>

<script>
export default {
    name: 'RecipeImages',
    data () {
        return {
            collapsed: true,
        }
    },
    methods: {
        toggleCollapsed: function() {
            this.collapsed = !this.collapsed
        },
    },
}
</script>

<style scoped>

header {
    display: block;
    width: 100%;
    text-align: center;
    margin-bottom: 1rem;
}
    img {
        cursor: pointer;
        max-width: 100%;
    }
    header.collapsed {
        flex-basis: 100%;
        height: 40vh;
        overflow: hidden;
    }
        header.collapsed img {
            margin: 0 auto;
            margin-top: 20vh;
            transform: translateY(-50%);
            display: block;
        }

@media print {
    header:not(.printable) {
        display: none !important;
    }
}

</style>
