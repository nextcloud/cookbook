<?php
namespace OCA\Cookbook\Service;

use OCP\IConfig;
use OCP\Files\IRootFolder;
use OCP\Files\FileInfo;
use OCP\IDBConnection;

use OCA\Cookbook\Db\RecipeDb;

class RecipeService {
    private $root;
    private $userId;
    private $db;
    private $config;

    public function __construct($root, $userId, IDBConnection $db, IConfig $config) {
        $this->userId = $userId;
        $this->root = $root;
        $this->db = new RecipeDb($db);
        $this->config = $config;
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
     * @param string $html
     * @return array 
     */
    private function parseRecipeHtml($html) {
        if(!$html) { return null; }
        
        $html = str_replace(["\r", "\n", "\t"], '', $html);
        
        $recipe = null;

        // Check for JSON
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
                'name' => isset($json['name']) ? $json['name'] : '',
                'image' => $this->parseRecipeImage($json),
                'recipeYield' => $this->parseRecipeYield($json),
                'keywords' => $this->parseRecipeKeywords($json),
                'recipeIngredient' => $this->parseRecipeIngredients($json),
                'recipeInstructions' => $this->parseRecipeInstructions($json),
            ];
            break;
        }

        // Check HTML
        foreach(['name', 'image', 'recipeYield', 'keywords', 'recipeIngredient', 'ingredients', 'recipeInstructions'] as $key) {
            $regex_matches = [];
            preg_match_all('/itemprop="' . $key . '"[^>]*>(.*?)</', $html, $regex_matches, PREG_SET_ORDER);

            foreach($regex_matches as $regex_match) {
                if(!$regex_match || !isset($regex_match[1])) { continue; }

                $value = $regex_match[1];

                if(!$recipe) {
                    $recipe = [
                        '@context' => 'http://schema.org',
                        '@type' => 'Recipe',
                    ];
                }

                switch($key) {
                    case 'image':
                        $src_matches = [];
                        preg_match('/="http([^"]+)"/', $regex_match[0], $src_matches);

                        if(!isset($src_matches[1])) { break; } 

                        $src = 'http' . $src_matches[1];

                        if(isset($recipe[$key]) && strlen($recipe[$key]) < strlen($src)) { break; }

                        $recipe[$key] = $src;
                        break;

                    case 'recipeYield':
                        $recipe[$key] = $this->parseRecipeYield($value);
                        break;

                    case 'keywords':
                        $recipe[$key] = $this->parseRecipeKeywords($value);
                        break;

                    case 'recipeIngredient': case 'ingredients':
                        $key = 'recipeIngredient';
                        if(!$recipe[$key]) { $recipe[$key] = []; }
                        
                        array_push($recipe[$key], $this->cleanUpString($value));
                        break;

                    case 'recipeInstructions':
                        if(!$recipe[$key]) { $recipe[$key] = []; }
                        
                        array_push($recipe[$key], $this->cleanUpString($value));
                        break;

                    default:
                        $recipe[$key] = $value;
                }
            }
        }

        if($recipe && !isset($recipe['name']) || !$recipe['name']) { throw new \Exception('Recipe name not found'); }

        return $recipe;
    }
    
    /**
     * @param int $id
     */
    public function deleteRecipe(int $id) {
        $file_folder = $this->getFolderForUser();

        $file = $file_folder->getById($id);

        if($file && sizeof($file) > 0) {
            $file[0]->delete();
        }
        
        $this->db->deleteRecipeById($id);

        $cache_folder = $this->getFolderForCache($id);

        if($cache_folder) {
            $cache_folder->delete();
        }
    }
    
    /**
     * @param array $json
     * @return \OCP\Files\File
     */
    public function addRecipe($json) {
        if(!$json || !isset($json['name']) || !$json['name']) { throw new \Exception('Recipe name not found'); }

        $folder = $this->getFolderForUser();
        $file = null;
        $filename = $json['name'] . '.json';

        try {
            $file = $folder->get($filename);
        } catch(\OCP\Files\NotFoundException $e) {
            $folder->newFile($filename);
            $file = $folder->get($filename);
        }

        $file->putContent(json_encode($json));

        $this->db->indexRecipeFile($file);
        
        $cache_folder = $this->getFolderForCache($file->getId());

        if($cache_folder) {
            $cache_folder->delete();
        }

        return $file;
    }

    /**
     * @param string $url
     * @return \OCP\Files\File
     */
    public function downloadRecipe($url) {
        $host = parse_url($url);

        if(!$host) { throw new \Exception('Could not parse URL'); }
        
        $html = file_get_contents($url);

        if(!$html) { throw new \Exception('Could not fetch site ' . $url); }

        $json = $this->parseRecipeHtml($html); 

        if(!$json) { throw new \Exception('No recipe data found'); }

        return $this->addRecipe($json);
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
        $cache_folder = $this->getFolderForCache();
        $cache_folder->delete();

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
    public function findRecipesInSearchIndex($keywords_string) {
        $keywords_string = strtolower($keywords_string);
        $keywords_array = [];
        preg_match_all('/[^ ,]+/', $keywords_string, $keywords_array);

        if(sizeof($keywords_array) > 0) {
            $keywords_array = $keywords_array[0];
        }

        return $this->db->findRecipes($keywords_array);
    }

    /**
     * @param string $folder
     */
    public function setUserFolderPath($path) {
        $this->config->setUserValue($this->userId, 'cookbook', 'folder', $path);
    }
    
    /**
     * @return string
     */
    public function getUserFolderPath() {
        $path = $this->config->getUserValue($this->userId, 'cookbook', 'folder');

        if(!$path) { $path = '/Recipes'; }

        return $path;
    }

    /**
     * @return Folder
     */
    public function getFolderForUser() {
        $path = '/' . $this->userId . '/files/' . $this->getUserFolderPath();
        $path = str_replace('//', '/', $path);

        return $this->getOrCreateFolder($path);
    }
    
    /**
     * @param int $id
     * @return Folder
     */
    public function getFolderForCache($id = '') {
        $path = '/cookbook/cache';
        
        if($id) {
            $path .= '/' . $id;
        }
        
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
        $filename = $size . '.jpg';

        try {
            $file = $folder->get($filename);
        } catch(\OCP\Files\NotFoundException $e) {
            $file = null;
        }

        if($file && $this->isImage($file)) { return $file; }

        $recipe_json = $this->parseRecipeFile($this->getRecipeFileById($id));

        if(!isset($recipe_json['image']) || !$recipe_json['image']) { throw new \Exception('No image specified in recipe'); }  
    
        $recipe_image_data = file_get_contents($recipe_json['image']);

        if(!$recipe_image_data) { throw new \Exception('Could not fetch image from ' . $recipe_json['image']); }

        if($size === 'thumb') {
            $img = new \OC_Image();
            $img->loadFromData($recipe_image_data);
            $img->resize(128);
            $img->centerCrop();
            $recipe_image_data = $img->data();
        }

        $folder->newFile($filename);
        $file = $folder->get($filename);
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
        $keywords = explode(',', $keywords);
        $keywords = array_unique($keywords);
        $keywords = implode(',', $keywords);

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
            $ingredient = $this->cleanUpString($ingredient);

            if(!$ingredient) { continue; }

            array_push($ingredients, $ingredient);
        }

        return $ingredients;
    }
    
    /**
     * @param string $str
     * @return string
     */  
    private function cleanUpString($str) {
        if(!$str) { return ''; }
        
        $str = strip_tags($str);
        $str = str_replace(["\r", "\n", "\t", "\\"], '', $str);

        return $str;
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

            $string = $this->cleanUpString(regex_match[1]);
                
            if(!$string) { continue; }

            array_push($instructions, [ '@type' => 'HowToStep', 'text' => $string ]);
        }

        if(sizeof($instructions) < 1) {
            array_push($instructions, [
                '@type' => 'HowToStep',
                'text' => $json['recipeInstructions'],
            ]);
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
