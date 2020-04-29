<template>
<!-- This component should not have a conflicting name with AppNavigation from the nextcloud/vue package -->
    <AppNavigation>
        <AppNavigationNew class="create" :text="$t('Create recipe')" @click="$window.goTo('/recipe/create')" />
        <ul>
            <ActionInput class="download" @submit="downloadRecipe" icon="icon-download">{{ $t('Recipe URL') }}</ActionInput>
            <AppNavigationItem :title="$t('All recipes')" icon="icon-category-organization" :to="'/'">
                <AppNavigationCounter slot="counter">{{ totalRecipeCount }}</AppNavigationCounter>
            </AppNavigationItem>
            <AppNavigationItem v-for="(cat,idx) in categories"
                :key="cat+idx"
                :title="cat.name"
                icon="icon-category-files"
                :allowCollapse="true"
                :to="'/category/'+cat.name"
            >
                <AppNavigationCounter slot="counter">{{ cat.recipes.length }}</AppNavigationCounter>
                <template>
                    <AppNavigationItem class="recipe" v-for="(rec,idy) in cat.recipes"
                        :key="idx+'-'+idy"
                        :title="rec.name"
                        :to="'/recipe/'+rec.id"
                    />
                </template>
            </AppNavigationItem>
        </ul>
        <!-- The Settings tab still immediately closes again after clicking -->
        <AppNavigationSettings :open="true">
            <div id="app-settings-content">
                <fieldset class="settings-fieldset">
                    <ul class="settings-fieldset-interior">
                        <li class="settings-fieldset-interior-item">
                            <button class="button icon-history" id="reindex-recipes">{{ $t('Rescan library') }}</button>
                        </li>
                        <li class="settings-fieldset-interior-item">
                            <label class="settings-input">{{ $t('Recipe folder') }}</label>
                            <input id="recipe-folder" type="text" class="input settings-input" :value="recipeFolder" :placeholder="$t('Please pick a folder')">
                        </li>
                        <li class="settings-fieldset-interior-item">
                            <label class="settings-input">
                                {{ $t('Update interval in minutes') }}
                            </label>
                            <div class="input-group">
                                <input id="recipe-update-interval" type="number" class="input settings-input" :value="updateInterval" :placeholder="updateInterval">
                                <div class="input-group-addon">
                                    <button class="icon-info" disabled="disabled" :title="$t('Last update: $upd')"></button>
                                </div>
                            </div>
                        </li>
                        <li v-if="$store.state.recipe" class="settings-fieldset-interior-item">
                            <input id="recipe-print-image" type="checkbox" class="checkbox" :checked="{ 'checked': $store.state.recipe.printImage }">>
                            <label class="settings-input" for="recipe-print-image">
                                {{ $t('Print image with recipe') }}
                            </label>
                        </li>
                    </ul>
                </fieldset>
            </div>
        </AppNavigationSettings>
    </AppNavigation>
</template>

<script>

import ActionInput from '@nextcloud/vue/dist/Components/ActionInput'
import AppNavigation from '@nextcloud/vue/dist/Components/AppNavigation'
import AppNavigationCaption from '@nextcloud/vue/dist/Components/AppNavigationCaption'
import AppNavigationCounter from '@nextcloud/vue/dist/Components/AppNavigationCounter'
import AppNavigationItem from '@nextcloud/vue/dist/Components/AppNavigationItem'
import AppNavigationNew from '@nextcloud/vue/dist/Components/AppNavigationNew'
import AppNavigationSettings from '@nextcloud/vue/dist/Components/AppNavigationSettings'
import AppNavigationSpacer from '@nextcloud/vue/dist/Components/AppNavigationSpacer'

export default {
    name: 'AppNavi',
    components: {
        ActionInput,
        AppNavigation,
        AppNavigationCaption,
        AppNavigationCounter,
        AppNavigationItem,
        AppNavigationNew,
        AppNavigationSettings,
        AppNavigationSpacer,
    },
    data () {
        return {
            categories: [],
            recipeFolder: "",
            updateInterval: 0,
        }
    },
    computed: {
        totalRecipeCount () {
            let recCount = 0
            for (let i=0; i<this.categories.length; i++) {
                recCount += this.categories[i].recipes.length
            }
            return recCount
        }
    },
    methods: {
        downloadRecipe: function(e) {
            console.log(e.target[1].value)
        },
    },
    mounted () {
        // Testing
        this.categories = [
            {
                name: 'Test category 1',
                recipes: [
                    { id: 1, name: 'Test recipe 1' },
                    { id: 2, name: 'Test recipe 2' },
                ]
            },
            {
                name: 'Test category 2',
                recipes: [
                    { id: 3, name: 'Test recipe 3' },
                    { id: 4, name: 'Test recipe 4' },
                    { id: 5, name: 'Test recipe 5' },
                    { id: 6, name: 'Test recipe 6' },
                ]
            },
            {
                name: 'Test category 3',
                recipes: [
                    { id: 7, name: 'Test recipe 7' },
                ]
            },
        ]
    },
}

</script>

<style scoped>

</style>
