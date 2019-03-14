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
        if(count($file) <= 0 || !$this->isRecipeFile($file[0])) {
            return null;
        }
        return $file[0];
    }
    
    /**
     * @param string $url
     * @return \OCP\Files\File
     */
    public function downloadRecipe($url) {
        $host = parse_url($url);

        if(!$host) { throw new \Exception('Could not parse URL'); }
        
        $html = file_get_contents($url);

        if(!$html) { throw new \Exception('Could not fetch site'); }

        $html = str_replace(["\r", "\n"], '', $html);
        
        $regex_matches = [];
        preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $regex_matches, PREG_SET_ORDER);

        foreach($regex_matches as $regex_match) {
            if(!$regex_match || !isset($regex_match[1])) { continue; }

            $string = $regex_match[1];
            
            if(!$string) { continue; }
                
            $json = json_decode($string, true);

            if(!$json || $json['@type'] !== 'Recipe') { continue; }

            $recipe = [
                '@context' => 'http://schema.org',
                '@type' => 'Recipe',
                'name' => isset($json['name']) ? $json['name'] : 'No name',
                'image' => $this->parseRecipeImage($json),
                'recipeYield' => $this->parseRecipeYield($json),
                'keywords' => $this->parseRecipeKeywords($json),
                'recipeIngredient' => $this->parseRecipeIngredients($json),
                'recipeInstructions' => $this->parseRecipeInstructions($json),
            ];

            $folder = $this->getFolderForUser();
            $file = null;

            try {
                $file = $folder->get($recipe['name'] . '.json');
            } catch(\OCP\Files\NotFoundException $e) {
                $folder->newFile($recipe['name'] . '.json');
                $file = $folder->get($recipe['name'] . '.json');
            }

            $file->putContent(json_encode($recipe));

            $this->db->indexRecipeFile($file);
            
            $cache_folder = $this->getFolderForCache($file->getId());

            if($cache_folder) {
                $cache_folder->delete();
            }

            return $file;
        }

        throw new Exception('No recipe data found');
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
			if($this->isRecipeFile($node)) {
				$recipes[] = $node;
			}
        }

        return $recipes;
    }

    /**
     * Rebuilds the search index
     */
    public function rebuildSearchIndex() {
        $this->db->emptySearchIndex();
        
        foreach($this->getRecipeFiles() as $file) {
            $this->db->indexRecipeFile($file);
        }
    }
    
    /**
     * Gets all keywords from the index
     * @return array
     */
    public function getAllKeywordsInSearchIndex() {
        return $this->db->findAllKeywords(); 
    }

    /**
     * Gets all recipes from the index
     * @return array
     */
    public function getAllRecipesInSearchIndex() {
        return $this->db->findAllRecipes(); 
    }

    /**
     * Searches for recipes by keywords
     *
     * @param string $keywords
     *
     * @return array
     */
    public function findRecipesInSearchIndex($keywords) {
        return $this->db->findRecipes(explode(',', $keywords));
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

        $json['recipe_id'] = $file->getId();

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
    private function isRecipeFile($file) {
        $allowedExtensions = ['json'];
        if($file->getType() !== 'file') return false;
        $ext = pathinfo($file->getName(), PATHINFO_EXTENSION);
        $iext = strtolower($ext);
        if(!in_array($iext, $allowedExtensions)) {
            return false;
        }
        return true;
    }

    /**
     * @param array $json
     * @return string
     */
    private function parseRecipeKeywords($json) {
        if(!isset($json['keywords'])) { return ''; }

        $keywords = $json['keywords'];
        $keywords = strip_tags($keywords);
        $keywords = str_replace(' and', '', $keywords);
        $keywords = str_replace(' ', ',', $keywords);
        $keywords = str_replace(',,', ',', $keywords);

        return $keywords;
    }

    /**
     * @param array $json
     * @return array
     */  
    private function parseRecipeIngredients($json) {
        if(!isset($json['recipeIngredient']) || !is_array($json['recipeIngredient'])) { return []; }

        $ingredients = [];

        foreach($json['recipeIngredient'] as $i => $ingredient) {
            $ingredient = strip_tags($ingredient);
            $ingredient = str_replace(["\r", "\n", "\t", "\\"], '', $ingredient);

            if(!$ingredient) { continue; }

            array_push($ingredients, $ingredient);
        }

        return $ingredients;
    }

    /**
     * @param array $json
     * @return array
     */
    private function parseRecipeInstructions($json) {
        if(!isset($json['recipeInstructions'])) { return []; }

        $instructions = $json['recipeInstructions'];

        if(is_array($instructions)) { return $instructions; }

        $regex_matches = [];
        preg_match_all('/<p>(.*?)<\/p>/', htmlspecialchars_decode($instructions), $regex_matches, PREG_SET_ORDER); 

        $instructions = [];

        foreach($regex_matches as $regex_match) {
            if(!$regex_match || !isset($regex_match[1])) { continue; }

            $string = $regex_match[1];
            $string = strip_tags($string);
            $string = str_replace(["\r", "\n", "\t"], '', $string);
                
            if(!$string) { continue; }

            array_push($instructions, [ '@type' => 'HowToStep', 'text' => $string ]);
        }

        return $instructions;
    }
    
    /**
     * @param array json
     * @return int
     */
    private function parseRecipeYield($json) {
        if(!isset($json['recipeYield'])) { return 1; }
        
        $yield = filter_var($json['recipeYield'], FILTER_SANITIZE_NUMBER_INT);

        if(!$yield) { return 1; }

        return (int) $yield;
    }

    /**
     * @param array json
     * @return string
     */
    private function parseRecipeImage($json) {
        if(!isset($json['image'])) { return ''; }

        $image = $json['image'];

        if(!$image) { return ''; }
        if(is_string($image)) { return $image; }
        if(isset($image['url'])) { return $image['url']; }

        $result = '';

        foreach($image as $img) {
            if(is_array($img) && isset($img['url'])) {
                $img = $img['url'];
            }
            
            if(!$result || strlen($img) < strlen($result)) {
                $result = $img;
            }
        }

        return $result;
    }
}
