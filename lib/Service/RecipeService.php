<?php
namespace OCA\Cookbook\Service;

use OCP\Files\IRootFolder;
use OCP\Files\FileInfo;
use OCP\IDBConnection;

use OCA\Cookbook\Db\RecipeDb;

class RecipeService {
    private $root;
    private $userId;
    private $db;

    public function __construct($root, $userId, IDBConnection $db) {
        $this->userId = $userId;
        $this->root = $root;
        $this->db = new RecipeDb($db);
	}

    /**
     * @param int $id
     * @return \OCP\Files\File
     */
    public function getRecipeFileById ($id) {
        $folder = $this->getFolderForUser();
        $file = $folder->getById($id);
        if(count($file) <= 0 || !$this->isRecipe($file[0])) {
            return null;
        }
        return $file[0];
    }
    
    /**
	 * Get recipe files in given directory
	 * @return array
	 */
    public function getRecipeFiles() {
        $folder = $this->getFolderForUser();

        $nodes = $folder->getDirectoryListing();
        $recipes = [];

        foreach($nodes as $node) {
			if($this->isRecipe($node)) {
				$recipes[] = $node;
			}
        }

        return $recipes;
    }

    /**
     * Gets all recipes from the index
     * @return array
     */
    public function getAllRecipesInSearchIndex() {
        return $this->db->findAllRecipes(); 
    }

    /**
     * Searches for recipes by ingredients and keywords
     *
     * @param string $ingredients
     * @param string $keywords
     *
     * @return array
     */
    public function findRecipesInSearchIndex($ingredients, $keywords, $limit = null, $offset = null) {
        return $this->db->findRecipes(explode(',', $ingredients), explode(',', $keywords), $limit, $offset);
    }

    /**
     * @return Folder
     */
    public function getFolderForUser() {
        $path = '/' . $this->userId . '/files/' . 'YipHan/Recipes';//$this->settings->get($userId, 'notesPath');
        
        return $this->getOrCreateFolder($path);
    }
    
    /**
     * @param int $id
     * @return Folder
     */
    public function getFolderForCache($id) {
        $path = '/cookbook/cache/' . $id;
        
        return $this->getOrCreateFolder($path);
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
     * Gets the recipe contents as an array
     *
     * @param \OCP\Files\File
     *
     * @return Array
     */
    public function parseRecipeFile($file) {
        if(!$file) { return null; }

        $json = json_decode($file->getContent(), true);

        if(!$json) { return null; }

        $json['id'] = $file->getId();

        return $json;
    } 

    /**
     * Gets the image file for a recipe
     *
     * @param int $id
     * @param string $size
     *
     * @return \OCP\Files\File
     */
    public function getRecipeImageFileById($id, $size = 'thumb') {
        if(!$size) { $size = 'thumb'; }
        if($size !== 'full' && $size !== 'thumb') { 
            throw new \Exception('Image size "' . $size . '" not recognised');
        }

        $folder = $this->getFolderForCache($id);
        $file = null;

        try {
            $file = $folder->get($size . '.jpg');
        } catch(\OCP\Files\NotFoundException $e) {
            $file = null;
        }

        if($file && $this->isImage($file)) { return $file; }

        $recipe_json = $this->parseRecipeFile($this->getRecipeFileById($id));

        if(!isset($recipe_json['image']) || !$recipe_json['image']) { return null; }  
    
        $recipe_image_data = file_get_contents($recipe_json['image']);

        if(!$recipe_image_data) { return null; }

        // TODO: Resize image
        $folder->newFile($size . '.jpg');
        $file = $folder->get($size . '.jpg');
        $file->putContent($recipe_image_data);

        return $file;
    }
    
    /**
     * Test if file is an image
     *
     * @param \OCP\Files\File $file
     * @return bool
     */
    private function isImage($file) {
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        if($file->getType() !== 'file') return false;
        $ext = pathinfo($file->getName(), PATHINFO_EXTENSION);
        $iext = strtolower($ext);
        if(!in_array($iext, $allowedExtensions)) {
            return false;
        }
        return true;
    }

    /**
     * Test if file is a recipe
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
