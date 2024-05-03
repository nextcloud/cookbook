/**
 * Name of a route.
 */
enum RouteName {
	// All recipes
	Index = 'index',
	ShowRecipeInIndex = 'recipe-view',
	EditRecipeInIndex = 'recipe-edit',

	// via category
	SearchRecipesByCategory = 'search-category',
	ShowRecipeInCategory = 'search-category__recipe-view',
	EditRecipeInCategory = 'search-category__recipe-edir',

	// via name
	SearchRecipesByName = 'search-name',
	ShowRecipeInNames = 'search-name__recipe-view',
	EditRecipeInNames = 'search-name__recipe-edir',

	// via general search
	SearchRecipesByAnything = 'search-general',
	ShowRecipeInGeneralSearch = 'search-general__recipe-view',
	EditRecipeInGeneralSearch = 'search-general__recipe-edit',

	// via tags
	SearchRecipesByTags = 'search-tags',
	ShowRecipeInTags = 'search-tags__recipe-view',
	EditRecipeInTags = 'search-tags__recipe-edir',
}

export default RouteName;
