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
	   ['name' => 'main#index', 'url' => '/', 'verb' => 'GET'],
	   ['name' => 'main#home', 'url' => '/home', 'verb' => 'GET'],
	   ['name' => 'main#keywords', 'url' => '/keywords', 'verb' => 'GET'],
	   ['name' => 'main#categories', 'url' => '/categories', 'verb' => 'GET'],
	   ['name' => 'main#category', 'url' => '/category/{category}', 'verb' => 'GET'],
	   ['name' => 'main#search', 'url' => '/search/{query}', 'verb' => 'GET'],
	   ['name' => 'main#error', 'url' => '/error', 'verb' => 'GET'],
	   ['name' => 'main#create', 'url' => '/recipes/create', 'verb' => 'GET'],
	   ['name' => 'main#new', 'url' => '/recipes/create', 'verb' => 'POST'],
	   ['name' => 'main#import', 'url' => '/import', 'verb' => 'POST'],
	   ['name' => 'main#edit', 'url' => '/recipes/{id}/edit', 'verb' => 'GET', 'requirements' => ['id' => '\d+']],
	   ['name' => 'main#update', 'url' => '/recipes/{id}/edit', 'verb' => 'PUT', 'requirements' => ['id' => '\d+']],
	   ['name' => 'main#recipe', 'url' => '/recipes/{id}', 'verb' => 'GET', 'requirements' => ['id' => '\d+']],
	   ['name' => 'recipe#image', 'url' => '/recipes/{id}/image', 'verb' => 'GET', 'requirements' => ['id' => '\d+']],
	   ['name' => 'config#reindex', 'url' => '/reindex', 'verb' => 'POST'],
	   ['name' => 'config#config', 'url' => '/config', 'verb' => 'POST'],
   ],
   'resources' => [
	   	'recipe' => ['url' => '/api/recipes']
   ]
];
