<template>
<!-- This component current has a conflicting name with the nextcloud/vue package -->
    <AppNavigation>
        <AppNavigationNew class="create" :text="$t('Create recipe')" @click="$window.goTo('/recipe/create')" />
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
        <!-- I just can't make this settings component work for the life of me
        <AppNavigationSettings>
            Dummy settings
        </AppNavigationSettings>
        -->
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

.download {
    /* These are the default app-navigation-entry styles */
    position: relative;
    display: flex;
    flex-shrink: 0;
    flex-wrap: wrap;
    order: 1;
    box-sizing: border-box;
    width: 100%;
    min-height: 44px;
}

</style>
