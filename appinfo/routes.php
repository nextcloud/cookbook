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
		 * If you add new features here, invrese the minor version of the API.
		 * If you change the behavior or remove functionality, increase the major version there.
		 */
		['name' => 'main#getApiVersion', 'url' => '/api/version', 'verb' => 'GET'],
		['name' => 'main#index', 'url' => '/', 'verb' => 'GET'],

		/*
		 * legacy routes -- deprecated
		 */
		['name' => 'main_v1#keywords', 'url' => '/keywords', 'verb' => 'GET', 'postfix' => '-legacy'],
		['name' => 'main_v1#categories', 'url' => '/categories', 'verb' => 'GET', 'postfix' => '-legacy'],
		['name' => 'main_v1#import', 'url' => '/import', 'verb' => 'POST', 'postfix' => '-legacy'],
		['name' => 'recipe_v1#image', 'url' => '/recipes/{id}/image', 'verb' => 'GET', 'requirements' => ['id' => '\d+'], 'postfix' => '-legacy'],
		['name' => 'config_v1#reindex', 'url' => '/reindex', 'verb' => 'POST', 'postfix' => '-legacy'],
		['name' => 'config_v1#list', 'url' => '/config', 'verb' => 'GET', 'postfix' => '-legacy'],
		['name' => 'config_v1#config', 'url' => '/config', 'verb' => 'POST', 'postfix' => '-legacy'],
		/* API routes */
		['name' => 'main_v1#category', 'url' => '/api/category/{category}', 'verb' => 'GET', 'postfix' => '-legacy'],
		['name' => 'main_v1#categoryUpdate', 'url' => '/api/category/{category}', 'verb' => 'PUT', 'postfix' => '-legacy'],
		['name' => 'main_v1#tags', 'url' => '/api/tags/{keywords}', 'verb' => 'GET', 'postfix' => '-legacy'],
		['name' => 'main_v1#search', 'url' => '/api/search/{query}', 'verb' => 'GET', 'postfix' => '-legacy'],
		/* Unknown usage */
		/* Deprecated routes */
		['name' => 'main_v1#new', 'url' => '/recipes/create', 'verb' => 'POST', 'postfix' => '-legacy'],
		['name' => 'main_v1#update', 'url' => '/recipes/{id}/edit', 'verb' => 'PUT', 'requirements' => ['id' => '\d+'], 'postfix' => '-legacy'],

		/*
		 * API v1
		 */
		['name' => 'main_v1#keywords', 'url' => '/api/v1/keywords', 'verb' => 'GET'],
		['name' => 'main_v1#categories', 'url' => '/api/v1/categories', 'verb' => 'GET'],
		['name' => 'main_v1#import', 'url' => '/api/v1/import', 'verb' => 'POST'],
		['name' => 'recipe_v1#image', 'url' => '/api/v1/recipes/{id}/image', 'verb' => 'GET', 'requirements' => ['id' => '\d+']],
		['name' => 'config_v1#reindex', 'url' => '/api/v1/reindex', 'verb' => 'POST'],
		['name' => 'config_v1#list', 'url' => '/api/v1/config', 'verb' => 'GET'],
		['name' => 'config_v1#config', 'url' => '/api/v1/config', 'verb' => 'POST'],
		/* API routes */
		['name' => 'main_v1#category', 'url' => '/api/v1/category/{category}', 'verb' => 'GET'],
		['name' => 'main_v1#categoryUpdate', 'url' => '/api/v1/category/{category}', 'verb' => 'PUT'],
		['name' => 'main_v1#tags', 'url' => '/api/v1/tags/{keywords}', 'verb' => 'GET'],
		['name' => 'main_v1#search', 'url' => '/api/v1/search/{query}', 'verb' => 'GET'],
	],

	/* API resources */
	'resources' => [
		'recipe_legacy' => ['url' => '/api/recipes'],
		'recipe_v1' => ['url' => '/api/v1/recipes'],
	]
];
