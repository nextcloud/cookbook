<?php
namespace OCA\Cookbook\Controller;

use OCP\IRequest;
use OCP\Files\IRootFolder;
use OCP\Files\FileInfo;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\FileDisplayResponse;
use OCP\AppFramework\Controller;
use OCA\Cookbook\Service\RecipeService;

class RemoteFile {
    private $data;
    
    public function __construct($data) {
        $this->data = $data;
    }

    public function getName() { return 'Image'; }
    public function getEtag() { return ''; }
    public function getMTime() { return date(); }
    public function getSize() { return strlen($this->data); }
    public function getContent() { return $this->data; }
}

class RecipeController extends Controller {
	private $userId;

	public function __construct($AppName, IRootFolder $root, IRequest $request, $UserId){
		parent::__construct($AppName, $request);
        $this->userId = $UserId;

        $this->service = new RecipeService($root, $UserId);
	}

    /**
	 * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function image() {
        if(!isset($_GET['recipe'])) { return null; }

        $size = isset($_GET['size']) ? $_GET['size'] : null;

        $file = $this->service->getRecipeImageFileById($_GET['recipe']);

        return new FileDisplayResponse($file, Http::STATUS_OK, [ 'Content-Type' => 'image/jpeg' ]);
    }
}
