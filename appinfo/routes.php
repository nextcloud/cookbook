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
	   ['name' => 'page#recipe', 'url' => '/recipe', 'verb' => 'GET'],
	   ['name' => 'recipe#all', 'url' => '/all', 'verb' => 'GET'],
	   ['name' => 'recipe#add', 'url' => '/add', 'verb' => 'POST'],
	   ['name' => 'recipe#keywords', 'url' => '/keywords', 'verb' => 'GET'],
	   ['name' => 'recipe#find', 'url' => '/find', 'verb' => 'GET'],
	   ['name' => 'recipe#image', 'url' => '/image', 'verb' => 'GET'],
	   ['name' => 'recipe#reindex', 'url' => '/reindex', 'verb' => 'POST'],
	   ['name' => 'recipe#config', 'url' => '/config', 'verb' => 'POST'],
    ]
];
