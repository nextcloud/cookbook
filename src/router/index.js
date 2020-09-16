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
    { path: '/search/:value', name: 'search-general', component: Search, props: { query: 'general' } },
    { path: '/tag/:value', name: 'search-tag', component: Search, props: { query: 'tag' } },

    // Recipe routes
    // Vue router has a strange way of determining when it renders a component again and when not.
    // In essence, when two routes point to the same component, it usually will not be re-rendered
    // automatically. If the contents change (e.g. between /recipe/xxx and /recipe/yyy) this must
    // be checked for and the component re-rendered manually. In order to avoid the need to write
    // separate checks for different item types, all items MUST follow this route convention:
    // - View: /{item}/:id
    // - Edit: /{item}/:id/edit
    // - Create: /{item}/create
    { path: '/recipe/create', name: 'recipe-create', component: RecipeEdit },
    { path: '/recipe/:id/edit', name: 'recipe-edit', component: RecipeEdit },
    { path: '/recipe/:id', name: 'recipe-view', component: RecipeView },

    // Index is the last defined route
    { path: '/', name:'index', component: Index },

    // Anything not matched goes to NotFound
    { path: '*', name:'not-found', component: NotFound },
];

export default new VueRouter({
    routes
})
