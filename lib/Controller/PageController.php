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
    protected $appName;

    private $service;
    private $urlGenerator;

    public function __construct(string $AppName, IRequest $request, RecipeService $recipeService, IURLGenerator $urlGenerator)
    {
        parent::__construct($AppName, $request);

        $this->service = $recipeService;
        $this->urlGenerator = $urlGenerator;
        $this->appName = $AppName;
    }

    /**
     * Load the start page of the app.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index(): TemplateResponse
    {
        $view_data = [
            'all_keywords' => $this->service->getAllKeywordsInSearchIndex(),
            'folder' => $this->service->getUserFolderPath(),
            'update_interval' => $this->service->getSearchIndexUpdateInterval(),
            'last_update' => $this->service->getSearchIndexLastUpdateTime(),
        ];

        return new TemplateResponse($this->appName, 'index', $view_data);  // templates/index.php
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function home()
    {
        $response = new TemplateResponse($this->appName, 'navigation/home');
        $response->renderAs('blank');

        return $response;
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
            $response = new TemplateResponse($this->appName, 'content/recipe', $recipe);
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

            $response = new TemplateResponse($this->appName, 'content/edit', $recipe);
            $response->renderAs('blank');

            return $response;
        } catch (\Exception $e) {
            return new DataResponse($e->getMessage(), 502);
        }
    }
}
