<template>
    <div>
        <EditInputField :fieldName="'name'" :fieldType="'text'" :fieldLabel="$t('Name')" />
        <EditInputField :fieldName="'description'" :fieldType="'text'" :fieldLabel="$t('Description')" />
        <EditInputField :fieldName="'url'" :fieldType="'url'" :fieldLabel="$t('URL')" />
        <EditImageField :fieldName="'image'" :fieldLabel="$t('Name')" />
        <EditTimeField :fieldName="'prepTime'" :fieldLabel="$t('Preparation time')" />
        <EditTimeField :fieldName="'cookTime'" :fieldLabel="$t('Cooking time')" />
        <EditTimeField :fieldName="'totalTime'" :fieldLabel="$t('Total time')" />
        <EditInputField :fieldName="'category'" :fieldType="'text'" :fieldLabel="$t('Category')" />
        <EditInputField :fieldName="'keywords'" :fieldType="'rext'" :fieldLabel="$t('Keywords (comma separated)')" />
        <EditInputField :fieldName="'servings'" :fieldType="'number'" :fieldLabel="$t('Servings')" />
        <EditInputGroup :fieldName="'tools'" :fieldType="'text'" :fieldLabel="$t('Tools')" />
        <EditInputGroup :fieldName="'ingredients'" :fieldType="'text'" :fieldLabel="$t('Ingredients')" />
        <EditInputGroup :fieldName="'instructions'" :fieldType="'textarea'" :fieldLabel="$t('Instructions')" />
        <button @click="test()">Test</button>
    </div>
</template>

<script>
import EditImageField from './EditImageField'
import EditInputField from './EditInputField'
import EditInputGroup from './EditInputGroup'
import EditTimeField from './EditTimeField'

export default {
    name: 'RecipeEdit',
    components: {
        EditImageField,
        EditInputField,
        EditInputGroup,
        EditTimeField,
    },
    props: ['id'],
    data () {
        return {
            recipe: {
                name: null,
                description: '',
                url: '',
                image: '',
                prepTime: '',
                cookTime: '',
                totalTime: '',
                category: '',
                keywords: '',
                servings: '',
                tools: [],
                ingredients: [],
                instructions: [],
            },
        }
    },
    methods: {
        test: function() {
            console.log(this.recipe)
        }
    },
    mounted () {
        // Testing
        //this.recipe =  {}
        if (this.id) {
            this.$store.dispatch('setPage', 'edit')
        } else {
            this.$store.dispatch('setPage', 'create')
        }
    },
}
</script>

<style>

</style>
