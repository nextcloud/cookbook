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
		['name' => 'main#keywords', 'url' => '/keywords', 'verb' => 'GET'],
		['name' => 'main#categories', 'url' => '/categories', 'verb' => 'GET'],
		['name' => 'main#import', 'url' => '/import', 'verb' => 'POST'],
		['name' => 'recipe#image', 'url' => '/recipes/{id}/image', 'verb' => 'GET', 'requirements' => ['id' => '\d+']],
		['name' => 'config#reindex', 'url' => '/reindex', 'verb' => 'POST'],
		['name' => 'config#list', 'url' => '/config', 'verb' => 'GET'],
		['name' => 'config#config', 'url' => '/config', 'verb' => 'POST'],
		/* API routes */
		['name' => 'main#category', 'url' => '/api/category/{category}', 'verb' => 'GET'],
		['name' => 'main#categoryUpdate', 'url' => '/api/category/{category}', 'verb' => 'PUT'],
		['name' => 'main#tags', 'url' => '/api/tags/{keywords}', 'verb' => 'GET'],
		['name' => 'main#search', 'url' => '/api/search/{query}', 'verb' => 'GET'],
		/* Unknown usage */
		/* Deprecated routes */
		['name' => 'main#new', 'url' => '/recipes/create', 'verb' => 'POST'],
		['name' => 'main#update', 'url' => '/recipes/{id}/edit', 'verb' => 'PUT', 'requirements' => ['id' => '\d+']],
	],

	/* API resources */
	'resources' => [
		'recipe' => ['url' => '/api/recipes']
	]
];
