<?php

namespace OCA\Cookbook\tests\Unit\Controller\Implementation;

use Exception;
use OCA\Cookbook\Controller\Implementation\CategoryImplementation;
use OCP\IRequest;
use OCP\Files\File;
use OCP\IURLGenerator;
use ReflectionProperty;
use PHPUnit\Framework\TestCase;
use OCA\Cookbook\Service\RecipeService;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCA\Cookbook\Service\DbCacheService;
use OCA\Cookbook\Controller\MainController;
use OCA\Cookbook\Helper\RestParameterParser;
use PHPUnit\Framework\MockObject\MockObject;
use OCA\Cookbook\Exception\RecipeExistsException;
use OCA\Cookbook\Exception\UserFolderNotWritableException;
use OCA\Cookbook\Helper\UserFolderHelper;

/**
 * @covers \OCA\Cookbook\Controller\Implementation\CategoryImplementation
 */
class CategoryImplementationTest extends TestCase {
	/**
	 * @var MockObject|RecipeService
	 */
	private $recipeService;
	/**
	 * @var IURLGenerator|MockObject
	 */
	private $urlGenerator;
	/**
	 * @var DbCacheService|MockObject
	 */
	private $dbCacheService;
	/**
	 * @var RestParameterParser|MockObject
	 */
	private $restParser;
	/**
	 * @var UserFolderHelper|MockObject
	 */
	private $userFolder;

	/**
	 * @var CategoryImplementation
	 */
	private $sut;

	public function setUp(): void {
		parent::setUp();

		$this->recipeService = $this->createMock(RecipeService::class);
		$this->dbCacheService = $this->createMock(DbCacheService::class);
		$this->restParser = $this->createMock(RestParameterParser::class);

		$this->sut = new CategoryImplementation(
			$this->recipeService,
			$this->dbCacheService,
			$this->restParser
		);
	}

	public function testConstructor(): void {
		$this->ensurePropertyIsCorrect('service', $this->recipeService);
		$this->ensurePropertyIsCorrect('dbCacheService', $this->dbCacheService);
		$this->ensurePropertyIsCorrect('restParser', $this->restParser);
	}

	private function ensurePropertyIsCorrect(string $name, &$val) {
		$property = new ReflectionProperty(CategoryImplementation::class, $name);
		$property->setAccessible(true);
		$this->assertSame($val, $property->getValue($this->sut));
	}

	private function ensureCacheCheckTriggered(): void {
		$this->dbCacheService->expects($this->once())->method('triggerCheck');
	}

	public function testGetCategories(): void {
		$this->ensureCacheCheckTriggered();

		$cat = ['Foo', 'Bar', 'Baz'];
		$this->recipeService->method('getAllCategoriesInSearchIndex')->willReturn($cat);

		$ret = $this->sut->index();
		$this->assertEquals(200, $ret->getStatus());
		$this->assertEquals($cat, $ret->getData());
	}




	/**
	 * @dataProvider dataProviderCategoryUpdateNoName
	 * @param mixed $requestParams
	 */
	public function testCategoryUpdateNoName($requestParams): void {
		$this->ensureCacheCheckTriggered();

		$this->restParser->expects($this->once())->method('getParameters')->willReturn($requestParams);

		$ret = $this->sut->rename('');

		$this->assertEquals(400, $ret->getStatus());
	}

	public function dataProviderCategoryUpdateNoName() {
		yield [[]];
		yield [[
			'some', 'variable'
		]];
		yield [['name' => null]];
		yield [['name' => '']];
	}

	/**
	 * @dataProvider dpCategoryUpdate
	 * @todo No business logic in controller
	 * @param mixed $cat
	 * @param mixed $oldCat
	 * @param mixed $recipes
	 */
	public function testCategoryUpdate($cat, $oldCat, $recipes): void {
		$this->ensureCacheCheckTriggered();

		$this->recipeService->expects($this->once())->method('getRecipesByCategory')->with($oldCat)->willReturn($recipes);
		$this->dbCacheService->expects($this->once())->method('updateCache');

		$this->restParser->expects($this->once())->method('getParameters')->willReturn(['name' => $cat]);

		$n = count($recipes);
		$indices = array_map(function ($v) {
			return [$v['recipe_id']];
		}, $recipes);
		$this->recipeService->expects($this->exactly($n))->method('getRecipeById')->withConsecutive(...$indices);
		$this->recipeService->expects($this->exactly($n))->method('addRecipe')->with($this->callback(function ($p) use ($cat) {
			return $p['recipeCategory'] === $cat;
		}));

		/**
		 * @var DataResponse $ret
		 */
		$ret = $this->sut->rename(urlencode($oldCat));

		$this->assertEquals(200, $ret->getStatus());
		$this->assertEquals($cat, $ret->getData());
	}

	public function dpCategoryUpdate() {
		return [
			'noRecipes' => [
				'new Category Name',
				'Old category',
				[]
			],
			'someRecipes' => [
				'new Category Name',
				'Old category',
				[
					[
						'name' => 'First recipe',
						'recipeCategory' => 'some fancy category',
						'recipe_id' => 123,
					],
					[
						'name' => 'Second recipe',
						'recipeCategory' => 'some fancy category',
						'recipe_id' => 124,
					],
				]
			],
		];
	}

	public function testCategoryUpdateFailure(): void {
		$this->ensureCacheCheckTriggered();

		$this->restParser->expects($this->once())->method('getParameters')->willReturn(['name' => 'New category']);

		$errorMsg = 'Something bad has happened.';
		$oldCat = 'Old category';

		$this->recipeService->expects($this->once())->method('getRecipesByCategory')->with($oldCat)->willThrowException(new Exception($errorMsg));

		/**
		 * @var DataResponse $ret
		 */
		$ret = $this->sut->rename(urlencode($oldCat));

		$this->assertEquals(500, $ret->getStatus());
		$this->assertEquals($errorMsg, $ret->getData());
	}
}
