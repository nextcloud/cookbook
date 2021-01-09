<template>
    <div v-if="$store.state.recipe.image" :class="{ 'collapsed': collapsed, 'printable': $store.state.recipe.printImage }">
        <img :src="$store.state.recipe.imageUrl" @click="toggleCollapsed()">
    </div>
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
    mounted () {
    }
}
</script>

<style scoped>

	div {
	    display: block;
	    width: 100%;
	    text-align: center;
	    margin-bottom: 1rem;
	}
    img {
        cursor: pointer;
        width: 100%;
        max-width: 950px;
        background-color: #bebdbd;
    }
    div.collapsed {
        height: 40vh;
        overflow: hidden;
    }
    div.collapsed img {
        margin: 0 auto;
        margin-top: 20vh;
        transform: translateY(-50%);
        display: block;
    }

@media print {
    div:not(.printable) {
        display: none !important;
    }
}

</style>
