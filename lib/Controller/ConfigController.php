<?php

namespace OCA\Cookbook\Controller;

use OCP\IConfig;
use OCP\IRequest;
use OCP\IDBConnection;
use OCP\Files\IRootFolder;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataDisplayResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\FileDisplayResponse;
use OCP\AppFramework\Controller;

use OCA\Cookbook\Service\RecipeService;
use OCP\IURLGenerator;

class ConfigController extends Controller
{
    /**
     * @var RecipeService
     */
    private $service;
    /**
     * @var IURLGenerator
     */
    private $urlGenerator;

    public function __construct($AppName, IRequest $request, IURLGenerator $urlGenerator, RecipeService $recipeService)
    {
        parent::__construct($AppName, $request);

        $this->service = $recipeService;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function config()
    {
        if (isset($_POST['folder'])) {
            $this->service->setUserFolderPath($_POST['folder']);
            $this->service->rebuildSearchIndex();
        }

        if (isset($_POST['update_interval'])) {
            $this->service->setSearchIndexUpdateInterval($_POST['update_interval']);
        }

        if (isset($_POST['print_image'])) {
            $this->service->setPrintImage((bool)$_POST['print_image']);
        }

        return new DataResponse('OK', Http::STATUS_OK);
    }
	
    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function reindex()
    {
        $this->service->rebuildSearchIndex();

        return new DataResponse('Search index rebuilt successfully', Http::STATUS_OK);
    }
}
