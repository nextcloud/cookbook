<?php

namespace OCA\Cookbook\Service;

use Exception;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\Image;
use OCP\IConfig;
use OCP\IL10N;
use OCP\Files\IRootFolder;
use OCP\Files\File;
use OCP\Files\Folder;
use OCA\Cookbook\Db\RecipeDb;
use OCP\PreConditionNotMetException;
use Psr\Log\LoggerInterface;
use OCA\Cookbook\Exception\UserFolderNotWritableException;
use OCA\Cookbook\Exception\RecipeExistsException;

/**
 * Main service class for the cookbook app.
 *
 * @package OCA\Cookbook\Service
 */
class RecipeService {
	private $root;
	private $user_id;
	private $db;
	private $config;
	private $il10n;
	private $logger;

	public function __construct(?string $UserId, IRootFolder $root, RecipeDb $db, IConfig $config, IL10N $il10n, LoggerInterface $logger) {
		$this->user_id = $UserId;
		$this->root = $root;
		$this->db = $db;
		$this->config = $config;
		$this->il10n = $il10n;
		$this->logger = $logger;
	}

	/**
	 * Get a recipe by its folder id.
	 *
	 * @param int $id
	 *
	 * @return array|null
	 */
	public function getRecipeById(int $id) {
		$file = $this->getRecipeFileByFolderId($id);

		if (!$file) {
			return null;
		}

		return $this->parseRecipeFile($file);
	}
	
	/**
	 * Get a recipe's modification time by its folder id.
	 *
	 * @param int $id
	 *
	 * @return int
	 */
	public function getRecipeMTime(int $id) {
		$file = $this->getRecipeFileByFolderId($id);

		if (!$file) {
			return null;
		}

		return $file->getMTime();
	}

	/**
	 * Returns a recipe file by folder id
	 *
	 * @param int $id
	 *
	 * @return File|null
	 */
	public function getRecipeFileByFolderId(int $id) {
		$user_folder = $this->getFolderForUser();
		$recipe_folder = $user_folder->getById($id);

		if (count($recipe_folder) <= 0) {
			return null;
		}

		$recipe_folder = $recipe_folder[0];

		if ($recipe_folder instanceof Folder === false) {
			return null;
		}

		foreach ($recipe_folder->getDirectoryListing() as $file) {
			if ($this->isRecipeFile($file)) {
				return $file;
			}
		}

		return null;
	}

	/**
	 * Checks the fields of a recipe and standardises the format
	 *
	 * @param array $json
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	public function checkRecipe(array $json): array {
		if (!$json) {
			throw new Exception('Recipe array was null');
		}
		
		if (empty($json['name'])) {
			throw new Exception('Field "name" is required');
		}

		// Make sure the schema.org fields are present
		$json['@context'] = 'http://schema.org';
		$json['@type'] = 'Recipe';

		// Make sure that "name" doesn't have any funky characters in it
		$json['name'] = $this->cleanUpString($json['name'], false, true);

		// Make sure that "image" is a string of the highest resolution image available
		if (isset($json['image']) && $json['image']) {
			if (is_array($json['image'])) {
				// Get the image from a subproperty "url"
				if (isset($json['image']['url'])) {
					$json['image'] = $json['image']['url'];

				// Try to get the image with the highest resolution by adding together all numbers in the url
				} else {
					$images = $json['image'];
					$image_size = 0;

					foreach ($images as $img) {
						if (is_array($img) && isset($img['url'])) {
							$img = $img['url'];
						}

						if (empty($img)) {
							continue;
						}
		
						$image_matches = [];

						preg_match_all('!\d+!', $img, $image_matches);

						$this_image_size = 0;

						foreach ($image_matches as $image_match) {
							$this_image_size += (int)$image_match;
						}

						if ($image_size === 0 || $this_image_size > $image_size) {
							$json['image'] = $img;
						}
					}
				}
			} elseif (!is_string($json['image'])) {
				$json['image'] = '';
			}
		} else {
			$json['image'] = '';
		}

		// The image is a URL without a scheme, fix it
		if (strpos($json['image'], '//') === 0) {
			if (isset($json['url']) && strpos($json['url'], 'https') === 0) {
				$json['image'] = 'https:' . $json['image'];
			} else {
				$json['image'] = 'http:' . $json['image'];
			}
		}

		// Clean up the image URL string
		$json['image'] = stripslashes($json['image']);

		// Last sanity check for URL
		if (!empty($json['image']) && (substr($json['image'], 0, 2) === '//' || $json['image'][0] !== '/')) {
			$image_url = parse_url($json['image']);

			if (!isset($image_url['scheme'])) {
				$image_url['scheme'] = 'http';
			}

			$json['image'] = $image_url['scheme'] . '://' . $image_url['host'] . $image_url['path'];

			if (isset($image_url['query'])) {
				$json['image'] .= '?' . $image_url['query'];
			}
		}
		

		// Make sure that "recipeCategory" is a string
		if (isset($json['recipeCategory'])) {
			if (is_array($json['recipeCategory'])) {
				$json['recipeCategory'] = reset($json['recipeCategory']);
			} elseif (!is_string($json['recipeCategory'])) {
				$json['recipeCategory'] = '';
			}
		} else {
			$json['recipeCategory'] = '';
		}

		$json['recipeCategory'] = $this->cleanUpString($json['recipeCategory'], false, true);

		
		// Make sure that "recipeYield" is an integer which is at least 1
		if (isset($json['recipeYield']) && $json['recipeYield']) {
			$regex_matches = [];
			preg_match('/(\d*)/', $json['recipeYield'], $regex_matches);
			if (count($regex_matches) >= 1) {
				$yield = filter_var($regex_matches[0], FILTER_SANITIZE_NUMBER_INT);
			}

			if ($yield && $yield > 0) {
				$json['recipeYield'] = (int) $yield;
			} else {
				$json['recipeYield'] = 1;
			}
		} else {
			$json['recipeYield'] = 1;
		}

		// Make sure that "keywords" is an array of unique strings
		if (isset($json['keywords']) && is_string($json['keywords'])) {
			$keywords = trim($json['keywords'], " \0\t\n\x0B\r,");
			$keywords = strip_tags($keywords);
			$keywords = preg_replace('/\s+/', ' ', $keywords); // Collapse whitespace
			$keywords = preg_replace('/(, | ,|,)+/', ',', $keywords); // Clean up separators
			$keywords = explode(',', $keywords);
			$keywords = array_unique($keywords);

			foreach ($keywords as $i => $keyword) {
				$keywords[$i] = $this->cleanUpString($keywords[$i]);
			}

			$keywords = implode(',', $keywords);
			$json['keywords'] = $keywords;
		} else {
			$json['keywords'] = '';
		}

		// Make sure that "tool" is an array of strings
		if (isset($json['tool']) && is_array($json['tool'])) {
			$tools = [];

			foreach ($json['tool'] as $i => $tool) {
				$tool = $this->cleanUpString($tool);

				if (!$tool) {
					continue;
				}

				array_push($tools, $tool);
			}
			$json['tool'] = $tools;
		} else {
			$json['tool'] = [];
		}

		$json['tool'] = array_filter($json['tool']);

		// Make sure that "recipeIngredient" is an array of strings
		if (isset($json['recipeIngredient']) && is_array($json['recipeIngredient'])) {
			$ingredients = [];

			foreach ($json['recipeIngredient'] as $i => $ingredient) {
				$ingredient = $this->cleanUpString($ingredient, false);

				if (!$ingredient) {
					continue;
				}

				array_push($ingredients, $ingredient);
			}

			$json['recipeIngredient'] = $ingredients;
		} else {
			$json['recipeIngredient'] = [];
		}

		$json['recipeIngredient'] = array_filter(array_values($json['recipeIngredient']));

		// Make sure that "recipeInstructions" is an array of strings
		if (isset($json['recipeInstructions'])) {
			if (is_array($json['recipeInstructions'])) {
				// Workaround for https://www.colruyt.be/fr/en-cuisine/meli-melo-de-legumes-oublies-au-chevre
				if (isset($json['recipeInstructions']['itemListElement'])) {
					$json['recipeInstructions'] = $json['recipeInstructions']['itemListElement'];
				}

				foreach ($json['recipeInstructions'] as $i => $step) {
					if (is_string($step)) {
						$json['recipeInstructions'][$i] = $this->cleanUpString($step, true);
					} elseif (is_array($step) && isset($step['text'])) {
						$json['recipeInstructions'][$i] = $this->cleanUpString($step['text'], true);
					} else {
						$json['recipeInstructions'][$i] = '';
					}
				}
			} elseif (is_string($json['recipeInstructions'])) {
				$json['recipeInstructions'] = html_entity_decode($json['recipeInstructions']);

				$regex_matches = [];
				preg_match_all('/<(p|li)>(.*?)<\/(p|li)>/', $json['recipeInstructions'], $regex_matches, PREG_SET_ORDER);

				$instructions = [];

				foreach ($regex_matches as $regex_match) {
					if (!$regex_match || !isset($regex_match[2])) {
						continue;
					}

					$step = $this->cleanUpString($regex_match[2]);

					if (!$step) {
						continue;
					}

					array_push($instructions, $step);
				}

				if (sizeof($instructions) > 0) {
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

		$json['recipeInstructions'] = array_filter(array_values($json['recipeInstructions']), function ($v) {
			return !empty($v) && $v !== "\n" && $v !== "\r";
		});

		// Make sure the 'description' is a string
		if (isset($json['description']) && is_string($json['description'])) {
			$json['description'] = $this->cleanUpString($json['description'], true);
		} else {
			$json['description'] = "";
		}

		// Make sure the 'url' is a URL, or blank
		if (isset($json['url']) && $json['url']) {
			$url = filter_var($json['url'], FILTER_SANITIZE_URL);
			if (filter_var($url, FILTER_VALIDATE_URL) === false) {
				$url = "";
			}
			$json['url'] = $url;
		} else {
			$json['url'] = "";
		}

		// Parse duration fields
		$durations = ['prepTime', 'cookTime', 'totalTime'];
		$duration_patterns = [
			'/P.*T(\d+H)?(\d+M)?/',   // ISO 8601
			'/(\d+):(\d+)/',        // Clock
		];

		foreach ($durations as $duration) {
			if (!isset($json[$duration]) || empty($json[$duration])) {
				continue;
			}

			$duration_hours = 0;
			$duration_minutes = 0;
			$duration_value = $json[$duration];

			if (is_array($duration_value) && sizeof($duration_value) === 2) {
				$duration_hours = $duration_value[0] ? $duration_value[0] : 0;
				$duration_minutes = $duration_value[1] ? $duration_value[1] : 0;
			} else {
				foreach ($duration_patterns as $duration_pattern) {
					$duration_matches = [];
					preg_match_all($duration_pattern, $duration_value, $duration_matches);

					if (isset($duration_matches[1][0]) && !empty($duration_matches[1][0])) {
						$duration_hours = intval($duration_matches[1][0]);
					}
					
					if (isset($duration_matches[2][0]) && !empty($duration_matches[2][0])) {
						$duration_minutes = intval($duration_matches[2][0]);
					}
				}
			}

			while ($duration_minutes >= 60) {
				$duration_minutes -= 60;
				$duration_hours++;
			}

			$json[$duration] = 'PT' . $duration_hours . 'H' . $duration_minutes . 'M';
		}

		// Nutrition information
		if (isset($json['nutrition']) && is_array($json['nutrition'])) {
			$json['nutrition'] = array_filter($json['nutrition']);
		} else {
			$json['nutrition'] = [];
		}
		
		return $json;
	}

	/**
	 * @param string $html
	 *
	 * @return array
	 */
	private function parseRecipeHtml($url, $html) {
		if (!$html) {
			return null;
		}

		// Make sure we don't have any encoded entities in the HTML string
		$html = html_entity_decode($html);

		// Start document parser
		$document = new \DOMDocument();

		$libxml_previous_state = libxml_use_internal_errors(true);

		try {
			if (!$document->loadHTML($html)) {
				throw new \Exception('Malformed HTML');
			}
			$errors = libxml_get_errors();
			$this->display_libxml_errors($url, $errors);
			libxml_clear_errors();
		} finally {
			libxml_use_internal_errors($libxml_previous_state);
		}
		
		$xpath = new \DOMXPath($document);

		$json_ld_elements = $xpath->query("//*[@type='application/ld+json']");

		foreach ($json_ld_elements as $json_ld_element) {
			if (!$json_ld_element || !$json_ld_element->nodeValue) {
				continue;
			}

			$string = $json_ld_element->nodeValue;

			// Some recipes have newlines inside quotes, which is invalid JSON. Fix this before continuing.
			$string = preg_replace('/\s+/', ' ', $string);

			$json = json_decode($string, true);

			// Look through @graph field for recipe
			if ($json && isset($json['@graph']) && is_array($json['@graph'])) {
				foreach ($json['@graph'] as $graph_item) {
					if (!isset($graph_item['@type']) || $graph_item['@type'] !== 'Recipe') {
						continue;
					}

					$json = $graph_item;
					break;
				}
			}

			// Check if json is an array for some reason
			if ($json && isset($json[0])) {
				foreach ($json as $element) {
					if (!$element || !isset($element['@type']) || $element['@type'] !== 'Recipe') {
						continue;
					}
					return $this->checkRecipe($element);
				}
			}

			if (!$json || !isset($json['@type']) || $json['@type'] !== 'Recipe') {
				continue;
			}

			return $this->checkRecipe($json);
		}

		// Parse HTML if JSON couldn't be found
		$json = [];
		
		$recipes = $xpath->query("//*[@itemtype='http://schema.org/Recipe']");

		if (!isset($recipes[0])) {
			throw new \Exception('Could not find recipe element');
		}

		$props = [
			'name',
			'image', 'images', 'thumbnail',
			'recipeYield',
			'keywords',
			'recipeIngredient', 'ingredients',
			'recipeInstructions', 'instructions', 'steps', 'guide',
		];

		foreach ($props as $prop) {
			$prop_elements = $xpath->query("//*[@itemprop='" . $prop . "']");

			foreach ($prop_elements as $prop_element) {
				switch ($prop) {
					case 'image':
					case 'images':
					case 'thumbnail':
						$prop = 'image';
						
						if (!isset($json[$prop]) || !is_array($json[$prop])) {
							$json[$prop] = [];
						}

						if (!empty($prop_element->getAttribute('src'))) {
							array_push($json[$prop], $prop_element->getAttribute('src'));
						} elseif (
							null !== $prop_element->getAttributeNode('content') &&
							!empty($prop_element->getAttributeNode('content')->value)
						) {
							array_push($json[$prop], $prop_element->getAttributeNode('content')->value);
						}

						break;

					case 'recipeIngredient':
					case 'ingredients':
						$prop = 'recipeIngredient';
						
						if (!isset($json[$prop]) || !is_array($json[$prop])) {
							$json[$prop] = [];
						}

						if (
							null !== $prop_element->getAttributeNode('content') &&
							!empty($prop_element->getAttributeNode('content')->value)
						) {
							array_push($json[$prop], $prop_element->getAttributeNode('content')->value);
						} else {
							array_push($json[$prop], $prop_element->nodeValue);
						}
						
						break;

					case 'recipeInstructions':
					case 'instructions':
					case 'steps':
					case 'guide':
						$prop = 'recipeInstructions';
						
						if (!isset($json[$prop]) || !is_array($json[$prop])) {
							$json[$prop] = [];
						}

						if (
							null !== $prop_element->getAttributeNode('content') &&
							!empty($prop_element->getAttributeNode('content')->value)
						) {
							array_push($json[$prop], $prop_element->getAttributeNode('content')->value);
						} else {
							array_push($json[$prop], $prop_element->nodeValue);
						}
						break;

					default:
						if (isset($json[$prop]) && $json[$prop]) {
							break;
						}

						if (
							null !== $prop_element->getAttributeNode('content') &&
							!empty($prop_element->getAttributeNode('content')->value)
						) {
							$json[$prop] = $prop_element->getAttributeNode('content')->value;
						} else {
							$json[$prop] = $prop_element->nodeValue;
						}
						break;
				}
			}
		}

		// Make one final desparate attempt at getting the instructions
		if (!isset($json['recipeInstructions']) || !$json['recipeInstructions'] || sizeof($json['recipeInstructions']) < 1) {
			$json['recipeInstructions'] = [];
			
			$step_elements = $recipes[0]->getElementsByTagName('p');

			foreach ($step_elements as $step_element) {
				if (!$step_element || !$step_element->nodeValue) {
					continue;
				}

				array_push($json['recipeInstructions'], $step_element->nodeValue);
			}
		}
		
		return $this->checkRecipe($json);
	}

	private function display_libxml_errors($url, $errors) {
		$error_counter = [];
		$by_error_code = [];
		
		foreach ($errors as $error) {
			$count = array_key_exists($error->code, $error_counter) ? $error_counter[$error->code] : 0;
			$error_counter[$error->code] = $count + 1;
			$by_error_code[$error->code] = $error;
		}
		
		foreach ($error_counter as $code => $count) {
			$error = $by_error_code[$code];
			
			switch ($error->level) {
				case LIBXML_ERR_WARNING:
					$error_message = "libxml: Warning $error->code ";
					break;
				case LIBXML_ERR_ERROR:
					$error_message = "libxml: Error $error->code ";
					break;
				case LIBXML_ERR_FATAL:
					$error_message = "libxml: Fatal Error $error->code ";
					break;
				default:
					$error_message = "Unknown Error ";
			}

			$error_message .= "occurred " . $count . " times while parsing " . $url . ". Last time in line $error->line" .
				" and column $error->column: " . $error->message;
			
			$this->logger->warning($error_message);
		}
	}

	/**
	 * @param int $id
	 */
	public function deleteRecipe(int $id) {
		$user_folder = $this->getFolderForUser();
		$recipe_folder = $user_folder->getById($id);

		if ($recipe_folder && count($recipe_folder) > 0) {
			$recipe_folder[0]->delete();
		}

		$this->db->deleteRecipeById($id);
	}

	/**
	 * @param array $json
	 *
	 * @return File
	 */
	public function addRecipe($json) {
		if (!$json || !isset($json['name']) || !$json['name']) {
			// XXX More specific Exception better?
			throw new Exception('Recipe name not found');
		}

		$now = date(DATE_ISO8601);

		// Sanity check
		$json = $this->checkRecipe($json);

		// Update modification date
		$json['dateModified'] = $now;

		// Create/move recipe folder
		$user_folder = $this->getFolderForUser();
		$recipe_folder = null;

		// Recipe already has an id, update it
		if (isset($json['id']) && $json['id']) {
			$recipe_folder = $user_folder->getById($json['id'])[0];

			$old_path = $recipe_folder->getPath();
			$new_path = dirname($old_path) . '/' . $json['name'];

			// The recipe is being renamed, move the folder
			if ($old_path !== $new_path) {
				if ($user_folder->nodeExists($json['name'])) {
					throw new RecipeExistsException($this->il10n->t('Another recipe with that name already exists'));
				}
				
				$recipe_folder->move($new_path);
			}

			// This is a new recipe, create it
		} else {
			$json['dateCreated'] = $now;

			if ($user_folder->nodeExists($json['name'])) {
				throw new RecipeExistsException($this->il10n->t('Another recipe with that name already exists'));
			}

			$recipe_folder = $user_folder->newFolder($json['name']);
		}

		// Write JSON file to disk
		$recipe_file = $this->getRecipeFileByFolderId($recipe_folder->getId());

		if (!$recipe_file) {
			$recipe_file = $recipe_folder->newFile('recipe.json');
		}

		// Rename .json file if it's not "recipe.json"
		if ($recipe_file->getName() !== 'recipe.json') {
			$recipe_file->move(str_replace($recipe_file->getName(), 'recipe.json', $recipe_file->getPath()));
		}

		$recipe_file->putContent(json_encode($json));

		// Download image and generate thumbnail
		$full_image_data = null;

		if (isset($json['image']) && $json['image']) {
			// The image is a URL
			if (strpos($json['image'], 'http') === 0) {
				$json['image'] = str_replace(' ', '%20', $json['image']);
				$full_image_data = file_get_contents($json['image']);

			// The image is a local path
			} else {
				try {
					$full_image_file = $this->root->get('/' . $this->user_id . '/files' . $json['image']);
					$full_image_data = $full_image_file->getContent();
				} catch (NotFoundException $e) {
					$full_image_data = null;
				}
			}

			// The image field was empty, remove images in the recipe folder
		} else {
			if ($recipe_folder->nodeExists('full.jpg')) {
				$recipe_folder->get('full.jpg')->delete();
			}

			if ($recipe_folder->nodeExists('thumb.jpg')) {
				$recipe_folder->get('thumb.jpg')->delete();
			}

			if ($recipe_folder->nodeExists('thumb16.jpg')) {
				$recipe_folder->get('thumb16.jpg')->delete();
			}
		}

		// If image data was fetched, write it to disk
		if ($full_image_data) {
			// Write the full image
			try {
				$full_image_file = $recipe_folder->get('full.jpg');
			} catch (NotFoundException $e) {
				$full_image_file = $recipe_folder->newFile('full.jpg');
			}

			$full_image_file->putContent($full_image_data);

			// Write the thumbnail
			$thumb_image = new Image();
			$thumb_image->loadFromData($full_image_data);
			$thumb_image->fixOrientation();
			$thumb_image->resize(256);
			$thumb_image->centerCrop();

			try {
				$thumb_image_file = $recipe_folder->get('thumb.jpg');
			} catch (NotFoundException $e) {
				$thumb_image_file = $recipe_folder->newFile('thumb.jpg');
			}

			$thumb_image_file->putContent($thumb_image->data());

			// Create low-resolution thumbnail preview
			$low_res_thumb_image = $thumb_image->resizeCopy(16);
			try {
				$low_res_thumb_image_file = $recipe_folder->get('thumb16.jpg');
			} catch (NotFoundException $e) {
				$low_res_thumb_image_file = $recipe_folder->newFile('thumb16.jpg');
			}
			$low_res_thumb_image_file->putContent($low_res_thumb_image->data());
		}

		// Write .nomedia file to avoid gallery indexing
		if (!$recipe_folder->nodeExists('.nomedia')) {
			$recipe_folder->newFile('.nomedia');
		}

		// Make sure the directory has been marked as changed
		$recipe_folder->touch();

		return $recipe_file;
	}

	/**
	 * @param string $url
	 *
	 * @return File
	 */
	public function downloadRecipe($url) {
		$host = parse_url($url);

		if (!$host) {
			throw new Exception('Could not parse URL');
		}

		$opts = [
			"http" => [
				"method" => "GET",
				"header" => "User-Agent: Nextcloud Cookbook App"
			]
		];

		$context = stream_context_create($opts);

		$html = file_get_contents($url, false, $context);

		if (!$html) {
			throw new Exception('Could not fetch site ' . $url);
		}

		$json = $this->parseRecipeHtml($url, $html);

		if (!$json) {
			throw new Exception('No recipe data found');
		}

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

		foreach ($recipe_folders as $recipe_folder) {
			$recipe_file = $this->getRecipeFileByFolderId($recipe_folder->getId());

			if (!$recipe_file) {
				continue;
			}

			$recipe_files[] = $recipe_file;
		}

		return $recipe_files;
	}

	/**
	 * Updates the search index (no more) and migrate file structure
	 * @deprecated
	 */
	public function updateSearchIndex() {
		try {
			$this->migrateFolderStructure();
		} catch (UserFolderNotWritableException $ex) {
			// Ignore migration if not permitted.
			$this->logger->warning("Cannot migrate cookbook file structure as not permitted.");
			throw $ex;
		}
	}
	
	private function migrateFolderStructure() {
		// Remove old cache folder if needed
		$legacy_cache_path = '/cookbook/cache';
		
		if ($this->root->nodeExists($legacy_cache_path)) {
			$this->root->get($legacy_cache_path)->delete();
		}
		
		// Restructure files if needed
		$user_folder = $this->getFolderForUser();
		
		foreach ($user_folder->getDirectoryListing() as $node) {
			// Move JSON files from the user directory into its own folder
			if ($this->isRecipeFile($node)) {
				$recipe_name = str_replace('.json', '', $node->getName());
				
				$node->move($node->getPath() . '_tmp');
				
				$recipe_folder = $user_folder->newFolder($recipe_name);
				
				$node->move($recipe_folder->getPath() . '/recipe.json');
				
			// Rename folders with .json extensions (this was likely caused by a migration bug)
			} elseif ($node instanceof Folder && strpos($node->getName(), '.json')) {
				$node->move(str_replace('.json', '', $node->getPath()));
			}
		}
	}

	/**
	 * Gets all keywords from the index
	 *
	 * @return array
	 */
	public function getAllKeywordsInSearchIndex() {
		return $this->db->findAllKeywords($this->user_id);
	}
	
	/**
	 * Gets all categories from the index
	 *
	 * @return array
	 */
	public function getAllCategoriesInSearchIndex() {
		return $this->db->findAllCategories($this->user_id);
	}



	/** Adds modification and creation date to each recipe in the list
	 *
	 * @param array $recipes
	 */
	private function addDatesToRecipes(array &$recipes) {
		foreach ($recipes as $i => $recipe) {
			// TODO Add data to database instead of reading from files
			$r = $this->getRecipeById($recipe['recipe_id']);
			$recipes[$i]['dateCreated'] = $r['dateCreated'];
			$recipes[$i]['dateModified'] = $r['dateModified'];
		}
	}

	/**
	 * Gets all recipes from the index
	 *
	 * @return array
	 */
	public function getAllRecipesInSearchIndex(): array {
		$recipes = $this->db->findAllRecipes($this->user_id);
		$this->addDatesToRecipes($recipes);
		return $recipes;
	}

	/**
	 * Get all recipes of a certain category
	 *
	 * @param string $category
	 *
	 * @return array
	 */
	public function getRecipesByCategory($category): array {
		$recipes = $this->db->getRecipesByCategory($category, $this->user_id);
		$this->addDatesToRecipes($recipes);
		return $recipes;
	}

	/**
	 * Get all recipes containing all of the keywords.
	 *
	 * @param string $keywords Keywords/tags as a comma-separated string.
	 *
	 * @return array
	 * @throws \OCP\AppFramework\Db\DoesNotExistException
	 */
	public function getRecipesByKeywords($keywords): array {
		$recipes = $this->db->getRecipesByKeywords($keywords, $this->user_id);
		$this->addDatesToRecipes($recipes);
		return $recipes;
	}

	/**
	 * Search for recipes by keywords
	 *
	 * @param $keywords_string
	 * @return array
	 * @throws \OCP\AppFramework\Db\DoesNotExistException
	 */
	public function findRecipesInSearchIndex($keywords_string): array {
		$keywords_string = strtolower($keywords_string);
		$keywords_array = [];
		preg_match_all('/[^ ,]+/', $keywords_string, $keywords_array);

		if (sizeof($keywords_array) > 0) {
			$keywords_array = $keywords_array[0];
		}

		$recipes = $this->db->findRecipes($keywords_array, $this->user_id);
		$this->addDatesToRecipes($recipes);
		return $recipes;
	}

	/**
	 * @param string $path
	 */
	public function setUserFolderPath(string $path) {
		$this->config->setUserValue($this->user_id, 'cookbook', 'folder', $path);
	}

	/**
	 * @return string
	 */
	public function getUserFolderPath() {
		$path = $this->config->getUserValue($this->user_id, 'cookbook', 'folder');

		if (!$path) {
			$path = '/' . $this->il10n->t('Recipes');
			$this->config->setUserValue($this->user_id, 'cookbook', 'folder', $path);
		}

		return $path;
	}

	/**
	 * @param int $interval
	 * @throws PreConditionNotMetException
	 */
	public function setSearchIndexUpdateInterval(int $interval) {
		$this->config->setUserValue($this->user_id, 'cookbook', 'update_interval', $interval);
	}

	/**
	 * @return Folder
	 */
	public function getFolderForUser() {
		$path = '/' . $this->user_id . '/files/' . $this->getUserFolderPath();
		$path = str_replace('//', '/', $path);

		return $this->getOrCreateFolder($path);
	}

	/**
	 * @param bool $printImage
	 * @throws PreConditionNotMetException
	 */
	public function setPrintImage(bool $printImage) {
		$this->config->setUserValue($this->user_id, 'cookbook', 'print_image', (int) $printImage);
	}

	/**
	 * Should image be printed with the recipe
	 * @return bool
	 */
	public function getPrintImage() {
		return (bool) $this->config->getUserValue($this->user_id, 'cookbook', 'print_image');
	}

	/**
	 * Finds a folder and creates it if non-existent
	 * @param string $path path to the folder
	 *
	 * @return Folder
	 *
	 * @throws NotFoundException
	 * @throws NotPermittedException
	 */
	private function getOrCreateFolder($path) {
		if ($this->root->nodeExists($path)) {
			$folder = $this->root->get($path);
		} else {
			try {
				$folder = $this->root->newFolder($path);
			} catch (NotPermittedException $ex) {
				throw new UserFolderNotWritableException($this->il10n->t('User cannot create recipe folder'), null, $ex);
			}
		}
		return $folder;
	}

	/**
	 * Get recipe file contents as an array
	 *
	 * @param File $file
	 *
	 * @return array
	 */
	public function parseRecipeFile($file) {
		if (!$file) {
			return null;
		}

		$json = json_decode($file->getContent(), true);

		if (!$json) {
			return null;
		}

		$json['id'] = $file->getParent()->getId();


		if (!array_key_exists('dateCreated', $json) && method_exists($file, 'getCreationTime')) {
			$json['dateCreated'] = $file->getCreationTime();
		}
		if (!array_key_exists('dateModified', $json)) {
			$json['dateModified'] = $file->getMTime();
		}

		return $this->checkRecipe($json);
	}

	/**
	 * Gets the image file for a recipe
	 *
	 * @param int $id
	 * @param string $size
	 *
	 * @return File
	 */
	public function getRecipeImageFileByFolderId($id, $size = 'thumb') {
		if (!$size) {
			$size = 'thumb';
		}
		if ($size !== 'full' && $size !== 'thumb' && $size !== 'thumb16') {
			throw new Exception('Image size "' . $size . '" not recognised');
		}

		$recipe_folder = $this->root->getById($id);

		if (count($recipe_folder) < 1) {
			throw new Exception('Recipe ' . $id . ' not found');
		}

		$recipe_folder = $recipe_folder[0];

		$image_file = null;
		$image_filename = $size . '.jpg';

		if (($size === 'thumb16' || $size === 'thumb') && !$recipe_folder->nodeExists($image_filename)) {
			if ($recipe_folder->nodeExists('full.jpg')) {
				// Write the thumbnail
				$recipe_full_image_file = $recipe_folder->get('full.jpg');
				$full_image_data = $recipe_full_image_file->getContent();
				$thumb_image = new Image();
				$thumb_image->loadFromData($full_image_data);
				$thumb_image->fixOrientation();
				$thumb_image->resize(256);
				$thumb_image->centerCrop();

				try {
					$thumb_image_file = $recipe_folder->get('thumb.jpg');
				} catch (NotFoundException $e) {
					$thumb_image_file = $recipe_folder->newFile('thumb.jpg');
				}

				$thumb_image_file->putContent($thumb_image->data());

				// Create low-resolution thumbnail preview
				$low_res_thumb_image = $thumb_image->resizeCopy(16);
				try {
					$low_res_thumb_image_file = $recipe_folder->get('thumb16.jpg');
				} catch (NotFoundException $e) {
					$low_res_thumb_image_file = $recipe_folder->newFile('thumb16.jpg');
				}
				$low_res_thumb_image_file->putContent($low_res_thumb_image->data());
			}
		}

		$image_file = $recipe_folder->get($image_filename);

		if ($image_file && $this->isImage($image_file)) {
			return $image_file;
		}

		throw new Exception('Image file not recognised');
	}

	/**
	 * Test if file is an image
	 *
	 * @param File $file
	 *
	 * @return bool
	 */
	private function isImage($file) {
		$allowedExtensions = ['jpg', 'jpeg', 'png'];
		if ($file->getType() !== 'file') {
			return false;
		}
		$ext = pathinfo($file->getName(), PATHINFO_EXTENSION);
		$iext = strtolower($ext);
		if (!in_array($iext, $allowedExtensions)) {
			return false;
		}
		return true;
	}

	/**
	 * Test if file is a recipe
	 *
	 * @param File $file
	 *
	 * @return bool
	 */
	private function isRecipeFile($file) {
		$allowedExtensions = ['json'];

		if ($file->getType() !== 'file') {
			return false;
		}

		$ext = pathinfo($file->getName(), PATHINFO_EXTENSION);
		$iext = strtolower($ext);

		if (!in_array($iext, $allowedExtensions)) {
			return false;
		}

		return true;
	}

	/**
	 * @param string $str
	 *
	 * @return string
	 */
	private function cleanUpString($str, $preserve_newlines = false, $remove_slashes = false) {
		if (!$str) {
			return '';
		}

		$str = strip_tags($str);

		if (!$preserve_newlines) {
			$str = str_replace(["\r", "\n"], '', $str);
		}

		// We want to remove forward-slashes for the name of the recipe, to tie it to the directory structure, which cannot have slashes
		if ($remove_slashes) {
			$str = str_replace(["\t", "\\", "/"], '', $str);
		} else {
			$str = str_replace(["\t", "\\"], '', $str);
		}
		
		$str = html_entity_decode($str);

		return $str;
	}
}
