<?php

namespace OCA\Cookbook\tests\Integration\Db\RecipeDb;

use OCA\Cookbook\Db\RecipeDb;
use OCA\Cookbook\tests\Integration\AbstractDatabaseTestCase;
use OCP\AppFramework\Db\DoesNotExistException;

/**
 * @covers OCA\Cookbook\Db\RecipeDb
 */
class RecipeDbBigTest extends AbstractDatabaseTestCase {
	/** @var RecipeDb */
	private $dut;

	private $user;
	private $recipes;

	protected function setUp(): void {
		parent::setUp();

		/** @var RecipeDb */
		$this->dut = $this->query(RecipeDb::class);

		$this->recipes = [
			[
				'name' => 'Cooked Bananas',
				'id' => 1234,
				'dateCreated' => '2022-05-03 14:30:12',
				'dateModified' => null,
			],
			[
				'name' => 'Salad',
				'id' => 5678,
				'dateCreated' => '2021-06-15 20:10:00',
				'dateModified' => '2021-07-15 14:12:00',
			],
			[
				'name' => 'Pulled Beef',
				'id' => 2345,
				'dateCreated' => '2021-06-15 20:10:00',
				'dateModified' => '2021-07-15 14:12:00',
			],
			[
				'name' => 'Chicken',
				'id' => 3456,
				'dateCreated' => '2021-06-15 20:10:00',
				'dateModified' => '2021-07-15 14:12:00',
			],
			[
				'name' => 'Cake',
				'id' => 6789,
				'dateCreated' => '2021-06-15 20:10:00',
				'dateModified' => '2021-07-15 14:12:00',
			],
			[
				'name' => 'Water',
				'id' => 2,
				'dateCreated' => '2021-06-15 20:10:00',
				'dateModified' => '2021-07-15 14:12:00',
			],
		];
		$this->user = 'test-user';

		$this->dut->insertRecipes($this->recipes, $this->user);

		$this->dut->addCategoryOfRecipe(1234, 'desert', $this->user);
		$this->dut->addCategoryOfRecipe(5678, 'Opener', $this->user);
		$this->dut->addCategoryOfRecipe(2345, 'Main dishes', $this->user);
		$this->dut->addCategoryOfRecipe(3456, 'Main dishes', $this->user);
		$this->dut->addCategoryOfRecipe(6789, 'desert', $this->user);

		$this->dut->addKeywordPairs([
			['recipeId' => 1234, 'name' => 'Sweet'],
			['recipeId' => 1234, 'name' => 'Foreign'],
			['recipeId' => 1234, 'name' => 'Favorites'],
			['recipeId' => 1234, 'name' => 'Fast'],

			['recipeId' => 5678, 'name' => 'Vegetarian'],
			['recipeId' => 5678, 'name' => 'Fast'],

			['recipeId' => 2345, 'name' => 'Beef'],
			['recipeId' => 2345, 'name' => 'Meat'],

			['recipeId' => 3456, 'name' => 'Chicken'],
			['recipeId' => 3456, 'name' => 'Meat'],
			['recipeId' => 3456, 'name' => 'Favorites'],

			// Water has no keyword
		], $this->user);
	}

	public function testGetKeywords() {
		$keywords = $this->dut->findAllKeywords($this->user);
		$this->assertEqualsCanonicalizing([
			['name' => 'Sweet', 'recipeCount' => 1],
			['name' => 'Foreign', 'recipeCount' => 1],
			['name' => 'Favorites', 'recipeCount' => 2],
			['name' => 'Fast', 'recipeCount' => 2],
			['name' => 'Vegetarian', 'recipeCount' => 1],
			['name' => 'Meat', 'recipeCount' => 2],
			['name' => 'Beef', 'recipeCount' => 1],
			['name' => 'Chicken', 'recipeCount' => 1],
		], $keywords);
	}

	public function testGetCategories() {
		$keywords = $this->dut->findAllCategories($this->user);
		$this->assertEqualsCanonicalizing([
			['name' => 'desert', 'recipeCount' => 2],
			['name' => 'Opener', 'recipeCount' => 1],
			['name' => 'Main dishes', 'recipeCount' => 2],
			['name' => '*', 'recipeCount' => 1],
		], $keywords);
	}

	public function testGetRecipesByKeyword() {
		$recipes = $this->dut->getRecipesByKeywords('Favorites', $this->user);
		$expected = [
			[
				'name' => 'Chicken',
				'recipe_id' => 3456,
				'dateCreated' => '2021-06-15 20:10:00',
				'dateModified' => '2021-07-15 14:12:00',
				'keywords' => 'Chicken,Favorites,Meat',
				'category' => 'Main dishes',
			],
			[
				'name' => 'Cooked Bananas',
				'recipe_id' => 1234,
				'dateCreated' => '2022-05-03 14:30:12',
				'dateModified' => null,
				'keywords' => 'Fast,Favorites,Foreign,Sweet',
				'category' => 'desert',
			],
		];
		$this->assertEquals($expected, $recipes);
	}

	public function testGetAllRecipesOfCategory() {
		$recipes = $this->dut->getRecipesByCategory('desert', $this->user);
		$expected = [
			[
				'name' => 'Cake',
				'recipe_id' => 6789,
				'dateCreated' => '2021-06-15 20:10:00',
				'dateModified' => '2021-07-15 14:12:00',
				'category' => 'desert',
				'keywords' => null,
			],
			[
				'name' => 'Cooked Bananas',
				'recipe_id' => 1234,
				'dateCreated' => '2022-05-03 14:30:12',
				'dateModified' => null,
				'category' => 'desert',
				'keywords' => 'Fast,Favorites,Foreign,Sweet',
			],
		];
		$this->assertEquals($expected, $recipes);
	}

	public function testGetAllRecipesOfEmptyCategory() {
		$recipes = $this->dut->getRecipesByCategory('_', $this->user);
		$expected = [
			[
				'name' => 'Water',
				'recipe_id' => 2,
				'dateCreated' => '2021-06-15 20:10:00',
				'dateModified' => '2021-07-15 14:12:00',
				'keywords' => null,
				'category' => null,
			],
		];
		$this->assertEquals($expected, $recipes);
	}

	public function testGetNonExistingRecipe() {
		$this->expectException(DoesNotExistException::class);
		$this->dut->findRecipeById(10);
	}

	public function testFindRecipes() {
		$recipes = $this->dut->findRecipes(['Desert', 'meat'], $this->user);
		$expected = [
			[
				'name' => 'Cake',
				'recipe_id' => 6789,
				'dateCreated' => '2021-06-15 20:10:00',
				'dateModified' => '2021-07-15 14:12:00',
				'category' => 'desert',
				'keywords' => null,
			],
			[
				'name' => 'Chicken',
				'recipe_id' => 3456,
				'dateCreated' => '2021-06-15 20:10:00',
				'dateModified' => '2021-07-15 14:12:00',
				'category' => 'Main dishes',
				'keywords' => 'Chicken,Favorites,Meat',
			],
			[
				'name' => 'Cooked Bananas',
				'recipe_id' => 1234,
				'dateCreated' => '2022-05-03 14:30:12',
				'category' => 'desert',
				'keywords' => 'Fast,Favorites,Foreign,Sweet',
				'dateModified' => null,
			],
			[
				'name' => 'Pulled Beef',
				'recipe_id' => 2345,
				'dateCreated' => '2021-06-15 20:10:00',
				'dateModified' => '2021-07-15 14:12:00',
				'category' => 'Main dishes',
				'keywords' => 'Beef,Meat',
			],
		];
		$this->assertEquals($expected, $recipes);
	}

	public function testDeleteRecipes() {
		$this->dut->deleteRecipes([1234, 3456], $this->user);
		$expected = [
			[
				'name' => 'Cake',
				'recipe_id' => 6789,
				'dateCreated' => '2021-06-15 20:10:00',
				'dateModified' => '2021-07-15 14:12:00',
				'category' => 'desert',
				'keywords' => null,
			],
			[
				'name' => 'Pulled Beef',
				'recipe_id' => 2345,
				'dateCreated' => '2021-06-15 20:10:00',
				'dateModified' => '2021-07-15 14:12:00',
				'category' => 'Main dishes',
				'keywords' => 'Beef,Meat',
			],
			[
				'name' => 'Salad',
				'recipe_id' => 5678,
				'dateCreated' => '2021-06-15 20:10:00',
				'dateModified' => '2021-07-15 14:12:00',
				'category' => 'Opener',
				'keywords' => 'Fast,Vegetarian',
			],
			[
				'name' => 'Water',
				'recipe_id' => 2,
				'dateCreated' => '2021-06-15 20:10:00',
				'dateModified' => '2021-07-15 14:12:00',
				'category' => null,
				'keywords' => null,
			],
		];

		$this->assertEquals($expected, $this->dut->findAllRecipes($this->user));
	}

	public function testEmptySearchIndex() {
		$this->dut->emptySearchIndex($this->user);
		$this->assertEmpty($this->dut->findAllRecipes($this->user));
		$this->assertEmpty($this->dut->findAllKeywords($this->user));
		$expectedCat = [
			['name' => '*', 'recipe_count' => 0],
		];
		$this->assertEquals($expectedCat, $this->dut->findAllCategories($this->user));
	}
}
