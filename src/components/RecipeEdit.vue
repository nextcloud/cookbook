<template>
    <div class="wrapper">
        <EditInputField :fieldName="'name'" :fieldType="'text'" :fieldLabel="t('Name')" />
        <EditInputField :fieldName="'description'" :fieldType="'text'" :fieldLabel="t('Description')" />
        <EditInputField :fieldName="'url'" :fieldType="'url'" :fieldLabel="t('URL')" />
        <EditImageField :fieldName="'image'" :fieldLabel="('Image')" />
        <EditTimeField :fieldName="'prepTime'" :fieldLabel="t('Preparation time')" />
        <EditTimeField :fieldName="'cookTime'" :fieldLabel="t('Cooking time')" />
        <EditTimeField :fieldName="'totalTime'" :fieldLabel="t('Total time')" />
        <EditInputField :fieldName="'recipeCategory'" :fieldType="'text'" :fieldLabel="t('Category')" />
        <EditInputField :fieldName="'keywords'" :fieldType="'rext'" :fieldLabel="t('Keywords (comma separated)')" />
        <EditInputField :fieldName="'recipeYield'" :fieldType="'number'" :fieldLabel="t('Servings')" />
        <EditInputGroup :fieldName="'tool'" :fieldType="'text'" :fieldLabel="t('Tools')" />
        <EditInputGroup :fieldName="'recipeIngredient'" :fieldType="'text'" :fieldLabel="t('Ingredients')" />
        <EditInputGroup :fieldName="'recipeInstructions'" :fieldType="'textarea'" :fieldLabel="t('Instructions')" />
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
                recipeCategory: '',
                keywords: '',
                recipeYield: '',
                tool: [],
                recipeIngredient: [],
                recipeInstructions: [],
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
        loadRecipeData: function() {
            if (!this.$store.state.recipe) {
                // Make the control row show that a recipe is loading
                this.$store.dispatch('setLoadingRecipe', {
                    recipe: -1
                })
            } else if (this.$store.state.recipe.id === parseInt(this.$route.params.id)) {
                // Make the control row show that the recipe is reloading
                this.$store.dispatch('setReloadingRecipe', {
                    recipe: this.$route.params.id
                })
            }
            let $this = this
            $.ajax({
                url: this.$window.baseUrl + '/api/recipes/'+this.$route.params.id,
                method: 'GET',
                data: null,
            }).done(function (recipe) {
                $this.$store.dispatch('setRecipe', { recipe: recipe })
                $this.setup()
            }).fail(function(e) {
                alert($this.t('Loading recipe failed'))
                // Disable loading indicator
                if ($this.$store.state.loadingRecipe) {
                    $this.$store.dispatch('setLoadingRecipe', { recipe: 0 })
                } else if ($this.$store.state.reloadingRecipe) {
                    $this.$store.dispatch('setReloadingRecipe', { recipe: 0 })
                }
                // Browse to new recipe creation
                $this.$window.goTo('/recipe/create')
            })
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
        save: function() {
            this.$store.dispatch('setSavingRecipe', { saving: true })
            let $this = this
            if (this.recipe.id) {
                // Update existing recipe
                $.ajax({
                    url: this.$window.baseUrl + '/api/recipes/'+this.recipe.id,
                    method: 'PUT',
                    data: this.recipe,
                }).done(function (recipe) {
                    $this.$store.dispatch('setSavingRecipe', { saving: false })
                    $this.$window.goTo('/recipe/'+recipe)
                    // Refresh navigation to display changes
                    $this.$root.$emit('refreshNavigation')
                }).fail(function(e) {
                    $this.$store.dispatch('setSavingRecipe', { saving: false })
                    alert($this.t('Recipe could not be saved'))
                })
            } else {
                // Create a new recipe
                $.ajax({
                    url: this.$window.baseUrl + '/api/recipes',
                    method: 'POST',
                    data: this.recipe,
                }).done(function (recipe) {
                    $this.$store.dispatch('setSavingRecipe', { saving: false })
                    $this.$window.goTo('/recipe/'+recipe)
                    // Refresh navigation to display changes
                    $this.$root.$emit('refreshNavigation')
                }).fail(function(e) {
                    $this.$store.dispatch('setSavingRecipe', { saving: false })
                    alert($this.t('Recipe could not be saved'))
                })
            }
        },
        setup: function() {
            if (this.$route.params.id) {
                // Load the recipe from store and make edits to a local copy first
                this.recipe = { ...this.$store.state.recipe }
                // Parse time values
                let timeComps = this.recipe.prepTime.match(/PT(\d+?)H(\d+?)M/)
                if (timeComps) {
                    this.prepTime = [timeComps[1], timeComps[2]]
                }
                timeComps = this.recipe.cookTime.match(/PT(\d+?)H(\d+?)M/)
                if (timeComps) {
                    this.cookTime = [timeComps[1], timeComps[2]]
                }
                timeComps = this.recipe.totalTime.match(/PT(\d+?)H(\d+?)M/)
                if (timeComps) {
                    this.totalTime = [timeComps[1], timeComps[2]]
                }
                // Always set the active page last!
                this.$store.dispatch('setPage', { page: 'edit' })
            } else {
                this.recipe = this.recipeInit
                this.prepTime = [0, 0]
                this.cookTime = [0, 0]
                this.totalTime = [0, 0]
                this.$store.dispatch('setPage', { page: 'create' })
            }
        },
    },
    mounted () {
        // Store the initial recipe configuration for possible later use
        if (this.recipeInit === null) {
            this.recipeInit = this.recipe
        }
        // Register save method hook for access from the controls components
        // The event hookmust first be destroyed to avoid it from firing multiple
        // times if the same component is loaded again
        this.$root.$off('saveRecipe')
        this.$root.$on('saveRecipe', () => {
            this.save()
        })
        // Register data load method hook for access from the controls components
        this.$root.$off('reloadRecipeEdit')
        this.$root.$on('reloadRecipeEdit', () => {
            this.loadRecipeData()
        })
    },
    // We can check if the user has browsed from the same recipe's view to this
    // edit and save some time by not reloading the recipe data, leading to a
    // more seamless experience.
    // This assumes that the data has not been changed some other way between
    // loading the view component and loading this edit component. If that is
    // the case, the user can always manually reload by clicking the breadcrumb.
    beforeRouteEnter (to, from, next) {
        if (window.isSameItemInstance(from.fullPath, to.fullPath)) {
            next(vm => { vm.setup() })
        } else {
            if (to.params && to.params.id) {
                next(vm => { vm.loadRecipeData() })
            } else {
                next(vm => { vm.setup() })
            }
        }
    },
    /**
     * This is one tricky feature of Vue router. If different paths lead to
     * the same component (such as '/recipe/create' and '/recipe/xxx/edit
     * or /recipe/xxx/edit and /recipe/yyy/edit)', the view will not automatically
     * reload. So we have to check for these conditions and reload manually.
     * This can also be used to confirm that the user wants to leave the page
     * if there are unsaved changes.
     */
    beforeRouteLeave (to, from, next) {
        // beforeRouteLeave is called when the static route changes.
        // We have to check if the target component stays the same and reload.
        // However, we should not reload if the component changes; otherwise
        // reloaded data may overwrite the data loaded at the target component
        // which will at the very least result in incorrect breadcrumb path!
        next()
        // Check if we should reload the component content
        if (this.$window.shouldReloadContent(from.fullPath, to.fullPath)) {
            this.setup()
        }
    },
    beforeRouteUpdate (to, from, next) {
        // beforeRouteUpdate is called when the static route stays the same
        next()
        // Check if we should reload the component content
        if (this.$window.shouldReloadContent(from.fullPath, to.fullPath)) {
            this.setup()
        }
    },

}
</script>

<style scoped>

.wrapper {
    width: 100%;
    padding: 1rem;
}

/* This is not used anywhere at the moment, but left here for future reference
form fieldset ul label input[type="checkbox"] {
    margin-left: 1em;
    vertical-align: middle;
    cursor: pointer;
} */

</style>
