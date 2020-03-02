<?php
namespace OCA\Cookbook\Controller;

use OCP\IConfig;
use OCP\IRequest;
use OCP\IDBConnection;
use OCP\IURLGenerator;
use OCP\Files\IRootFolder;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
use OCA\Cookbook\Service\RecipeService;

class MainController extends Controller
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
    public function tags()
    {
		$tags = $this->service->getAllKeywordsInSearchIndex();
        return new DataResponse($tags, 200, ['Content-Type' => 'application/json']);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function home()
    {
        try {
			$recipes = $this->service->getAllRecipesInSearchIndex();
			
			foreach ($recipes as $i => $recipe) {
				$recipes[$i]['image_url'] = $this->urlGenerator->linkToRoute('cookbook.recipe.image', ['id' => $recipe['recipe_id'], 'size' => 'thumb']);
			}
			
			$response = new TemplateResponse($this->appName, 'content/search', ['recipes' => $recipes]);
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
    public function error()
    {
        $response = new TemplateResponse($this->appName, 'navigation/error');
        $response->renderAs('blank');

        return $response;
    }
	
    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function search($query)
    {
		$query = urldecode($query);
        try {
			$recipes = $this->service->findRecipesInSearchIndex($query);
			
			foreach ($recipes as $i => $recipe) {
				$recipes[$i]['image_url'] = $this->urlGenerator->linkToRoute('cookbook.recipe.image', ['id' => $recipe['recipe_id'], 'size' => 'thumb']);
			}
			
			$response = new TemplateResponse($this->appName, 'content/search', ['query' => $query, 'recipes' => $recipes]);
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
    public function tag($tag)
    {
		$tag = urldecode($tag);
        try {
			$recipes = $this->service->findRecipesInSearchIndex($tag);
			
			foreach ($recipes as $i => $recipe) {
				$recipes[$i]['image_url'] = $this->urlGenerator->linkToRoute('cookbook.recipe.image', ['id' => $recipe['recipe_id'], 'size' => 'thumb']);
			}
			
			$response = new TemplateResponse($this->appName, 'content/search', ['tag' => $tag, 'recipes' => $recipes]);
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
    public function recipe($id)
    {
        try {
            $recipe = $this->service->getRecipeById($id);
            $recipe['imageURL'] = $this->urlGenerator->linkToRoute('cookbook.recipe.image', ['id' => $id, 'size' => 'full']);
            $recipe['id'] = $id;
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
    public function create()
    {
        try {
            $recipe = [];
			
            $response = new TemplateResponse($this->appName, 'content/edit', $recipe);
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
	public function import()
	{
        if (!isset($_POST['url'])) {
            return new DataResponse('Field "url" is required', 400);
        }

        try {
            $recipe_file = $this->service->downloadRecipe($_POST['url']);
            $recipe_json = $this->service->parseRecipeFile($recipe_file);

            return new DataResponse($recipe_json, Http::STATUS_OK, ['Content-Type' => 'application/json']);
        } catch (\Exception $e) {
            return new DataResponse($e->getMessage(), 502);
        }
    }

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function new()
	{
		try {
	        $recipe_data = $_POST;
			$file = $this->service->addRecipe($recipe_data);
			
			return new DataResponse('#recipes/' . $file->getParent()->getId());
		} catch (\Exception $e) {
			return new DataResponse($e->getMessage(), 502);
		}
	}

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function edit($id)
    {
        try {
            $recipe = [];

            if ($id !== null) {
                $recipe = $this->service->getRecipeById($id);

                if(!$recipe) { throw new \Exception('Recipe ' . $id . ' not found'); }

                $recipe['id'] = $id;
            }
			
            $response = new TemplateResponse($this->appName, 'content/edit', $recipe);
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
    public function update($id)
    {
		try {
	        $recipeData = [];
	        parse_str(file_get_contents("php://input"), $recipeData);
			$recipeData['id'] = $id;
	        $file = $this->service->addRecipe($recipeData);
			
			return new DataResponse($id);
		} catch (\Exception $e) {
			return new DataResponse($e->getMessage(), 502);
		}
    }
}
