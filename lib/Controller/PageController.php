<?php
namespace OCA\Cookbook\Controller;

use OCP\IRequest;
use OCP\Files\IRootFolder;
use OCP\Files\FileInfo;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\FileDisplayResponse;
use OCP\AppFramework\Controller;

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

class PageController extends Controller {
	private $userId;
    private $root;

	public function __construct($AppName, IRootFolder $root, IRequest $request, $UserId){
		parent::__construct($AppName, $request);
        $this->userId = $UserId;
        $this->root = $root;
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
        $folder = $this->getFolderForUser();
        $all_nodes = $this->gatherRecipeFiles($folder);
        $current_node = null;

        if(isset($_GET['recipe'])) {
            $current_node = $this->getFileById($folder, $_GET['recipe']);
        }

        return new TemplateResponse('cookbook', 'index', [ 'all_nodes' => $all_nodes, 'current_node' => $current_node ]);  // templates/index.php
    }
    
    /**
	 * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function image() {
        if(!isset($_GET['url'])) { return null; }

        $url = base64_decode($_GET['url']);
        $data = file_get_contents($url);

        $file = new RemoteFile($data);

        return new FileDisplayResponse($file, Http::STATUS_OK, [ 'Content-Type' => 'image/jpeg' ]);
    }

    /**
     * @param Folder $folder
     * @param int $id
     * @throws NoteDoesNotExistException
     * @return \OCP\Files\File
     */
    private function getFileById ($folder, $id) {
        $file = $folder->getById($id);
        if(count($file) <= 0 || !$this->isRecipe($file[0])) {
            throw new NoteDoesNotExistException();
        }
        return $file[0];
    }

    /**
	 * gather recipe files in given directory and all subdirectories
	 * @param Folder $folder
	 * @return array
	 */
	private function gatherRecipeFiles ($folder) {
        $nodes = $folder->getDirectoryListing();
        $recipes = [];

        foreach($nodes as $node) {
			if($node->getType() === FileInfo::TYPE_FOLDER) {
				$recipes = array_merge($recipes, $this->gatherRecipeFiles($node));
				continue;
			}
			if($this->isRecipe($node)) {
				$recipes[] = $node;
			}
        }

        return $recipes;
    }

    /**
     * @return Folder
     */
    private function getFolderForUser() {
        $path = '/' . $this->userId . '/files/' . 'YipHan/Recipes';//$this->settings->get($userId, 'notesPath');
        try {
            $folder = $this->getOrCreateFolder($path);
        } catch(\Exception $e) {
            throw new NotesFolderException($path);
        }
        return $folder;
    }

     /**
     * Finds a folder and creates it if non-existent
     * @param string $path path to the folder
     * @return Folder
     */
    private function getOrCreateFolder($path) {
        if ($this->root->nodeExists($path)) {
            $folder = $this->root->get($path);
        } else {
            $folder = $this->root->newFolder($path);
        }
        return $folder;
    }

    /**
     * test if file is a recipe
     *
     * @param \OCP\Files\File $file
     * @return bool
     */
    private function isRecipe($file) {
        $allowedExtensions = ['json'];
        if($file->getType() !== 'file') return false;
        $ext = pathinfo($file->getName(), PATHINFO_EXTENSION);
        $iext = strtolower($ext);
        if(!in_array($iext, $allowedExtensions)) {
            return false;
        }
        return true;
    }
}
