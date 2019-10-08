<?php
namespace OCA\Cookbook\Controller;

use OCP\IConfig;
use OCP\IRequest;
use OCP\IDBConnection;
use OCP\IURLGenerator;
use OCP\Files\IRootFolder;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
use OCA\Cookbook\Service\RecipeService;

class PageController extends Controller
{
    private $userId;
    private $service;
    private $urlGenerator;

    public function __construct($AppName, IDBConnection $db, IRootFolder $root, IRequest $request, $UserId, IConfig $config, IURLGenerator $urlGenerator)
    {
        parent::__construct($AppName, $request);
        $this->userId = $UserId;

        $this->service = new RecipeService($root, $UserId, $db, $config);
        $this->urlGenerator = $urlGenerator;
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
    public function index()
    {
        $view_data = [
            'all_recipes' => $this->service->getAllRecipesInSearchIndex(),
            'all_keywords' => $this->service->getAllKeywordsInSearchIndex(),
            'folder' => $this->service->getUserFolderPath(),
            'update_interval' => $this->service->getSearchIndexUpdateInterval(),
            'last_update' => $this->service->getSearchIndexLastUpdateTime(),
            'current_node' => isset($_GET['recipe']) ? $this->service->getRecipeFileByFolderId($_GET['recipe']) : null
        ];

        return new TemplateResponse('cookbook', 'index', $view_data);  // templates/index.php
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function recipe()
    {
        if (!isset($_GET['id'])) {
            return new DataResponse('Paramater "id" is required', 400);
        }

        try {
            $recipe = $this->service->getRecipeById($_GET['id']);
            $recipe['imageURL'] = $this->urlGenerator->linkToRoute('cookbook.recipe.image', ['id' => $_GET['id'], 'size' => 'full']);
            $recipe['id'] = $_GET['id'];
            $response = new TemplateResponse('cookbook', 'content/recipe', $recipe);
            $response->renderAs('blank');

            return $response;
        } catch (\Exception $e) {
            return new DataResponse($e->getMessage(), 502);
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function edit()
    {
        if (!isset($_GET['id']) && !isset($_GET['new'])) {
            return new DataResponse('Paramater "id" or "new" is required', 400);
        }

        try {
            $recipe = [];

            if (isset($_GET['id'])) {
                $recipe = $this->service->getRecipeById($_GET['id']);
            
                if(!$recipe) { throw new \Exception('Recipe ' . $_GET['id'] . ' not found'); }

                $recipe['id'] = $_GET['id'];
            }

            $response = new TemplateResponse('cookbook', 'content/edit', $recipe);
            $response->renderAs('blank');

            return $response;
        } catch (\Exception $e) {
            return new DataResponse($e->getMessage(), 502);
        }
    }
}
