<?php

namespace OCA\Cookbook\tests\Integration\Db\RecipeDb;

use OCA\Cookbook\Db\RecipeDb;
use OCA\Cookbook\Tests\Integration\AbstractDatabaseTestCase;

/**
 * @covers OCA\Cookbook\Db\RecipeDb
 */
class RecipeDbTest extends AbstractDatabaseTestCase {
	/** @var RecipeDb */
	private $dut;

	protected function setUp(): void {
		parent::setUp();

		$this->dut = $this->query(RecipeDb::class);
	}

	public function testBasicCRUD() {
		$recipeStub = [
			'name' => 'My recipe',
			'id' => 1234,
			'dateCreated' => '2022-02-01 15:10:00',
			'dateModified' => '2022-03-05 11:12:34',
		];
		$user = 'test-user';

		$this->assertEmpty($this->dut->findAllRecipes($user));

		$this->dut->insertRecipes([$recipeStub], $user);

		$allRecipes = $this->dut->findAllRecipes($user);

		$this->assertEmpty($this->dut->findAllRecipes('some-other-user'));
		$this->assertIsArray($allRecipes);

		$expected = [
			'name' => 'My recipe',
			'recipe_id' => 1234,
			'dateCreated' => '2022-02-01 15:10:00',
			'dateModified' => '2022-03-05 11:12:34',
			'keywords' => null,
			'category' => null,
		];
		$this->assertEquals($expected, $allRecipes[0]);

		$recipeStub = [
			'name' => 'My new recipe name',
			'id' => 1234,
			'dateCreated' => '2022-12-11 10:11:12',
			'dateModified' => '2022-09-25 16:17:18',
		];

		$this->dut->updateRecipes([$recipeStub], $user);
		$allRecipes = $this->dut->findAllRecipes($user);

		$expected = [
			'name' => 'My new recipe name',
			'recipe_id' => 1234,
			'dateCreated' => '2022-12-11 10:11:12',
			'dateModified' => '2022-09-25 16:17:18',
			'keywords' => null,
			'category' => null,
		];
		$this->assertEquals($expected, $allRecipes[0]);

		$expected = [
			'name' => 'My new recipe name',
			'id' => 1234,
			'dateCreated' => '2022-12-11 10:11:12',
			'dateModified' => '2022-09-25 16:17:18',
			// 'keywords' => null,
			// 'category' => null,
		];
		$recipe = $this->dut->findRecipeById(1234);
		$this->assertEquals($expected, $recipe);

		$this->dut->deleteRecipeById(1234);
		$this->assertEmpty($this->dut->findAllRecipes($user));
	}

	public function testEmptyDate() {
		$recipeStub = [
			'name' => 'My recipe',
			'id' => 1234,
			'dateCreated' => null,
			'dateModified' => null,
		];
		$user = 'test-user';

		$this->assertEmpty($this->dut->findAllRecipes($user));

		$this->dut->insertRecipes([$recipeStub], $user);

		$allRecipes = $this->dut->findAllRecipes($user);

		$expected = [
			'name' => 'My recipe',
			'recipe_id' => 1234,
			'dateCreated' => null,
			'dateModified' => null,
			'keywords' => null,
			'category' => null,
		];
		$this->assertEquals($expected, $allRecipes[0]);
	}

	public function testCategoryAddReplace() {
		$recipeStub = [
			'name' => 'My recipe',
			'id' => 1234,
			'dateCreated' => null,
			'dateModified' => null,
		];
		$user = 'test-user';

		$this->assertEmpty($this->dut->findAllRecipes($user));
		$this->dut->insertRecipes([$recipeStub], $user);

		$this->dut->addCategoryOfRecipe(1234, 'my category', $user);

		$recipe = $this->dut->findAllRecipes($user);
		$expected = [
			'name' => 'My recipe',
			'recipe_id' => 1234,
			'dateCreated' => null,
			'dateModified' => null,
			'keywords' => null,
			'category' => 'my category',
		];
		$this->assertEquals([$expected], $recipe);

		$this->dut->updateCategoryOfRecipe(1234, 'new category', $user);

		$recipe = $this->dut->findAllRecipes($user);
		$expected = [
			'name' => 'My recipe',
			'recipe_id' => 1234,
			'dateCreated' => null,
			'dateModified' => null,
			'keywords' => null,
			'category' => 'new category',
		];
		$this->assertEquals([$expected], $recipe);

		$this->assertEquals('new category', $this->dut->getCategoryOfRecipe(1234, $user));

		$this->dut->removeCategoryOfRecipe(1234, $user);
		$this->assertNull($this->dut->getCategoryOfRecipe(1234, $user));
	}

	public function testKeywordsAddReplace() {
		$recipeStub = [
			'name' => 'My recipe',
			'id' => 1234,
			'dateCreated' => null,
			'dateModified' => null,
		];
		$user = 'test-user';

		$this->assertEmpty($this->dut->findAllRecipes($user));
		$this->dut->insertRecipes([$recipeStub], $user);

		$pairs = [
			['recipeId' => 1234, 'name' => 'A'],
			['recipeId' => 1234, 'name' => 'B'],
			['recipeId' => 1234, 'name' => 'c'],
		];
		$this->dut->addKeywordPairs($pairs, $user);

		$recipes = $this->dut->findAllRecipes($user);
		$expected = [
			'name' => 'My recipe',
			'recipe_id' => 1234,
			'dateCreated' => null,
			'dateModified' => null,
			'keywords' => 'A,B,c',
			'category' => null,
		];
		$this->assertEquals([$expected], $recipes);

		$this->assertEquals(['A', 'B', 'c'], $this->dut->getKeywordsOfRecipe(1234, $user));

		$pairs = [
			['recipeId' => 1234, 'name' => 'A'],
			['recipeId' => 1234, 'name' => 'c'],
		];
		$this->dut->removeKeywordPairs($pairs, $user);

		$recipes = $this->dut->findAllRecipes($user);
		$expected = [
			'name' => 'My recipe',
			'recipe_id' => 1234,
			'dateCreated' => null,
			'dateModified' => null,
			'keywords' => 'B',
			'category' => null,
		];
		$this->assertEquals([$expected], $recipes);
	}

	public function testEmptyInsert() {
		$user = 'test-user';
		$this->dut->insertRecipes([], $user);
		$this->assertEmpty($this->dut->findAllRecipes($user));
	}
}
