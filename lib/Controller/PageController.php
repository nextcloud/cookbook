<?php
namespace OCA\Cookbook\Controller;

use OCP\IConfig;
use OCP\IRequest;
use OCP\IDBConnection;
use OCP\Files\IRootFolder;
use OCP\Files\FileInfo;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
use OCA\Cookbook\Service\RecipeService;

class PageController extends Controller {
	private $userId;
    private $service;

	public function __construct($AppName, IDBConnection $db, IRootFolder $root, IRequest $request, $UserId, IConfig $config){
		parent::__construct($AppName, $request);
        $this->userId = $UserId;

        $this->service = new RecipeService($root, $UserId, $db, $config);
	}

	/**
	 * CAUTION: the @Stuff turns off security checks; for this page no admin is
	 *          required and no CSRF check. If you don't know what CSRF is, read
	 *          it up in the docs or you might create a security hole. This is
	 *          basically the only required method to add this exemption, don't
	 *          add it to any other method if you don't exactly know what it does
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
    public function index() {
        $view_data = [
            'all_recipes' => $this->service->getAllRecipesInSearchIndex(),
            'all_keywords' => $this->service->getAllKeywordsInSearchIndex(),
            'folder' => $this->service->getUserFolderPath(),
            'current_node' => isset($_GET['recipe']) ? $this->service->getRecipeFileById($_GET['recipe']) : null
        ];

        return new TemplateResponse('cookbook', 'index', $view_data);  // templates/index.php
    }    
    
    /**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
    public function recipe() {
        $view_data = [
            'current_node' => isset($_GET['id']) ? $this->service->getRecipeFileById($_GET['id']) : null
        ];

        $response = new TemplateResponse('cookbook', 'recipe', $view_data);  // templates/recipe.php

        $response->renderAs('blank');

        return $response;
    }    
}
