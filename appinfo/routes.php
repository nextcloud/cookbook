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
	   ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
	   ['name' => 'page#home', 'url' => '/home', 'verb' => 'GET'],
	   ['name' => 'page#search', 'url' => '/search/{query}', 'verb' => 'GET'],
	   ['name' => 'page#tag', 'url' => '/tag/{tag}', 'verb' => 'GET'],
	   ['name' => 'page#all', 'url' => '/all', 'verb' => 'GET'],
	   ['name' => 'page#error', 'url' => '/error', 'verb' => 'GET'],
	   ['name' => 'page#create', 'url' => '/recipes/create', 'verb' => 'GET'],
	   ['name' => 'page#new', 'url' => '/recipes/create', 'verb' => 'POST'],
	   ['name' => 'page#import', 'url' => '/import', 'verb' => 'POST'],
	   ['name' => 'page#edit', 'url' => '/recipes/{id}/edit', 'verb' => 'GET', 'requirements' => ['id' => '\d+']],
	   ['name' => 'page#update', 'url' => '/recipes/{id}/edit', 'verb' => 'PUT', 'requirements' => ['id' => '\d+']],
	   ['name' => 'page#recipe', 'url' => '/recipes/{id}', 'verb' => 'GET', 'requirements' => ['id' => '\d+']],
	   ['name' => 'recipe#index', 'url' => '/recipes', 'verb' => 'GET'],
	   ['name' => 'recipe#add', 'url' => '/add', 'verb' => 'POST'],
	   ['name' => 'recipe#delete', 'url' => '/recipes/{id}', 'verb' => 'DELETE', 'requirements' => ['id' => '\d+']],
	   ['name' => 'recipe#update', 'url' => '/recipes/{id}', 'verb' => 'PUT', 'requirements' => ['id' => '\d+']],
	   ['name' => 'recipe#create', 'url' => '/recipes', 'verb' => 'POST'],
	   // ['name' => 'recipe#get', 'url' => '/recipes/{id}', 'verb' => 'GET', 'requirements' => ['id' => '\d+']],
	   ['name' => 'recipe#image', 'url' => '/recipes/{id}/image', 'verb' => 'GET', 'requirements' => ['id' => '\d+']],
	   ['name' => 'recipe#reindex', 'url' => '/reindex', 'verb' => 'POST'],
	   ['name' => 'recipe#config', 'url' => '/config', 'verb' => 'POST'],
    ]
];
