<template>
    <ul>
        <li v-for="result in results" :key="result.id">
            <router-link :to="'/recipe/'+result.id">
                <img v-if="result.imageUrl" :src="result.imageUrl">
                <span>{{ result.name }}</span>
            </router-link>
        </li>
    </ul>
</template>

<script>
export default {
    name: "Search",
    props: ['query'],
    data () {
        return {
            results: [],
        }
    },
    methods: {
    },
    mounted () {
        if (this.query === 'name') {
            // Search by name
            console.log("Recipe name search for "+this.$route.params.value)
        }
        if (this.query === 'tag') {
            // Search by tag
            console.log("Tag search for "+this.$route.params.value)
        }
        if (this.query === 'cat') {
            // Search by category
            console.log("Category search for "+this.$route.params.value)
        } else {
            // Something else?
        }
    },
}
</script>

<style scoped>

ul {
    display: flex;
    flex-wrap: wrap;
    flex-direction: row;
    width: 100%;
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
