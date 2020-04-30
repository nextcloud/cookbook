<template>
    <ul>
        <li v-for="recipe in recipes" :key="recipe.id">
            <router-link :to="'/recipe/'+recipe.id">
                <img v-if="recipe.imageUrl" :src="recipe.imageUrl">
                <span>{{ recipe.name }}</span>
            </router-link>
        </li>
    </ul>
</template>

<script>
export default {
    name: 'Index',
    data () {
        return {
            recipes: []
        }
    },
    mounted () {
        this.recipes = [
            { id: 1, name: "Test recipe 1", iamgeUrl: false },
            { id: 2, name: "Test recipe 2", iamgeUrl: false },
            { id: 3, name: "Test recipe 3", iamgeUrl: false },
            { id: 4, name: "Test recipe 4", iamgeUrl: false },
            { id: 5, name: "Test recipe 5", iamgeUrl: false },
            { id: 6, name: "Test recipe 6", iamgeUrl: false },
            { id: 7, name: "Test recipe 7", iamgeUrl: false },
            { id: 8, name: "Test recipe 8", iamgeUrl: false },
            { id: 9, name: "Test recipe 9", iamgeUrl: false },
        ]
        // Set page last!
        this.$store.dispatch('setPage', { page: 'index' })
    },
}
</script>

<style scoped>

ul {
    display: flex;
    flex-wrap: wrap;
    flex-direction: row;
}

    ul li {
        width: 300px;
        max-width: 100%;
        margin: 0.5rem 1rem 1rem;
    }
        ul li a {
            display: block;
            height: 105px;
            box-shadow: 0 0 3px #AAA;
            border-radius: 3px;
        }
        ul li a:hover {
            box-shadow: 0 0 5px #888;
        }

        ul li img {
            float: left;
            height: 105px;
            border-radius: 3px 0 0 3px;
        }

        ul li span {
            display: block;
            padding: 0.5rem 0.5em 0.5rem calc(105px + 0.5rem);
        }

</style>
