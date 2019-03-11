<?php
namespace OCA\Cookbook\Controller;

use OCP\IRequest;
use OCP\Files\IRootFolder;
use OCP\Files\FileInfo;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Controller;
use OCA\Cookbook\Service\RecipeService;

class PageController extends Controller {
	private $userId;
    private $service;

	public function __construct($AppName, IRootFolder $root, IRequest $request, $UserId){
		parent::__construct($AppName, $request);
        $this->userId = $UserId;

        $this->service = new RecipeService($root, $UserId);
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
        $all_nodes = $this->service->getRecipeFiles();
        $current_node = null;

        if(isset($_GET['recipe'])) {
            $current_node = $this->service->getRecipeFileById($_GET['recipe']);
        }

        return new TemplateResponse('cookbook', 'index', [ 'all_nodes' => $all_nodes, 'current_node' => $current_node ]);  // templates/index.php
    }    
}
