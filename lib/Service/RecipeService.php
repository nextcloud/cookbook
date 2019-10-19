<?php

namespace OCA\Cookbook\Service;

use OCP\Image;
use OCP\IConfig;
use OCP\Files\IRootFolder;
use OCP\Files\FileInfo;
use OCP\Files\File;
use OCP\Files\Folder;
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
        $file = $this->getRecipeFileByFolderId($id);

        if(!$file) { return null; }

        return $this->parseRecipeFile($file);
    }

    /**
     * Returns a recipe file by folder id
     *
     * @param int $id
     *
     * @return \OCP\Files\File
     */
    public function getRecipeFileByFolderId($id) {
        $user_folder = $this->getFolderForUser();
        $recipe_folder = $user_folder->getById($id);

        if(count($recipe_folder) <= 0) { return null; }

        $recipe_folder = $recipe_folder[0];

        if($recipe_folder instanceof Folder === false) { return null; }

        foreach($recipe_folder->getDirectoryListing() as $file) {
            if($this->isRecipeFile($file)) { return $file; }
        }

        return null;
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

        // Make sure that "image" is a string of the highest resolution image available
        if(isset($json['image']) && $json['image']) {
            if(is_array($json['image'])) {
                // Get the image from a subproperty "url"
                if(isset($json['image']['url'])) {
                    $json['image'] = $json['image']['url'];

                // Try to get the image with the highest resolution by adding together all numbers in the url
                } else {
                    $images = $json['image'];
                    $image_size = 0;

                    foreach($images as $img) {
                        if(is_array($img) && isset($img['url'])) {
                            $img = $img['url'];
                        }

                        $image_matches = [];

                        preg_match_all('!\d+!', $img, $image_matches);

                        $this_image_size = 0;

                        foreach($image_matches as $image_match) {
                            $this_image_size += (int) $image_match;
                        }

                        if($image_size === 0 || $this_image_size > $image_size) {
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

        // Clean up the image URL string
        $json['image'] = stripslashes($json['image']);

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

        // Make sure that "keywords" is an array of unique strings
        if(isset($json['keywords']) && is_string($json['keywords'])) {
          $keywords = trim($json['keywords'], " \0\t\n\x0B\r,");
          $keywords = strip_tags($keywords);
          $keywords = preg_replace('/\s+/', ' ', $keywords); // Colapse whitespace
          $keywords = preg_replace('/(, | ,|,)+/', ',', $keywords); // Clean up separators
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
                        $json['recipeInstructions'][$i] = $this->cleanUpString($step, true);
                    } else if(is_array($step) && isset($step['text'])) {
                        $json['recipeInstructions'][$i] = $this->cleanUpString($step['text'], true);
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
                } else {
                    $json['recipeInstructions'] = explode(PHP_EOL, $json['recipeInstructions']);
                }
            } else {
                $json['recipeInstructions'] = [];
            }
        } else {
            $json['recipeInstructions'] = [];
        }

        $json['recipeInstructions'] = array_filter($json['recipeInstructions'], function($v) {
            return !empty($v) && $v !== "\n" && $v !== "\r";
        });

      	// Make sure the 'description' is a string
      	if(isset($json['description']) && is_string($json['description'])) {
      		$json['description'] = $this->cleanUpString($json['description']);
      	} else {
      		$json['description'] = "";
      	}

      	// Make sure the 'url' is a URL, or blank
        if(isset($json['url']) && $json['url']) {
            $url = filter_var($json['url'], FILTER_SANITIZE_URL);
            if (filter_var($url, FILTER_VALIDATE_URL) == false) {
                $url = "";
            }
            $json['url'] = $url;
        } else {
            $json['url'] = "";
        }
        // Make sure 'prepTime' is a string and valid DateInterval
        // regex validation from here: https://stackoverflow.com/a/32045167
        $interval_regex = "/^P(?!$)(\d+Y)?(\d+M)?(\d+W)?(\d+D)?(T(?=\d)(\d+H)?(\d+M)?(\d+S)?)?$/";
        if(isset($json['prepTime']) && is_string($json['prepTime'])) {
            $prep_string = $this->cleanUpString($json['prepTime']);
            if(preg_match_all($interval_regex, $prep_string)) {
                $json['prepTime'] = $prep_string;
            } else {
                $json['prepTime'] = "";
            }
        } else {
            $json['prepTime'] = "";
        }

        // Make sure 'cookTime' is a string and valid DateInterval
        if(isset($json['cookTime']) && is_string($json['cookTime'])) {
            $cook_string = $this->cleanUpString($json['cookTime']);
            if(preg_match_all($interval_regex, $cook_string)) {
                $json['cookTime'] = $cook_string;
            } else {
                $json['cookTime'] = "";
            }
        } else {
            $json['cookTime'] = "";
        }


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
        preg_match_all('/<script type=["|\']application\/ld\+json["|\'][^>]*>([\s\S]*?)<\/script>/', $html, $json_matches, PREG_SET_ORDER);
        foreach($json_matches as $json_match) {
            if(!$json_match || !isset($json_match[1])) { continue; }

            $string = $json_match[1];

            if(!$string) { continue; }

            $json = json_decode($string, true);

            // Look through @graph field for recipe
            if($json && isset($json['@graph']) && is_array($json['@graph'])) {
                foreach($json['@graph'] as $graph_item) {
                    if(!isset($graph_item['@type']) || $graph_item['@type'] !== 'Recipe') { continue; }

                    $json = $graph_item;
                    break;
                }
            }

            if(!$json || !isset($json['@type']) || $json['@type'] !== 'Recipe') { continue; }

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
        $user_folder = $this->getFolderForUser();
        $recipe_folder = $user_folder->getById($id);

        if($recipe_folder && sizeof($recipe_folder) > 0) {
            $recipe_folder[0]->delete();
        }

        $this->db->deleteRecipeById($id);
    }

    /**
     * @param array $json
     *
     * @return \OCP\Files\File
     */
    public function addRecipe($json) {
        if(!$json || !isset($json['name']) || !$json['name']) { throw new \Exception('Recipe name not found'); }

        $json = $this->checkRecipe($json);

        $user_folder = $this->getFolderForUser();
        $recipe_folder = null;
        $recipe_file = null;

        try {
            if(isset($json['id']) && $json['id']) {
                $recipe_folder = $user_folder->getById($json['id'])[0];

                $old_path = $recipe_folder->getPath();
                $new_path = dirname($old_path) . '/' . $json['name'];

                if($old_path !== $new_path) {
                    $recipe_folder->move($new_path);
                }

            } else {
                $recipe_folder = $user_folder->get($json['name']);
            }

        } catch(\OCP\Files\NotFoundException $e) {
            $recipe_folder = $user_folder->newFolder($json['name']);
        }

        $recipe_file = $this->getRecipeFileByFolderId($recipe_folder->getId());

        if(!$recipe_file) {
            $recipe_file = $recipe_folder->newFile($json['name'] . '.json');
        }

        $recipe_file->putContent(json_encode($json));

        try {
            $recipe_folder->get('full.jpg')->delete();
        } catch(\OCP\Files\NotFoundException $e) {}

        try {
            $recipe_folder->get('thumb.jpg')->delete();
        } catch(\OCP\Files\NotFoundException $e) {}

        $this->db->indexRecipeFile($recipe_file, $this->userId);

        return $recipe_file;
    }

    /**
     * @param string $url
     *
     * @return \OCP\Files\File
     */
    public function downloadRecipe($url) {
        $host = parse_url($url);

        if(!$host) { throw new \Exception('Could not parse URL'); }

        $opts = [
    		"http" => [
        		"method" => "GET",
        		"header" => "User-Agent: Nextcloud Cookbook App"
    		]
		];
		$context = stream_context_create($opts);

        $html = file_get_contents($url, false, $context);

        if(!$html) { throw new \Exception('Could not fetch site ' . $url); }

        $json = $this->parseRecipeHtml($html);

        if(!$json) { throw new \Exception('No recipe data found'); }

        $json['url'] = $url;

        return $this->addRecipe($json);
    }

    /**
     * @return array
     */
    public function getRecipeFiles() {
        $user_folder = $this->getFolderForUser();
        $recipe_folders = $user_folder->getDirectoryListing();
        $recipe_files = [];

        foreach($recipe_folders as $recipe_folder) {
            $recipe_file = $this->getRecipeFileByFolderId($recipe_folder->getId());

            if(!$recipe_file) { continue; }

            $recipe_files[] = $recipe_file;
        }

        return $recipe_files;
    }

    /**
     * Rebuilds the search index and removes cached images
     */
    public function rebuildSearchIndex() {
        // Clear the database
        $this->db->emptySearchIndex($this->userId);

        // Rebuild info
        $this->updateSearchIndex();
    }

    /**
     * Updates the search index
     */
    public function updateSearchIndex() {
        // Remove old cache folder if needed
        $legacy_cache_path = '/cookbook/cache';

        if($this->root->nodeExists($legacy_cache_path)) {
            $this->root->get($legacy_cache_path)->delete();
        }

        // Restructure files if needed
        $user_folder = $this->getFolderForUser();

        foreach($user_folder->getDirectoryListing() as $node) {
            // Move JSON files from the user directory into its own folder
            if($this->isRecipeFile($node)) {
                $recipe_name = str_replace('.json', '', $node->getName());

                $node->move($node->getPath() . '_tmp');

                $recipe_folder = $user_folder->newFolder($recipe_name);

                $node->move($recipe_folder->getPath() . '/' . $recipe_name . '.json');

            // Rename folders with .json extensions (this was likely caused by a migration bug)
            } else if($node instanceof Folder && strpos($node->getName(), '.json')) {
                $node->move(str_replace('.json', '', $node->getPath()));

            }
        }

        // Re-index recipe files
        foreach($this->getRecipeFiles() as $file) {
            $this->db->indexRecipeFile($file, $this->userId);
        }

        // Cache the last index update
        $this->config->setUserValue($this->userId, 'cookbook', 'last_index_update', time());
    }

    /**
     * Checks if a search index update is needed and performs it
     */
    private function checkSearchIndexUpdate() {
        $last_index_update = $this->getSearchIndexLastUpdateTime();
        $interval = $this->getSearchIndexUpdateInterval();

        if($last_index_update < 1 || time() > $last_index_update + ($interval * 60)) {
            $this->updateSearchIndex();
        }
    }

    /**
     * Gets the last time the search index was updated
     */
    public function getSearchIndexLastUpdateTime() {
        return (int) $this->config->getUserValue($this->userId, 'cookbook', 'last_index_update');
    }

    /**
     * Gets all keywords from the index
     *
     * @return array
     */
    public function getAllKeywordsInSearchIndex() {
        $this->checkSearchIndexUpdate();

        return $this->db->findAllKeywords($this->userId);
    }

    /**
     * Gets all recipes from the index
     *
     * @return array
     */
    public function getAllRecipesInSearchIndex() {
        $this->checkSearchIndexUpdate();

        return $this->db->findAllRecipes($this->userId);
    }

    /**
     * Search for recipes by keywords
     *
     * @param string $keywords
     *
     * @return array
     */
    public function findRecipesInSearchIndex($keywords_string) {
        $this->checkSearchIndexUpdate();

        $keywords_string = strtolower($keywords_string);
        $keywords_array = [];
        preg_match_all('/[^ ,]+/', $keywords_string, $keywords_array);

        if(sizeof($keywords_array) > 0) {
            $keywords_array = $keywords_array[0];
        }

        return $this->db->findRecipes($keywords_array, $this->userId);
    }

    /**
     * @param string $path
     */
    public function setUserFolderPath(string $path) {
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
     * @param int $interval
     */
    public function setSearchIndexUpdateInterval(int $interval) {
        $this->config->setUserValue($this->userId, 'cookbook', 'update_interval', $interval);
    }

    /**
     * @return int
     */
    public function getSearchIndexUpdateInterval() {
        $interval = (int) $this->config->getUserValue($this->userId, 'cookbook', 'update_interval');

        if($interval < 1) { $interval = 5; }

        return $interval;
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

        $json['id'] = $file->getParent()->getId();

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
    public function getRecipeImageFileByFolderId($id, $size = 'thumb') {
        if(!$size) { $size = 'thumb'; }
        if($size !== 'full' && $size !== 'thumb') {
            throw new \Exception('Image size "' . $size . '" not recognised');
        }

        $recipe_folder = $this->root->getById($id);

        if(sizeof($recipe_folder) < 1) { throw new \Exception('Recipe ' . $id . ' not found'); }

        $recipe_folder = $recipe_folder[0];

        $image_file = null;
        $image_filename = $size . '.jpg';

        try {
            $image_file = $recipe_folder->get($image_filename);
        } catch(\OCP\Files\NotFoundException $e) {
            $image_file = null;
        }

        if($image_file && $this->isImage($image_file)) { return $image_file; }

        $recipe_json = $this->getRecipeById($id);

        if(!isset($recipe_json['image']) || !$recipe_json['image']) { throw new \Exception('No image specified in recipe'); }

        try {
            if(strpos($recipe_json['image'], 'http') === 0) {
                $recipe_json['image'] = str_replace(' ', '%20', $recipe_json['image']);
                $image_data = file_get_contents($recipe_json['image']);
            } else {
                $image_file = $this->root->get('/' . $this->userId . '/files' . $recipe_json['image']);
                $image_data = $image_file->getContent();
            }
        } catch(\OCP\Files\NotFoundException $e) {
            throw new \Exception($e->getMessage());
        }

        if(!$image_data) { throw new \Exception('Could not fetch image from ' . $recipe_json['image']); }

        if($size === 'thumb') {
            $img = new Image();
            $img->loadFromData($image_data);
            $img->resize(128);
            $img->centerCrop();
            $image_data = $img->data();
        }

        $image_file = $recipe_folder->newFile($image_filename);
        $image_file->putContent($image_data);

        return $image_file;
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
    private function cleanUpString($str, $preserve_newlines = false) {
        if(!$str) { return ''; }

        $str = strip_tags($str);

        if(!$preserve_newlines) {
            $str = str_replace(["\r", "\n"], '', $str);
        }

        $str = str_replace(["\t", "\\"], '', $str);

        $str = html_entity_decode($str);

        return $str;
    }
}

?>
