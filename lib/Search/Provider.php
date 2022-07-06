<?php

namespace OCA\Cookbook\Search;

use OCA\Cookbook\AppInfo\Application;
use OCA\Cookbook\Service\RecipeService;
use OCP\IL10N;
use OCP\IUser;
use OCP\IURLGenerator;
use OCP\Search\IProvider;
use OCP\Search\ISearchQuery;
use OCP\Search\SearchResult;
use OCP\Search\SearchResultEntry;
use OCP\Util;

// IProvider requies NC >= 20
// Remove conditional once we end support for NC 19
if (Util::getVersion()[0] >= 20) {
	class Provider implements IProvider {
		/** @var IL10N */
		private $l;

		/** @var IURLGenerator */
		private $urlGenerator;

		/** @var RecipeService */
		private $recipeService;

		public function __construct(
			IL10n $il10n,
			IURLGenerator $urlGenerator,
			RecipeService $recipeService
			) {
			$this->l = $il10n;
			$this->urlGenerator = $urlGenerator;
			$this->recipeService = $recipeService;
		}

		public function getId(): string {
			return Application::APP_ID;
		}

		public function getName(): string {
			return $this->l->t('Recipes');
		}

		public function getOrder(string $route, array $routeParameters): int {
			if (strpos($route, 'files' . '.') === 0) {
				return 25;
			} elseif (strpos($route, Application::APP_ID . '.') === 0) {
				return -1;
			}
			return 4;
		}

		public function search(IUser $user, ISearchQuery $query): SearchResult {
			$recipes = $this->recipeService->findRecipesInSearchIndex($query->getTerm());
			$result = array_map(
				function (array $recipe) use ($user): SearchResultEntry {
					$id = $recipe['recipe_id'];

					$subline = '';
					$category = $recipe['category'];
					if ($category !== null) {
						// TRANSLATORS Will be shown in search results, listing the recipe category, e.g., 'in Salads'
						$subline = $this->l->t('in %s', [$category]);
					}

					return new SearchResultEntry(
						// Thumb image
						$this->urlGenerator->linkToRoute('cookbook.recipe.image', ['id' => $id, 'size' => 'thumb']),
						// Name as title
						$recipe['name'],
						// Category as subline
						$subline,
						// Link to Vue route of recipe
						$this->urlGenerator->linkToRouteAbsolute('cookbook.main.index') . '#/recipe/' . $id
					);
				}, $recipes
			);

			return SearchResult::complete(
				$this->getName(),
				$result
			);
		}
	}
} else {
	class Provider {
	}
}
