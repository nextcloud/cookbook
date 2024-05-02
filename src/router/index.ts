/**
 * Nextcloud Cookbook app
 * Vue router module
 * ----------------------
 * @license AGPL3 or later
 */
import Vue from 'vue';
import VueRouter, { Route, RouteConfig } from 'vue-router';

import RouteName from 'cookbook/js/Enums/RouteName';
import { routeToQueryProp } from 'cookbook/js/utils/routeUtils';
import RecipeViewSidebar from 'cookbook/components/RecipeView/Sidebar/RecipeViewSidebar.vue';
import Index from 'cookbook/components/AppIndex.vue';
import NotFound from 'cookbook/components/NotFound.vue';
import RecipeView from 'cookbook/components/RecipeView/RecipeView.vue';
import RecipeEdit from 'cookbook/components/RecipeEdit.vue';
import SearchResults from 'cookbook/components/SearchResults.vue';

Vue.use(VueRouter);

/**
 * Get the named-router - components mapping for paths to a recipe list.
 */
const componentsForSearchRoute = {
	default: SearchResults,
	'content-list': SearchResults,
};

/**
 * Get the props for recipe-listing paths (all recipes, recipes in category, recipes with tags, etc.)
 * @param query 'index', 'cat', 'tags', 'general'
 */
function getPropsForSearchRoute(query: string) {
	return {
		default: (route: Route) => ({
			query,
			value: `"${route.params.value}"`,
			searchQuery: routeToQueryProp(route),
		}),
		'content-list': (route: Route) => ({
			query,
			value: `"${route.params.value}"`,
			searchQuery: routeToQueryProp(route),
		}),
	};
}

/**
 * Get the named-router - components mapping for paths with a selected recipe.
 */
const componentsForRecipeInSearchRoute = {
	default: RecipeView,
	'content-list': SearchResults,
	'main-view__active-list': RecipeView,
	sidebar: RecipeViewSidebar,
};

/**
 * Get the named-router - components mapping for paths with the editor for a selected recipe.
 */
const componentsForRecipeEditInSearchRoute = {
	default: RecipeEdit,
	'content-list': SearchResults,
	'main-view__active-list': RecipeEdit,
};

/**
 * Get the props for a selected recipe within recipe-listing paths (all recipes, recipes in category, recipes with tags, etc.)
 * @param query 'index', 'cat', 'tags', 'general'
 */
function getPropsForRecipeInSearchRoute(query: string) {
	return {
		default: (route: Route) => ({
			id: parseInt(route.params.id, 10),
		}),
		'content-list': (route: Route) => ({
			query,
			value: `"${route.params.value}"`,
			searchQuery: routeToQueryProp(route),
		}),
		'main-view__active-list': (route: Route) => ({
			id: parseInt(route.params.id, 10),
		}),
	};
}

// The router will try to match routers in descending order.
// Routes that share the same root, must be listed from the
//  most descriptive to the least descriptive, e.g.
//  /section/component/subcomponent/edit/:id
//  /section/component/subcomponent/new
//  /section/component/subcomponent/:id
//  /section/component/:id
//  /section/:id
const routes: RouteConfig[] = [
	// Search routes
	// Recipes with category
	{
		path: '/category/:value',
		name: RouteName.SearchRecipesByCategory,
		components: componentsForSearchRoute,
		props: getPropsForSearchRoute('cat'),
	},
	{
		path: '/category/:value/:id',
		name: RouteName.ShowRecipeInCategory,
		components: componentsForRecipeInSearchRoute,
		props: getPropsForRecipeInSearchRoute('cat'),
	},
	{
		path: '/category/:value/:id/edit',
		name: RouteName.ShowRecipeInCategory,
		components: componentsForRecipeEditInSearchRoute,
		props: getPropsForRecipeInSearchRoute('cat'),
	},

	// Recipes with name
	{
		path: '/name/:value',
		name: RouteName.SearchRecipesByName,
		components: componentsForSearchRoute,
		props: getPropsForSearchRoute('name'),
	},
	{
		path: '/name/:value/:id',
		name: RouteName.ShowRecipeInNames,
		components: componentsForRecipeInSearchRoute,
		props: getPropsForRecipeInSearchRoute('name'),
	},
	{
		path: '/name/:value/:id/edit',
		name: RouteName.ShowRecipeInCategory,
		components: componentsForRecipeEditInSearchRoute,
		props: getPropsForRecipeInSearchRoute('name'),
	},

	// General search
	{
		path: '/search/:value',
		name: RouteName.SearchRecipesByAnything,
		components: componentsForSearchRoute,
		props: getPropsForSearchRoute('general'),
	},
	{
		path: '/search/:value/:id',
		name: RouteName.ShowRecipeInGeneralSearch,
		components: componentsForRecipeInSearchRoute,
		props: getPropsForRecipeInSearchRoute('general'),
	},
	{
		path: '/search/:value/:id/edit',
		name: RouteName.ShowRecipeInCategory,
		components: componentsForRecipeEditInSearchRoute,
		props: getPropsForRecipeInSearchRoute('general'),
	},

	// Recipes with tags
	{
		path: '/tags/:value',
		name: RouteName.SearchRecipesByTags,
		components: componentsForSearchRoute,
		props: getPropsForSearchRoute('tags'),
	},
	{
		path: '/tags/:value/:id',
		name: RouteName.ShowRecipeInTags,
		components: componentsForRecipeInSearchRoute,
		props: getPropsForRecipeInSearchRoute('tags'),
	},
	{
		path: '/tags/:value/:id/edit',
		name: RouteName.ShowRecipeInCategory,
		components: componentsForRecipeEditInSearchRoute,
		props: getPropsForRecipeInSearchRoute('tags'),
	},

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
	{ path: '/recipe/:id/clone', name: 'recipe-clone', component: RecipeEdit },
	{ path: '/recipe/:id/edit', name: 'recipe-edit', component: RecipeEdit },
	{
		path: '/recipe/:id',
		name: 'recipe-view',
		// Vue Named Views
		components: { default: RecipeView, sidebar: RecipeViewSidebar },
	},

	// Index is the last defined route
	{ path: '/', name: 'index', component: Index },

	// Anything not matched goes to NotFound
	{ path: '*', name: 'not-found', component: NotFound },
];

export default new VueRouter({
	routes,
});
