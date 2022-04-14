<?php

namespace OCA\Cookbook\tests\Unit\Controller;

use Exception;
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

/**
 * @covers \OCA\Cookbook\Controller\MainController
 * @covers \OCA\Cookbook\Exception\UserFolderNotWritableException
 */
class MainControllerTest extends TestCase {
	
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
	 * @var MainController
	 */
	private $sut;

	public function setUp(): void {
		parent::setUp();

		$this->recipeService = $this->createMock(RecipeService::class);
		$this->urlGenerator = $this->createMock(IURLGenerator::class);
		$this->dbCacheService = $this->createMock(DbCacheService::class);
		$this->restParser = $this->createMock(RestParameterParser::class);
		$request = $this->createStub(IRequest::class);

		$this->sut = new MainController('cookbook', $request, $this->recipeService, $this->dbCacheService, $this->urlGenerator, $this->restParser);
	}

	public function testConstructor(): void {
		$this->ensurePropertyIsCorrect('urlGenerator', $this->urlGenerator);
		$this->ensurePropertyIsCorrect('service', $this->recipeService);
		$this->ensurePropertyIsCorrect('dbCacheService', $this->dbCacheService);
		$this->ensurePropertyIsCorrect('restParser', $this->restParser);
	}

	private function ensurePropertyIsCorrect(string $name, &$val) {
		$property = new ReflectionProperty(MainController::class, $name);
		$property->setAccessible(true);
		$this->assertSame($val, $property->getValue($this->sut));
	}

	private function ensureCacheCheckTriggered(): void {
		$this->dbCacheService->expects($this->once())->method('triggerCheck');
	}

	public function testIndex(): void {
		$this->ensureCacheCheckTriggered();
		$ret = $this->sut->index();
		$this->assertEquals(200, $ret->getStatus());
		$this->assertEquals('index', $ret->getTemplateName());
	}

	public function testIndexInvalidUser(): void {
		$this->recipeService->method('getFolderForUser')->willThrowException(new UserFolderNotWritableException());
		$ret = $this->sut->index();
		$this->assertEquals(200, $ret->getStatus());
		$this->assertEquals('invalid_guest', $ret->getTemplateName());
	}

	public function testGetAPIVersion(): void {
		$ret = $this->sut->getApiVersion();
		$this->assertEquals(200, $ret->getStatus());

		$retData = $ret->getData();
		$this->assertTrue(isset($retData['cookbook_version']));
		$this->assertEquals(3, count($retData['cookbook_version']));
		$this->assertTrue(isset($retData['api_version']));
		$this->assertTrue(isset($retData['api_version']['epoch']));
		$this->assertTrue(isset($retData['api_version']['major']));
		$this->assertTrue(isset($retData['api_version']['minor']));
	}

	public function testGetCategories(): void {
		$this->ensureCacheCheckTriggered();
		
		$cat = ['Foo', 'Bar', 'Baz'];
		$this->recipeService->expects($this->once())->method('getAllCategoriesInSearchIndex')->willReturn($cat);

		$ret = $this->sut->categories();
		$this->assertEquals(200, $ret->getStatus());
		$this->assertEquals($cat, $ret->getData());
	}

	public function testGetKeywords(): void {
		$this->ensureCacheCheckTriggered();
		
		$kw = ['Foo', 'Bar', 'Baz'];
		$this->recipeService->expects($this->once())->method('getAllKeywordsInSearchIndex')->willReturn($kw);

		$ret = $this->sut->keywords();
		$this->assertEquals(200, $ret->getStatus());
		$this->assertEquals($kw, $ret->getData());
	}

	/**
	 * @dataProvider dataProviderNew
	 */
	public function testNew($data, $id): void {
		$this->ensureCacheCheckTriggered();

		$this->restParser->method('getParameters')->willReturn($data);
		$file = $this->createMock(File::class);
		$file->method('getParent')->willReturnSelf();
		$file->method('getId')->willReturn($id);
		$this->recipeService->expects($this->once())->method('addRecipe')->with($data)->willReturn($file);
		$this->dbCacheService->expects($this->once())->method('addRecipe')->with($file);

		/**
		 * @var DataResponse $ret
		 */
		$ret = $this->sut->new();

		$this->assertEquals(200, $ret->getStatus());
		$this->assertEquals($id, $ret->getData());
	}

	public function dataProviderNew() {
		return [
			'success' => [
				['some', 'recipe', 'data'],
				10
			],
		];
	}

	/**
	 * @dataProvider dataProviderNew
	 */
	public function testNewFailed($data, $id): void {
		$this->ensureCacheCheckTriggered();

		$errMsg = 'My error message';

		$this->restParser->method('getParameters')->willReturn($data);
		$this->recipeService->expects($this->once())->method('addRecipe')->with($data)->willThrowException(new Exception($errMsg));

		/**
		 * @var DataResponse $ret
		 */
		$ret = $this->sut->new();

		$this->assertEquals(500, $ret->getStatus());
		$this->assertEquals($errMsg, $ret->getData());
	}

	/**
	 * @dataProvider dataProviderUpdate
	 */
	public function testUpdate($data, $id): void {
		$this->ensureCacheCheckTriggered();

		$this->restParser->method('getParameters')->willReturn($data);
		$file = $this->createMock(File::class);
		$file->method('getParent')->willReturnSelf();
		$file->method('getId')->willReturn($id);
		$this->recipeService->expects($this->once())->method('addRecipe')->with($data)->willReturn($file);
		$this->dbCacheService->expects($this->once())->method('addRecipe')->with($file);

		/**
		 * @var DataResponse $ret
		 */
		$ret = $this->sut->update($id);

		$this->assertEquals(200, $ret->getStatus());
		$this->assertEquals($id, $ret->getData());
	}

	public function dataProviderUpdate() {
		return [
			'success' => [
				['some', 'recipe', 'data', 'id' => 10],
				10
			],
		];
	}

	/**
	 * @dataProvider dataProviderUpdate
	 */
	public function testUpdateFailed($data, $id): void {
		$this->ensureCacheCheckTriggered();

		$errMsg = 'My error message';

		$this->restParser->method('getParameters')->willReturn($data);
		$this->recipeService->expects($this->once())->method('addRecipe')->with($data)->willThrowException(new Exception($errMsg));

		/**
		 * @var DataResponse $ret
		 */
		$ret = $this->sut->update($id);

		$this->assertEquals(500, $ret->getStatus());
		$this->assertEquals($errMsg, $ret->getData());
	}

	public function testImportFailed(): void {
		$this->ensureCacheCheckTriggered();

		$this->restParser->method('getParameters')->willReturn([]);

		/**
		 * @var DataResponse $ret
		 */
		$ret = $this->sut->import();

		$this->assertEquals(400, $ret->getStatus());
	}

	public function testImport(): void {
		$this->ensureCacheCheckTriggered();

		$url = 'http://example.com/Recipe.html';
		$file = $this->createStub(File::class);
		$json = [
			'id' => 123,
			'name' => 'The recipe name',
		];

		$this->restParser->method('getParameters')->willReturn([ 'url' => $url ]);
		$this->recipeService->expects($this->once())->method('downloadRecipe')->with($url)->willReturn($file);
		$this->recipeService->expects($this->once())->method('parseRecipeFile')->with($file)->willReturn($json);
		$this->dbCacheService->expects($this->once())->method('addRecipe')->with($file);

		/**
		 * @var DataResponse $ret
		 */
		$ret = $this->sut->import();

		$this->assertEquals(200, $ret->getStatus());
		$this->assertEquals($json, $ret->getData());
	}

	public function testImportExisting(): void {
		$this->ensureCacheCheckTriggered();

		$url = 'http://example.com/Recipe.html';
		$errorMsg = 'The error message';
		$ex = new RecipeExistsException($errorMsg);

		$this->restParser->method('getParameters')->willReturn([ 'url' => $url ]);
		$this->recipeService->expects($this->once())->method('downloadRecipe')->with($url)->willThrowException($ex);

		/**
		 * @var JSONResponse $ret
		 */
		$ret = $this->sut->import();

		$expected = [
			'msg' => $ex->getMessage(),
			'line' => $ex->getLine(),
			'file' => $ex->getFile(),
		];

		$this->assertEquals(409, $ret->getStatus());
		$this->assertEquals($expected, $ret->getData());
	}

	public function testImportOther(): void {
		$this->ensureCacheCheckTriggered();

		$url = 'http://example.com/Recipe.html';
		$errorMsg = 'The error message';
		$ex = new Exception($errorMsg);

		$this->restParser->method('getParameters')->willReturn([ 'url' => $url ]);
		$this->recipeService->expects($this->once())->method('downloadRecipe')->with($url)->willThrowException($ex);

		/**
		 * @var JSONResponse $ret
		 */
		$ret = $this->sut->import();


		$this->assertEquals(400, $ret->getStatus());
		$this->assertEquals($errorMsg, $ret->getData());
	}

	/**
	 * @dataProvider dataProviderCategory
	 */
	public function testCategory($cat, $recipes): void {
		$this->ensureCacheCheckTriggered();

		$this->recipeService->method('getRecipesByCategory')->with($cat)->willReturn($recipes);
		
		$expected = $this->getExpectedRecipes($recipes);

		/**
		 * @var DataResponse $ret
		 */
		$ret = $this->sut->category(urlencode($cat));

		$this->assertEquals(200, $ret->getStatus());
		$this->assertEquals($expected, $ret->getData());
	}

	private function getExpectedRecipes($recipes): array {
		$ret = $recipes;

		$ids = [];
		for ($i = 0; $i < count($recipes); $i++) {
			$id = $recipes[$i]['recipe_id'];
			$ids[] = $id;
			$ret[$i]['imageUrl'] = "/path/to/image/$id/thumb";
			$ret[$i]['imagePlaceholderUrl'] = "/path/to/image/$id/thumb16";
		}

		$this->urlGenerator->method('linkToRoute')->with(
			'cookbook.recipe.image',
			$this->callback(function ($p) use ($ids) {
				return isset($p['id']) && isset($p['size']) && false !== array_search($p['id'], $ids);
			})
		)->willReturnCallback(function ($name, $p) use ($ret) {
			// return $ret[$idx[$p['id']]];
			$id = $p['id'];
			$size = $p['size'];
			return "/path/to/image/$id/$size";
		});

		return $ret;
	}

	public function dataProviderCategory(): array {
		return [
			'noRecipes' => [
				'My category',
				[]
			],
			'someRecipes' => [
				'My category',
				[
					[
						'name' => 'My recipe 1',
						'recipe_id' => 123,
					],
					[
						'name' => 'My recipe 2',
						'recipe_id' => 122,
					],
				]
			],
		];
	}

	public function testCategoryFailed(): void {
		$this->ensureCacheCheckTriggered();

		$cat = 'My category';
		$errorMsg = 'The error is found.';
		$this->recipeService->method('getRecipesByCategory')->with($cat)->willThrowException(new Exception($errorMsg));
		
		/**
		 * @var DataResponse $ret
		 */
		$ret = $this->sut->category(urlencode($cat));

		$this->assertEquals(500, $ret->getStatus());
		$this->assertEquals($errorMsg, $ret->getData());
	}
	
	/**
	 * @dataProvider dataProviderTags
	 */
	public function testTags($keywords, $recipes): void {
		$this->ensureCacheCheckTriggered();

		$this->recipeService->method('getRecipesByKeywords')->with($keywords)->willReturn($recipes);
		
		$expected = $this->getExpectedRecipes($recipes);

		/**
		 * @var DataResponse $ret
		 */
		$ret = $this->sut->tags(urlencode($keywords));

		$this->assertEquals(200, $ret->getStatus());
		$this->assertEquals($expected, $ret->getData());
	}

	public function dataProviderTags(): array {
		return [
			'noTag' => [
				'*',
				[
					[
						'name' => 'My recipe 1',
						'recipe_id' => 123,
					],
					[
						'name' => 'My recipe 2',
						'recipe_id' => 122,
					],
				]
			],
			'noRecipes' => [
				'Tag A,Tag B',
				[]
			],
			'someRecipes' => [
				'Tag A, Tag B',
				[
					[
						'name' => 'My recipe 1',
						'recipe_id' => 123,
					],
					[
						'name' => 'My recipe 2',
						'recipe_id' => 122,
					],
				]
			],
		];
	}
	
	public function testTagsFailed(): void {
		$this->ensureCacheCheckTriggered();

		$keywords = 'Tag 1,Tag B';
		$errorMsg = 'The error is found.';
		$this->recipeService->method('getRecipesByKeywords')->with($keywords)->willThrowException(new Exception($errorMsg));
		
		/**
		 * @var DataResponse $ret
		 */
		$ret = $this->sut->tags(urlencode($keywords));

		$this->assertEquals(500, $ret->getStatus());
		$this->assertEquals($errorMsg, $ret->getData());
	}

	/**
	 * @dataProvider dpSearch
	 * @todo no implementation in controller
	 */
	public function testSearch($query, $recipes): void {
		$this->ensureCacheCheckTriggered();

		$this->recipeService->expects($this->once())->method('findRecipesInSearchIndex')->with($query)->willReturn($recipes);

		$expected = $this->getExpectedRecipes($recipes);

		/**
		 * @var DataResponse $res
		 */
		$res = $this->sut->search(urlencode($query));

		$this->assertEquals(200, $res->getStatus());
		$this->assertEquals($expected, $res->getData());
	}

	public function dpSearch() {
		return [
			'noRecipes' => [
				'some query',
				[],
			],
			'someRecipes' => [
				'some query',
				[
					[
						'name' => 'First recipe',
						'recipe_id' => 123,
					],
				],
			],
		];
	}
	
	public function testSearchFailed(): void {
		$this->ensureCacheCheckTriggered();

		$query = 'some query';
		$errorMsg = 'Could not search for recipes';
		$this->recipeService->expects($this->once())->method('findRecipesInSearchIndex')->with($query)->willThrowException(new Exception($errorMsg));

		/**
		 * @var DataResponse $res
		 */
		$res = $this->sut->search(urlencode($query));

		$this->assertEquals(500, $res->getStatus());
		$this->assertEquals($errorMsg, $res->getData());
	}

	/**
	 * @dataProvider dataProviderCategoryUpdateNoName
	 */
	public function testCategoryUpdateNoName($requestParams): void {
		$this->ensureCacheCheckTriggered();

		$this->restParser->expects($this->once())->method('getParameters')->willReturn($requestParams);

		$ret = $this->sut->categoryUpdate('');

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
		$ret = $this->sut->categoryUpdate(urlencode($oldCat));

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
		$ret = $this->sut->categoryUpdate(urlencode($oldCat));

		$this->assertEquals(500, $ret->getStatus());
		$this->assertEquals($errorMsg, $ret->getData());
	}
}
