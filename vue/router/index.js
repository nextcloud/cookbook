/**
 * Nextcloud Cookbook app
 * Vue router module
 * ----------------------
 * @license AGPL3 or later
 */
import Vue from 'vue'
import VueRouter from 'vue-router'

import Index from '../components/AppIndex'
import NotFound from '../components/NotFound'
import RecipeView from '../components/RecipeView'
import RecipeEdit from '../components/RecipeEdit'
import Search from '../components/SearchResults'

Vue.use(VueRouter)

// The router will try to match routers in a descending order.
// Routes that share the same root, must be listed from the
//  most descriptive to the least descriptive, e.g.
//  /section/component/subcomponent/edit/:id
//  /section/component/subcomponent/new
//  /section/component/subcomponent/:id
//  /section/component/:id
//  /section/:id
const routes = [
    // Search routes
    { path: '/category/:value', name: 'search-category', component: Search, props: { query: 'cat' } },
    { path: '/name/:value', name: 'search-name', component: Search, props: { query: 'name' } },
    { path: '/tag/:value', name: 'search-tag', component: Search, props: { query: 'tag' } },
    // Recipe routes
    { path: '/recipe/edit/:id', name: 'recipe-edit', component: RecipeEdit },
    { path: '/recipe/create', name: 'recipe-create', component: RecipeEdit },
    { path: '/recipe/:id', name: 'recipe-view', component: RecipeView },
    // Index is the last defined route
    { path: '/', name:'index', component: Index },
    // Anything not matched goes to NotFound
    { path: '*', name:'not-found', component: NotFound },
]
export default new VueRouter({
    routes
})
