<?php

namespace OCA\Cookbook\Service;

use OCP\Image;
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
     *
     * @return array
     */
    public function getRecipeById($id) {
        $file = $this->getRecipeFileById($id);

        if(!$file) { return null; }

        return $this->parseRecipeFile($file);
    }

    /**
     * @param int $id
     *
     * @return \OCP\Files\File
     */
    public function getRecipeFileById($id) {
        $folder = $this->getFolderForUser();
        $file = $folder->getById($id);

        if(count($file) <= 0 || !$this->isRecipeFile($file[0])) {
            return null;
        }

        return $file[0];
    }

    /**
     * Checks the fields of a recipe and standardises the format
     *
     * @param array $json
     *
     * @return array
     */
    public function checkRecipe($json) {
        if(!$json) { throw new \Exception('Recipe array was null'); }
        if(!isset($json['name']) || !$json['name']) { throw new \Exception('Field "name" is required'); }

        // Make sure the schema.org fields are present
        $json['@context'] = 'http://schema.org';
        $json['@type'] = 'Recipe';

        // Make sure that "name" doesn't have any funky characters in it
        $json['name'] = $this->cleanUpString($json['name']);

        // Make sure that "dailyDozen" is a comma-separated string
        if(isset($json['dailyDozen'])) {
            if(is_array($json['dailyDozen'])) {
                if(!isset($json['dailyDozen'][0])) {
                    $json['dailyDozen'] = array_keys($json['dailyDozen']);
                }

                $json['dailyDozen'] = implode(',', $json['dailyDozen']);
            } else if(!is_string($json['dailyDozen'])) {
                $json['dailyDozen'] = '';
            } else {
                $json['dailyDozen'] = preg_replace('/[^a-zA-Z,]/', '', $json['dailyDozen']);
            }
        } else {
            $json['dailyDozen'] = '';
        }

        // Make sure that "image" is a string of the highest resolution image available
        if(isset($json['image']) && $json['image']) {
            if(is_array($json['image'])) {
                if(isset($json['image']['url'])) {
                    $json['image'] = $json['image']['url'];
                } else {
                    $images = $json['image'];
                    foreach($images as $img) {
                        if(is_array($img) && isset($img['url'])) {
                            $img = $img['url'];
                        }

                        if(!$result || strlen($img) < strlen($json['image'])) {
                            $json['image'] = $img;
                        }
                    }
                }
            } else if(!is_string($json['image'])) {
                $json['image'] = '';
            }
        } else {
            $json['image'] = '';
        }

        // Make sure that "recipeYield" is an integer which is at least 1 
        if(isset($json['recipeYield']) && $json['recipeYield']) {
            $yield = filter_var($json['recipeYield'], FILTER_SANITIZE_NUMBER_INT);

            if($yield && $yield > 0) {
                $json['recipeYield'] = (int) $yield;
            } else {
                $json['recipeYield'] = 1;
            }
        } else {
            $json['recipeYield'] = 1;
        }

        // Make sure that "keywords" is an array of unique, lowercase strings
        if(isset($json['keywords']) && is_string($json['keywords'])) {
            $keywords = $json['keywords'];
            $keywords = strtolower($keywords);
            $keywords = strip_tags($keywords);
            $keywords = str_replace(' and', '', $keywords);
            $keywords = str_replace(' ', ',', $keywords);
            $keywords = str_replace(',,', ',', $keywords);
            $keywords = explode(',', $keywords);
            $keywords = array_unique($keywords);
            $keywords = implode(',', $keywords);
            $json['keywords'] = $keywords;
        } else {
            $json['keywords'] = '';
        }

        // Make sure that "recipeIngredient" is an array of strings
        if(isset($json['recipeIngredient']) && is_array($json['recipeIngredient'])) {
            $ingredients = [];

            foreach($json['recipeIngredient'] as $i => $ingredient) {
                $ingredient = $this->cleanUpString($ingredient);

                if(!$ingredient) { continue; }

                array_push($ingredients, $ingredient);
            }
        } else {
            $json['recipeIngredient'] = [];
        }

        $json['recipeIngredient'] = array_filter($json['recipeIngredient']);

        // Make sure that "recipeInstructions" is an array of strings
        if(isset($json['recipeInstructions'])) {
            if(is_array($json['recipeInstructions'])) {
                foreach($json['recipeInstructions'] as $i => $step) {
                    if(is_string($step)) {
                        $json['recipeInstructions'][$i] = $this->cleanUpString($step);
                    } else if(is_array($step) && isset($step['text'])) {
                        $json['recipeInstructions'][$i] = $this->cleanUpString($step['text']);
                    } else {
                        $json['recipeInstructions'][$i] = '';
                    }
                }

            } else if(is_string($json['recipeInstructions'])) {
                $json['recipeInstructions'] = html_entity_decode($json['recipeInstructions']);

                $regex_matches = [];
                preg_match_all('/<(p|li)>(.*?)<\/(p|li)>/', $json['recipeInstructions'], $regex_matches, PREG_SET_ORDER); 

                $instructions = [];

                foreach($regex_matches as $regex_match) {
                    if(!$regex_match || !isset($regex_match[2])) { continue; }

                    $step = $this->cleanUpString($regex_match[2]);

                    if(!$step) { continue; }

                    array_push($instructions, $step);
                }

                if(sizeof($instructions) > 0) {
                    $json['recipeInstructions'] = $instructions;
                }
            } else {
                $json['recipeInstructions'] = [];
            }
        } else {
            $json['recipeInstructions'] = []; 
        }

        $json['recipeInstructions'] = array_filter($json['recipeInstructions']);

        return $json;
    }

    /**
     * @param string $html
     *
     * @return array 
     */
    private function parseRecipeHtml($html) {
        if(!$html) { return null; }

        //$html = str_replace(["\r", "\n", "\t"], '', $html);
        $json_matches = [];

        // Parse JSON
        preg_match_all('/<script type="application\/ld\+json">([\s\S]*?)<\/script>/', $html, $json_matches, PREG_SET_ORDER);
        foreach($json_matches as $json_match) {
            if(!$json_match || !isset($json_match[1])) { continue; }

            $string = $json_match[1];

            if(!$string) { continue; }

            $json = json_decode($string, true);

            if(!$json || $json['@type'] !== 'Recipe') { continue; }

            return $this->checkRecipe($json);
        }

        // Parse HTML, if JSON couldn't be found
        $json = [];
        $article_matches = [];
        preg_match_all('/<article.*itemtype=".*Recipe".*>([\s\S]*?)<\/article>/', $html, $article_matches);

        if(!isset($article_matches[1][0])) {
            throw new \Exception('Could not find article element');
        }

        $article_html = $article_matches[1][0];

        $props = [
            'name',
            'image', 'images', 'thumbnail',
            'recipeYield',
            'keywords',
            'recipeIngredient', 'ingredients',
            'recipeInstructions', 'instructions', 'steps', 'guide',
        ];

        $prop_matches = [];

        foreach($props as $prop) {
            preg_match_all('/itemprop="' . $prop . '".*>(.*?)<\//', $article_html, $prop_matches, PREG_SET_ORDER);

            foreach($prop_matches as $prop_match) {
                if(!$prop_match || !isset($prop_match[1])) { continue; }

                $value = $prop_match[1];

                switch($prop) {
                case 'image': case 'images': case 'thumbnail':
                    $prop = 'image';
                    $src_matches = [];
                    preg_match('/="http([^"]+)"/', $prop_match[0], $src_matches);

                    if(!isset($src_matches[1])) { break; } 

                    $src = 'http' . $src_matches[1];

                    if(isset($json[$prop]) && strlen($json[$prop]) < strlen($src)) { break; }

                    $json[$prop] = $src;
                    break;

                case 'recipeIngredient': case 'ingredients':
                    $prop = 'recipeIngredient';
                    if(!$json[$prop]) { $json[$prop] = []; }

                    array_push($json[$prop], $value);
                    break;

                case 'recipeInstructions': case 'instructions': case 'steps': case 'guide':
                    $prop = 'recipeInstructions';
                    if(!$json[$prop]) { $json[$prop] = []; }

                    array_push($json[$prop], $value);
                    break;

                default:
                    if(isset($json[$prop]) && $json[$prop]) { break; }

                    $json[$prop] = $value;
                }
            }
        }

        // Make one final desparate attempt at getting the instructions
        if(!isset($json['recipeInstructions']) || !$json['recipeInstructions'] || sizeof($json['recipeInstructions']) < 1) {
            $step_matches = [];
            $json['recipeInstructions'] = [];
            preg_match_all('/<p.*>(.*?)<\/p>/', $article_html, $step_matches, PREG_SET_ORDER);

            foreach($step_matches as $step_match) {
                if(!$step_match || !isset($step_match[1])) { continue; }

                $value = $step_match[1];

                array_push($json['recipeInstructions'], $value); 
            }
        }

        // If no keywords were found, use the ingredients
        if(!isset($json['keywords']) || !$json['keywords']) {
            $json['keywords'] = '';

            if(isset($json['recipeIngredient'])) {
                foreach($json['recipeIngredient'] as $ingredient) {
                    $keyword = strip_tags($ingredient);
                    $keyword = strtolower($keyword);
                    $parts = array_filter(explode(' ', $keyword));
                    $keyword = array_pop($parts);

                    if($json['keywords']) { $json['keywords'] .= ','; }

                    $json['keywords'] .= $keyword;
                }
            }
        }

        return $this->checkRecipe($json);
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
     *
     * @return \OCP\Files\File
     */
    public function addRecipe($json) {
        if(!$json || !isset($json['name']) || !$json['name']) { throw new \Exception('Recipe name not found'); }

        $json = $this->checkRecipe($json);
        
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
     *
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
     * Rebuilds the search index and removes cached images
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
     *
     * @return array
     */
    public function getAllKeywordsInSearchIndex() {
        return $this->db->findAllKeywords(); 
    }

    /**
     * Gets all recipes from the index
     *
     * @return array
     */
    public function getAllRecipesInSearchIndex() {
        return $this->db->findAllRecipes(); 
    }

    /**
     * Search for recipes by keywords
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
     * @param string $path
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
     * @return \OCP\Files\Folder
     */
    public function getFolderForUser() {
        $path = '/' . $this->userId . '/files/' . $this->getUserFolderPath();
        $path = str_replace('//', '/', $path);

        return $this->getOrCreateFolder($path);
    }

    /**
     * @param int $id
     *
     * @return \OCP\Files\Folder
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
     *
     * @return \OCP\Files\Folder
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
     * Get recipe file contents as an array
     *
     * @param \OCP\Files\File $file
     *
     * @return array
     */
    public function parseRecipeFile($file) {
        if(!$file) { return null; }

        $json = json_decode($file->getContent(), true);

        if(!$json) { return null; }

        $json['id'] = $file->getId();

        return $this->checkRecipe($json);
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
            $img = new Image();
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
     *
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
     *
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
     * @param string $str
     *
     * @return string
     */  
    private function cleanUpString($str) {
        if(!$str) { return ''; }

        $str = strip_tags($str);
        $str = str_replace(["\r", "\n", "\t", "\\"], '', $str);
        $str = html_entity_decode($str);

        return $str;
    }
}

?>
