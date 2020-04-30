/**
 * Nextcloud Cookbook app
 * Vue router module
 * ----------------------
 * @license AGPL3 or later
 */
import Vue from 'vue'
import VueRouter from 'vue-router'

import Index from '../components/AppIndex'
import Search from '../components/SearchResults'
import RecipeView from '../components/RecipeView'
import RecipeEdit from '../components/RecipeEdit'

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
    // Defining props as true we can pass props to the components much more flexibly
    { path: '/', component: Index },
    // Search routes
    { path: '/category/:value', component: Search, props: { query: 'cat' } },
    { path: '/name/:value', component: Search, props: { query: 'name' } },
    { path: '/tag/:value', component: Search, props: { query: 'tag' } },
    // Recipe routes
    { path: '/recipe/edit/:id', component: RecipeEdit, props: true },
    { path: '/recipe/create', component: RecipeEdit, props: { id: 0 } },
    { path: '/recipe/:id', component: RecipeView, props: { query: 'tag' } },
]
export default new VueRouter({
    routes
})
