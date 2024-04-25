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
	{
		path: '/category/:value',
		name: RouteName.SearchRecipesByCategory,
		components: { default: SearchResults, 'content-list': SearchResults },
		props: {
			default: (route: Route) => ({
				query: 'cat',
				value: `"${route.params.value}"`,
				searchQuery: routeToQueryProp(route),
			}),
			'content-list': (route: Route) => ({
				query: 'cat',
				value: `"${route.params.value}"`,
				searchQuery: routeToQueryProp(route),
			}),
		},
	},
	{
		path: '/category/:value/:id',
		name: RouteName.ShowRecipeInCategory,
		components: {
			default: RecipeView,
			'content-list': SearchResults,
			'main-view__active-list': RecipeView,
			sidebar: RecipeViewSidebar,
		},
		props: {
			default: (route: Route) => ({
				id: parseInt(route.params.id, 10),
			}),
			'content-list': (route: Route) => ({
				query: 'cat',
				value: `"${route.params.value}"`,
				searchQuery: routeToQueryProp(route),
			}),
			'main-view__active-list': (route: Route) => ({
				id: parseInt(route.params.id, 10),
			}),
		},
	},

	// Name
	{
		path: '/name/:value',
		name: RouteName.SearchRecipesByName,
		components: { default: SearchResults, 'content-list': SearchResults },
		props: {
			default: (route: Route) => ({
				query: 'name',
				value: `"${route.params.value}"`,
				searchQuery: routeToQueryProp(route),
			}),
			'content-list': (route: Route) => ({
				query: 'name',
				value: `"${route.params.value}"`,
				searchQuery: routeToQueryProp(route),
			}),
		},
	},
	{
		path: '/name/:value/:id',
		name: RouteName.ShowRecipeInNames,
		components: {
			default: RecipeView,
			'content-list': SearchResults,
			'main-view__active-list': RecipeView,
			sidebar: RecipeViewSidebar,
		},
		props: {
			default: (route: Route) => ({
				id: parseInt(route.params.id, 10),
			}),
			'content-list': (route: Route) => ({
				query: 'name',
				value: `"${route.params.value}"`,
				searchQuery: routeToQueryProp(route),
			}),
			'main-view__active-list': (route: Route) => ({
				id: parseInt(route.params.id, 10),
			}),
		},
	},

	// General search
	{
		path: '/search/:value',
		name: RouteName.SearchRecipesByAnything,
		components: { default: SearchResults, 'content-list': SearchResults },
		props: {
			default: (route: Route) => ({
				query: 'general',
				value: `"${route.params.value}"`,
				searchQuery: routeToQueryProp(route),
			}),
			'content-list': (route: Route) => ({
				query: 'general',
				value: `"${route.params.value}"`,
				searchQuery: routeToQueryProp(route),
			}),
		},
	},
	{
		path: '/search/:value/:id',
		name: RouteName.ShowRecipeInGeneralSearch,
		components: {
			default: RecipeView,
			'content-list': SearchResults,
			'main-view__active-list': RecipeView,
			sidebar: RecipeViewSidebar,
		},
		props: {
			default: (route: Route) => ({
				id: parseInt(route.params.id, 10),
			}),
			'content-list': (route: Route) => ({
				query: 'general',
				value: `"${route.params.value}"`,
				searchQuery: routeToQueryProp(route),
			}),
			'main-view__active-list': (route: Route) => ({
				id: parseInt(route.params.id, 10),
			}),
		},
	},

	// Tags
	{
		path: '/tags/:value',
		name: RouteName.SearchRecipesByTags,
		components: { default: SearchResults, 'content-list': SearchResults },
		props: {
			default: (route: Route) => ({
				query: 'tags',
				value: `"${route.params.value}"`,
				searchQuery: routeToQueryProp(route),
			}),
			'content-list': (route: Route) => ({
				query: 'tags',
				value: `"${route.params.value}"`,
				searchQuery: routeToQueryProp(route),
			}),
		},
	},
	{
		path: '/tags/:value/:id',
		name: RouteName.ShowRecipeInTags,
		components: {
			default: RecipeView,
			'content-list': SearchResults,
			'main-view__active-list': RecipeView,
			sidebar: RecipeViewSidebar,
		},
		props: {
			default: (route: Route) => ({
				id: parseInt(route.params.id, 10),
			}),
			'content-list': (route: Route) => ({
				query: 'tags',
				value: `"${route.params.value}"`,
				searchQuery: routeToQueryProp(route),
			}),
			'main-view__active-list': (route: Route) => ({
				id: parseInt(route.params.id, 10),
			}),
		},
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
