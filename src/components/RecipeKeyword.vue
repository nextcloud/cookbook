<template>
    <router-link :to="'/tags/' + keyword"><li>{{ keyword }}</li></router-link>
</template>

<script>
export default {
    name: 'RecipeKeyword',
    props: ['keyword'],
    data () {
        return {
        };
    },
    computed: {
        
    },
    methods: {

    }

}
</script>

<style scoped>

li {
    display: inline-block;
    margin-right: .3em;
    margin-bottom: .3em;
    padding: 0px .5em;
    border: 1px solid #999999;
    border-radius: 4px;
}

li:hover {
    background-color: #EEEEEE;
}

</style>
