<template>
    <li>{{ keyword }}</li>
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
    margin-right: .75em;
    padding: 0px .5em;
    border: 1px solid #999999;
    border-radius: 4px;
}

</style>
