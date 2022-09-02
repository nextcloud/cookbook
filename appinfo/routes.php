<?php
/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\Cookbook\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */
return [
	'routes' => [
		/*
		 * Very important: Do not change anything here without updating the api version in MainController.
		 * If you add new features here, increase the minor version of the API.
		 * If you change the behavior or remove functionality, increase the major version there.
		 */
		
		// The static HTML template
		['name' => 'main#index', 'url' => '/', 'verb' => 'GET'],

		// The /webapp routes
		['name' => 'recipe#image', 'url' => '/webapp/recipes/{id}/image', 'verb' => 'GET', 'requirements' => ['id' => '\d+']],
		['name' => 'recipe#import', 'url' => '/webapp/import', 'verb' => 'POST'],
		['name' => 'recipe#category', 'url' => '/webapp/category/{category}', 'verb' => 'GET'],
		['name' => 'recipe#tags', 'url' => '/webapp/tags/{keywords}', 'verb' => 'GET'],
		['name' => 'recipe#search', 'url' => '/webapp/search/{query}', 'verb' => 'GET'],
		
		['name' => 'keyword#keywords', 'url' => '/webapp/keywords', 'verb' => 'GET'],
		
		['name' => 'category#categories', 'url' => '/webapp/categories', 'verb' => 'GET'],
		['name' => 'category#rename', 'url' => '/webapp/category/{category}', 'verb' => 'PUT'],
		
		['name' => 'config#list', 'url' => '/webapp/config', 'verb' => 'GET'],
		['name' => 'config#config', 'url' => '/webapp/config', 'verb' => 'POST'],
		['name' => 'config#reindex', 'url' => '/webapp/reindex', 'verb' => 'POST'],

		/* API routes */

		// Generic routes on /api
		['name' => 'util_api#getApiVersion', 'url' => '/api/version', 'verb' => 'GET'],

		// APIv1 routes under /api/v1
		['name' => 'recipe_api#image', 'url' => '/api/v1/recipes/{id}/image', 'verb' => 'GET', 'requirements' => ['id' => '\d+']],
		['name' => 'recipe_api#import', 'url' => '/api/v1/import', 'verb' => 'POST'],
		['name' => 'recipe_api#category', 'url' => '/api/v1/category/{category}', 'verb' => 'GET'],
		['name' => 'recipe_api#tags', 'url' => '/api/v1/tags/{keywords}', 'verb' => 'GET'],
		['name' => 'recipe_api#search', 'url' => '/api/v1/search/{query}', 'verb' => 'GET'],
		
		['name' => 'keyword_api#keywords', 'url' => '/api/v1/keywords', 'verb' => 'GET'],
		
		['name' => 'category_api#categories', 'url' => '/api/v1/categories', 'verb' => 'GET'],
		['name' => 'category_api#rename', 'url' => '/api/v1/category/{category}', 'verb' => 'PUT'],
		
		['name' => 'config_api#list', 'url' => '/api/v1/config', 'verb' => 'GET'],
		['name' => 'config_api#config', 'url' => '/api/v1/config', 'verb' => 'POST'],
		['name' => 'config_api#reindex', 'url' => '/api/v1/reindex', 'verb' => 'POST'],

		['name' => 'util_api#preflighted_cors', 'url' => '/api/{path}', 'verb' => 'OPTIONS', 'requirements' => ['path' => '.+']],
	],

	/* API resources */
	'resources' => [
		'recipe' => ['url' => '/webapp/recipes'],
		'recipe_api' => ['url' => '/api/v1/recipes'],
	]
];
