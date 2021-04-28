<template>
    <div class="wrapper">
        <div class="overlay" :class="{ hidden: !overlayVisible }" />
        <EditInputField
            v-model="recipe['name']"
            :field-type="'text'"
            :field-label="t('cookbook', 'Name')"
        />
        <EditInputField
            v-model="recipe['description']"
            :field-type="'markdown'"
            :field-label="t('cookbook', 'Description')"
            :reference-popup-enabled="true"
        />
        <EditInputField
            v-model="recipe['url']"
            :field-type="'url'"
            :field-label="t('cookbook', 'URL')"
        />
        <EditImageField
            v-model="recipe['image']"
            :field-label="t('cookbook', 'Image')"
        />
        <EditTimeField
            v-model="prepTime"
            :field-label="t('cookbook', 'Preparation time')"
        />
        <EditTimeField
            v-model="cookTime"
            :field-label="t('cookbook', 'Cooking time')"
        />
        <EditTimeField
            v-model="totalTime"
            :field-label="t('cookbook', 'Total time')"
        />
        <EditMultiselect
            v-model="recipe['recipeCategory']"
            :field-label="t('cookbook', 'Category')"
            :placeholder="t('cookbook', 'Choose category')"
            :options="allCategories"
            :taggable="true"
            :multiple="false"
            :loading="isFetchingCategories"
            @tag="addCategory"
        />
        <EditMultiselect
            v-model="selectedKeywords"
            :field-label="t('cookbook', 'Keywords')"
            :placeholder="t('cookbook', 'Choose keywords')"
            :options="allKeywords"
            :taggable="true"
            :multiple="true"
            :tag-width="60"
            :loading="isFetchingKeywords"
            @tag="addKeyword"
        />
        <EditInputField
            v-model="recipe['recipeYield']"
            :field-type="'number'"
            :field-label="t('cookbook', 'Servings')"
        />
        <EditMultiselectInputGroup
            v-model="recipe['nutrition']"
            :field-label="t('cookbook', 'Nutrition Information')"
            :options="availableNutritionFields"
            :label-select-placeholder="t('cookbook', 'Pick option')"
        />
        <EditInputGroup
            v-model="recipe['tool']"
            :field-name="'tool'"
            :field-type="'text'"
            :field-label="t('cookbook', 'Tools')"
            :create-fields-on-newlines="true"
            :reference-popup-enabled="true"
        />
        <EditInputGroup
            v-model="recipe['recipeIngredient']"
            :field-name="'recipeIngredient'"
            :field-type="'text'"
            :field-label="t('cookbook', 'Ingredients')"
            :create-fields-on-newlines="true"
            :reference-popup-enabled="true"
        />
        <EditInputGroup
            v-model="recipe['recipeInstructions']"
            :field-name="'recipeInstructions'"
            :field-type="'textarea'"
            :field-label="t('cookbook', 'Instructions')"
            :create-fields-on-newlines="true"
            :show-step-number="true"
            :reference-popup-enabled="true"
        />
        <edit-multiselect-popup
            ref="referencesPopup"
            class="references-popup"
            :class="{ visible: referencesPopupFocused }"
            :options="allRecipeOptions"
            track-by="recipe_id"
            label="title"
            :loading="loadingRecipeReferences"
            :focused="referencesPopupFocused"
        />
    </div>
</template>

<script>
import axios from "@nextcloud/axios"
import Vue from "vue"
import EditImageField from "./EditImageField.vue"
import EditInputField from "./EditInputField.vue"
import EditInputGroup from "./EditInputGroup.vue"
import EditMultiselect from "./EditMultiselect.vue"
import EditMultiselectInputGroup from "./EditMultiselectInputGroup.vue"
import EditMultiselectPopup from "./EditMultiselectPopup.vue"
import EditTimeField from "./EditTimeField.vue"

export default {
    name: "RecipeEdit",
    components: {
        EditImageField,
        EditInputField,
        EditInputGroup,
        EditMultiselect,
        EditTimeField,
        EditMultiselectInputGroup,
        EditMultiselectPopup,
    },
    // We can check if the user has browsed from the same recipe's view to this
    // edit and save some time by not reloading the recipe data, leading to a
    // more seamless experience.
    // This assumes that the data has not been changed some other way between
    // loading the view component and loading this edit component. If that is
    // the case, the user can always manually reload by clicking the breadcrumb.
    beforeRouteEnter(to, from, next) {
        if (window.isSameItemInstance(from.fullPath, to.fullPath)) {
            next((vm) => {
                vm.setup()
            })
        } else if (to.params && to.params.id) {
            next((vm) => {
                vm.loadRecipeData()
            })
        } else {
            next((vm) => {
                vm.setup()
            })
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
    beforeRouteLeave(to, from, next) {
        // beforeRouteLeave is called when the static route changes.
        // Cancel the navigation, if the form has unsaved edits and the user did not
        // confirm leave. This prevents accidentally losing changes
        if (this.confirmStayInEditedForm()) {
            next(false)
        } else {
            // We have to check if the target component stays the same and reload.
            // However, we should not reload if the component changes; otherwise
            // reloaded data may overwrite the data loaded at the target component
            // which will at the very least result in incorrect breadcrumb path!
            next()
        }
        // Check if we should reload the component content
        if (this.$window.shouldReloadContent(from.fullPath, to.fullPath)) {
            this.setup()
        }
    },
    beforeRouteUpdate(to, from, next) {
        // beforeRouteUpdate is called when the static route stays the same
        next()
        // Check if we should reload the component content
        if (this.$window.shouldReloadContent(from.fullPath, to.fullPath)) {
            this.setup()
        }
    },
    props: {
        id: {
            type: String,
            default: "",
        },
    },
    data() {
        return {
            // Initialize the recipe schema, otherwise v-models in child components may not work
            recipe: {
                id: 0,
                name: null,
                description: "",
                url: "",
                image: "",
                prepTime: "",
                cookTime: "",
                totalTime: "",
                recipeCategory: "",
                keywords: "",
                recipeYield: "",
                tool: [],
                recipeIngredient: [],
                recipeInstructions: [],
                nutrition: {},
            },
            // This will hold the above configuration after recipe is loaded, so we don't have to
            // keep it up to date in multiple places if it changes later
            recipeInit: null,

            // ==========================
            // These are helper variables

            // Changes have been made to the initial values of the form
            formDirty: false,
            // the save button has been clicked
            savingRecipe: false,
            prepTime: { time: [0, 0], paddedTime: "" },
            cookTime: { time: [0, 0], paddedTime: "" },
            totalTime: { time: [0, 0], paddedTime: "" },
            allCategories: [],
            isFetchingCategories: true,
            isFetchingKeywords: true,
            allKeywords: [],
            selectedKeywords: [],
            allRecipes: [],
            availableNutritionFields: [
                {
                    key: "calories",
                    label: t("cookbook", "Calories"),
                    // prettier-ignore
                    placeholder: t("cookbook","E.g.: 450 kcal (amount & unit)"),
                },
                {
                    key: "carbohydrateContent",
                    label: t("cookbook", "Carbohydrate content"),
                    placeholder: t("cookbook", "E.g.: 2 g (amount & unit)"),
                },
                {
                    key: "cholesterolContent",
                    label: t("cookbook", "Cholesterol content"),
                    placeholder: t("cookbook", "E.g.: 2 g (amount & unit)"),
                },
                {
                    key: "fatContent",
                    label: t("cookbook", "Fat content"),
                    placeholder: t("cookbook", "E.g.: 2 g (amount & unit)"),
                },
                {
                    key: "fiberContent",
                    label: t("cookbook", "Fiber content"),
                    placeholder: t("cookbook", "E.g.: 2 g (amount & unit)"),
                },
                {
                    key: "proteinContent",
                    label: t("cookbook", "Protein content"),
                    placeholder: t("cookbook", "E.g.: 2 g (amount & unit)"),
                },
                {
                    key: "saturatedFatContent",
                    label: t("cookbook", "Saturated-fat content"),
                    placeholder: t("cookbook", "E.g.: 2 g (amount & unit)"),
                },
                {
                    key: "servingSize",
                    label: t("cookbook", "Serving size"),
                    // prettier-ignore
                    placeholder: t("cookbook","Enter serving size (volume or mass)"),
                },
                {
                    key: "sodiumContent",
                    label: t("cookbook", "Sodium content"),
                    placeholder: t("cookbook", "E.g.: 2 g (amount & unit)"),
                },
                {
                    key: "sugarContent",
                    label: t("cookbook", "Sugar content"),
                    placeholder: t("cookbook", "E.g.: 2 g (amount & unit)"),
                },
                {
                    key: "transFatContent",
                    label: t("cookbook", "Trans-fat content"),
                    placeholder: t("cookbook", "E.g.: 2 g (amount & unit)"),
                },
                {
                    key: "unsaturatedFatContent",
                    label: t("cookbook", "Unsaturated-fat content"),
                    placeholder: t("cookbook", "E.g.: 2 g (amount & unit)"),
                },
            ],
            referencesPopupFocused: false,
            popupContext: undefined,
            loadingRecipeReferences: true,
        }
    },
    computed: {
        allRecipeOptions() {
            return this.allRecipes.map((r) => ({
                recipe_id: r.recipe_id,
                title: `${r.recipe_id}: ${r.name}`,
            }))
        },
        categoryUpdating() {
            return this.$store.state.categoryUpdating
        },
        overlayVisible() {
            return (
                this.$store.state.loadingRecipe ||
                this.$store.state.reloadingRecipe ||
                (this.$store.state.categoryUpdating &&
                    this.$store.state.categoryUpdating ===
                        this.recipe.recipeCategory)
            )
        },
    },
    watch: {
        prepTime: {
            handler() {
                this.recipe.prepTime = this.prepTime.paddedTime
            },
            deep: true,
        },
        cookTime: {
            handler() {
                this.recipe.cookTime = this.cookTime.paddedTime
            },
            deep: true,
        },
        totalTime: {
            handler() {
                this.recipe.totalTime = this.totalTime.paddedTime
            },
            deep: true,
        },
        selectedKeywords: {
            deep: true,
            handler() {
                // convert keyword array to comma-separated string
                this.recipe.keywords = this.selectedKeywords.join()
            },
        },
        recipe: {
            deep: true,
            handler() {
                this.formDirty = true
            },
        },
    },
    mounted() {
        const $this = this

        // Store the initial recipe configuration for possible later use
        if (this.recipeInit === null) {
            this.recipeInit = this.recipe
        }
        // Register save method hook for access from the controls components
        // The event hookmust first be destroyed to avoid it from firing multiple
        // times if the same component is loaded again
        this.$root.$off("saveRecipe")
        this.$root.$on("saveRecipe", () => {
            this.save()
        })
        // Register data load method hook for access from the controls components
        this.$root.$off("reloadRecipeEdit")
        this.$root.$on("reloadRecipeEdit", () => {
            this.loadRecipeData()
        })
        this.$root.$off("categoryRenamed")
        this.$root.$on("categoryRenamed", (val) => {
            // Update selectable categories
            const idx = this.allCategories.findIndex((c) => c === val[1])
            if (idx >= 0) {
                Vue.set(this.allCategories, idx, val[0])
                // this.allCategories[idx] = val[0]
            }
            // Update selected category if the currently selected was renamed
            if (this.recipe.recipeCategory === val[1]) {
                // eslint-disable-next-line prefer-destructuring
                this.recipe.recipeCategory = val[0]
            }
        })
        // Register recipe-reference selection hook for showing popup when requested
        // from a child element
        this.$off("showRecipeReferencesPopup")
        this.$on("showRecipeReferencesPopup", (val) => {
            this.referencesPopupFocused = true
            this.popupContext = val
        })
        // Register hook when recipe reference has been selected in popup
        this.$off("ms-popup-selected")
        this.$on("ms-popup-selected", (opt) => {
            this.referencesPopupFocused = false
            this.popupContext.context.pasteString(`r/${opt.recipe_id} `)
        })
        // Register hook when recipe reference has been selected in popup
        this.$off("ms-popup-selection-canceled")
        this.$on("ms-popup-selection-canceled", () => {
            this.referencesPopupFocused = false
            this.popupContext.context.pasteCanceled()
        })
        this.savingRecipe = false

        // Load data for all recipes to be used in recipe-reference popup suggestions
        axios
            .get(`${this.$window.baseUrl}/api/recipes`)
            .then((response) => {
                $this.allRecipes = response.data
            })
            .catch((e) => {
                // eslint-disable-next-line no-console
                console.log(e)
            })
            .then(() => {
                // finally
                $this.loadingRecipeReferences = false
            })
    },
    beforeDestroy() {
        window.removeEventListener("beforeunload", this.beforeWindowUnload)
    },
    created() {
        window.addEventListener("beforeunload", this.beforeWindowUnload)
    },
    methods: {
        /**
         * Add newly created category and set as selected.
         */
        addCategory(newCategory) {
            this.allCategories.push(newCategory)
            this.recipe.recipeCategory = newCategory
        },
        /**
         * Add newly created keyword.
         */
        addKeyword(newKeyword) {
            this.allKeywords.push(newKeyword)
            this.selectedKeywords.push(newKeyword)
        },
        addEntry(field, index, content = "") {
            this.recipe[field].splice(index, 0, content)
        },
        beforeWindowUnload(e) {
            if (this.confirmStayInEditedForm()) {
                // Cancel the window unload event
                e.preventDefault()
                e.returnValue = ""
            }
        },
        confirmLeavingPage() {
            // eslint-disable-next-line no-alert
            return window.confirm(
                t(
                    "cookbook",
                    "You have unsaved changes! Do you still want to leave?"
                )
            )
        },
        confirmStayInEditedForm() {
            return (
                !this.savingRecipe &&
                this.formDirty &&
                !this.confirmLeavingPage()
            )
        },
        deleteEntry(field, index) {
            this.recipe[field].splice(index, 1)
        },
        /**
         * Fetch and display recipe categories
         */
        fetchCategories() {
            const $this = this
            axios
                .get(`${this.$window.baseUrl}/categories`)
                .then((response) => {
                    const json = response.data || []
                    $this.allCategories = []
                    for (let i = 0; i < json.length; i++) {
                        if (json[i].name !== "*") {
                            $this.allCategories.push(json[i].name)
                        }
                    }
                    $this.isFetchingCategories = false
                })
                .catch((e) => {
                    // eslint-disable-next-line no-alert
                    alert(t("cookbook", "Failed to fetch categories"))
                    if (e && e instanceof Error) {
                        throw e
                    }
                })
        },
        /**
         * Fetch and display recipe keywords
         */
        fetchKeywords() {
            const $this = this
            axios
                .get(`${this.$window.baseUrl}/keywords`)
                .then((response) => {
                    const json = response.data || []
                    if (json) {
                        $this.allKeywords = []
                        for (let i = 0; i < json.length; i++) {
                            if (json[i].name !== "*") {
                                $this.allKeywords.push(json[i].name)
                            }
                        }
                    }
                    $this.isFetchingKeywords = false
                })
                .catch((e) => {
                    // eslint-disable-next-line no-alert
                    alert(t("cookbook", "Failed to fetch keywords"))
                    if (e && e instanceof Error) {
                        throw e
                    }
                })
        },
        loadRecipeData() {
            if (!this.$store.state.recipe) {
                // Make the control row show that a recipe is loading
                this.$store.dispatch("setLoadingRecipe", {
                    recipe: -1,
                })
            } else if (
                this.$store.state.recipe.id ===
                parseInt(this.$route.params.id, 10)
            ) {
                // Make the control row show that the recipe is reloading
                this.$store.dispatch("setReloadingRecipe", {
                    recipe: this.$route.params.id,
                })
            }
            const $this = this
            axios
                .get(
                    `${this.$window.baseUrl}/api/recipes/${this.$route.params.id}`
                )
                .then((response) => {
                    const recipe = response.data
                    $this.$store.dispatch("setRecipe", { recipe })
                    $this.setup()
                })
                .catch(() => {
                    // eslint-disable-next-line no-alert
                    alert(t("cookbook", "Loading recipe failed"))
                    // Disable loading indicator
                    if ($this.$store.state.loadingRecipe) {
                        $this.$store.dispatch("setLoadingRecipe", { recipe: 0 })
                    } else if ($this.$store.state.reloadingRecipe) {
                        $this.$store.dispatch("setReloadingRecipe", {
                            recipe: 0,
                        })
                    }
                    // Browse to new recipe creation
                    $this.$window.goTo("/recipe/create")
                })
        },
        save() {
            this.savingRecipe = true
            this.$store.dispatch("setSavingRecipe", { saving: true })
            const $this = this

            const request = (() => {
                if(this.recipe_id) {
                    return this.$store.dispatch("updateRecipe", { recipe: this.recipe })
                } else {
                    return this.$store.dispatch("createRecipe", { recipe: this.recipe })
                }
            })()

            request.then((response) => {
                    $this.$window.goTo(`/recipe/${response.data}`)
                })
                .catch((e) => {
                    // error

                    if(e.response){
                        // Non 2xx state returned

                        switch(e.response.status){
                            case 409:
                                alert(e.response.data.msg)
                                break

                            default:
                                // eslint-disable-next-line no-alert
                                alert(t('cookbook', 'Unknown answer returned from server. See logs.'))
                                // eslint-disable-next-line no-console
                                console.log(e.response)
                        }
                    } else if(e.request) {
                        // eslint-disable-next-line no-alert
                        alert(t("cookbook", "No answer for request was received."))
                        // eslint-disable-next-line no-console
                        console.log(e)
                    } else {
                        // eslint-disable-next-line no-alert
                        alert(t("cookbook", "Could not start request to save recipe."))
                        // eslint-disable-next-line no-console
                        console.log(e)
                    }
                })
                .then(() => {
                    // finally
                    $this.$store.dispatch("setSavingRecipe", {
                        saving: false,
                    })
                    $this.savingRecipe = false
                })
                .catch((e) => {
                    console.log('terminal catch')
                    console.log(e)
                })
        },
        setup() {
            this.fetchCategories()
            this.fetchKeywords()
            if (this.$route.params.id) {
                // Load the recipe from store and make edits to a local copy first
                this.recipe = { ...this.$store.state.recipe }
                // Parse time values
                let timeComps = this.recipe.prepTime
                    ? this.recipe.prepTime.match(/PT(\d+?)H(\d+?)M/)
                    : null
                this.prepTime = {
                    time: timeComps ? [timeComps[1], timeComps[2]] : [0, 0],
                    paddedTime: this.recipe.prepTime,
                }

                timeComps = this.recipe.cookTime
                    ? this.recipe.cookTime.match(/PT(\d+?)H(\d+?)M/)
                    : null
                this.cookTime = {
                    time: timeComps ? [timeComps[1], timeComps[2]] : [0, 0],
                    paddedTime: this.recipe.cookTime,
                }

                timeComps = this.recipe.totalTime
                    ? this.recipe.totalTime.match(/PT(\d+?)H(\d+?)M/)
                    : null

                this.totalTime = {
                    time: timeComps ? [timeComps[1], timeComps[2]] : [0, 0],
                    paddedTime: this.recipe.totalTime,
                }

                this.selectedKeywords = this.recipe.keywords.split(",")

                // fallback if fetching all keywords fails
                this.selectedKeywords.forEach((kw) => {
                    if (!this.allKeywords.includes(kw)) {
                        this.allKeywords.push(kw)
                    }
                })

                // fallback if fetching all categories fails
                if (!this.allCategories.includes(this.recipe.recipeCategory)) {
                    this.allCategories.push(this.recipe.recipeCategory)
                }

                // Always set the active page last!
                this.$store.dispatch("setPage", { page: "edit" })
            } else {
                this.initEmptyRecipe()
                this.$store.dispatch("setPage", { page: "create" })
            }
            this.recipeInit = JSON.parse(JSON.stringify(this.recipe))
            this.$nextTick(function markDirty() {
                this.formDirty = false
            })
        },
        initEmptyRecipe() {
            this.prepTime = { time: [0, 0], paddedTime: "" }
            this.cookTime = { time: [0, 0], paddedTime: "" }
            this.totalTime = { time: [0, 0], paddedTime: "" }
            this.nutrition = {}
            this.recipe = {
                id: 0,
                name: null,
                description: "",
                url: "",
                image: "",
                prepTime: "",
                cookTime: "",
                totalTime: "",
                recipeCategory: "",
                keywords: "",
                recipeYield: "",
                tool: [],
                recipeIngredient: [],
                recipeInstructions: [],
                nutrition: {},
            }
            this.formDirty = false
        },
    },
}
</script>

<style scoped>
.wrapper {
    width: 100%;
    padding: 1rem;
}

.overlay {
    position: absolute;
    z-index: 1000;
    top: 0;
    left: 0;
    display: block;
    width: 100%;
    height: 100%;
    background-color: var(--color-main-background);
    opacity: 0.75;
}
.overlay.hidden {
    display: none;
}

/* This is not used anywhere at the moment, but left here for future reference
form fieldset ul label input[type="checkbox"] {
    margin-left: 1em;
    vertical-align: middle;
    cursor: pointer;
} */

.references-popup {
    position: fixed;
    display: none;
}

.references-popup.visible {
    display: block;
}
</style>
