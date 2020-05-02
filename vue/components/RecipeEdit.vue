<template>
    <div class="wrapper">
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
            // Initialize the recipe schema, otherwise v-models in child components may not work
            recipe: {
                id: 0,
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
            // This will hold the above configuration after recipe is loaded, so we don't have to
            // keep it up to date in multiple places if it changes later
            recipeInit: null,
            // These are helper variables
            prepTime: [0, 0],
            cookTime: [0, 0],
            totalTime: [0, 0],
        }
    },
    watch: {
        prepTime () {
            let hours = this.prepTime[0].toString().padStart(2, '0')
            let mins = this.prepTime[1].toString().padStart(2, '0')
            this.recipe.prepTime = 'PT' + hours + 'H' + mins + 'M'
        },
        cookTime () {
            let hours = this.cookTime[0].toString().padStart(2, '0')
            let mins = this.cookTime[1].toString().padStart(2, '0')
            this.recipe.cookTime = 'PT' + hours + 'H' + mins + 'M'
        },
        totalTime () {
            let hours = this.totalTime[0].toString().padStart(2, '0')
            let mins = this.totalTime[1].toString().padStart(2, '0')
            this.recipe.totalTime = 'PT' + hours + 'H' + mins + 'M'
        },
    },
    methods: {
        addEntry: function(field) {
            this.recipe[field].push('')
        },
        deleteEntry: function(field, index) {
            this.recipe[field].splice(index, 1)
        },
        moveEntryDown: function(field, index) {
            if (index >= this.recipe[field].length - 1) {
                // Already at the send of array
                return
            }
            let entry = this.recipe[field].splice(index, 1)
            if (index + 1 < this.recipe[field].length) {
                this.recipe[field].splice(index + 1, 0, entry)
            } else {
                this.recipe[field].push(entry)
            }
        },
        moveEntryUp: function(field, index) {
            if (index < 1) {
                // Already at the start of array
                return
            }
            let entry = this.recipe[field].splice(index, 1)
            this.recipe[field].splice(index - 1, 0, entry)
        },
        setup: function() {
            // Store the initial recipe configuration for possible later use
            if (this.recipeInit === null) {
                this.recipeInit = this.recipe
            }
            if (this.$route.params.id) {
                let $this = this
                $.ajax({
                    url: this.$window.baseUrl + '/api/recipes/'+this.$route.params.id,
                    method: 'GET',
                    data: null,
                }).done(function (recipe) {
                    //console.log(recipe) // Testing
                    // Parse time values
                    let timeComps = recipe.prepTime.match(/PT(\d+?)H(\d+?)M/)
                    if (timeComps) {
                        $this.prepTime = [timeComps[1], timeComps[2]]
                    }
                    timeComps = recipe.cookTime.match(/PT(\d+?)H(\d+?)M/)
                    if (timeComps) {
                        $this.cookTime = [timeComps[1], timeComps[2]]
                    }
                    timeComps = recipe.totalTime.match(/PT(\d+?)H(\d+?)M/)
                    if (timeComps) {
                        $this.totalTime = [timeComps[1], timeComps[2]]
                    }
                    $this.recipe = recipe
                    // Always set the active page last!
                    $this.$store.dispatch('setPage', 'edit')
                }).fail(function(e) {
                    alert($this.$t('Loading recipe failed'))
                    $this.$store.dispatch('setPage', 'edit')
                })
            } else {
                this.recipe = this.recipeInit
                this.prepTime = [0, 0]
                this.cookTime = [0, 0]
                this.totalTime = [0, 0]
                this.$store.dispatch('setPage', 'create')
            }
        },
        test: function() {
            let $this = this
            if (this.recipe.id) {
                // Update existing recipe
                $.ajax({
                    url: this.$window.baseUrl + '/api/recipes/'+this.recipe.id,
                    method: 'PUT',
                    data: this.recipe,
                }).done(function (recipe) {
                    $this.$window.goTo('/recipe/'+recipe)
                }).fail(function(e) {
                    alert($this.$t('Recipe could not be saved'))
                })
            } else {
                // Create a new recipe
                $.ajax({
                    url: this.$window.baseUrl + '/api/recipes',
                    method: 'POST',
                    data: this.recipe,
                }).done(function (recipe) {
                    $this.$window.goTo('/recipe/'+recipe)
                }).fail(function(e) {
                    alert($this.$t('Recipe could not be saved'))
                })
            }
        }
    },
    mounted () {
        this.setup()
    },
    /**
     * This is one tricky feature of Vue router. If different paths lead to
     * the same component (such as '/recipe/create' and '/recipe/xxx/edit)',
     * the view may not automatically reload. So we have to force it.
     * This can also be used to confirm that the user wants to leave the page
     * if there are unsaved changes.
     */
    beforeRouteLeave (to, from, next) {
        // Move to next route as expected
        next()
        // Reload view
        this.setup()
    },

}
</script>

<style scoped>

.wrapper {
    width: 100%;
    padding: 0.5rem 1rem 1rem;
}

</style>
