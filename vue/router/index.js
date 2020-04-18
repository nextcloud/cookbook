/**
 * Nextcloud Cookbook app
 * Vue router module
 * ----------------------
 * @license AGPL3 or later
 */
import Vue from 'vue'
import VueRouter from 'vue-router'

import RecipeImages from '../components/RecipeImages'
import RecipeIngredient from '../components/RecipeIngredient'
import RecipeInstruction from '../components/RecipeInstruction'

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
    // At the moment there are no routes
]
export default new VueRouter({
    routes
})
