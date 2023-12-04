<?php

namespace OCA\Cookbook\tests\Unit\Controller\Implementation;

use Exception;
use OCA\Cookbook\Controller\Implementation\RecipeImplementation;
use OCA\Cookbook\Exception\NoRecipeNameGivenException;
use OCA\Cookbook\Exception\RecipeExistsException;
use OCA\Cookbook\Helper\AcceptHeaderParsingHelper;
use OCA\Cookbook\Helper\Filter\Output\RecipeJSONOutputFilter;
use OCA\Cookbook\Helper\Filter\Output\RecipeStubFilter;
use OCA\Cookbook\Helper\RestParameterParser;
use OCA\Cookbook\Service\DbCacheService;
use OCA\Cookbook\Service\RecipeService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\IOutput;
use OCP\AppFramework\Http\JSONResponse;
use OCP\Files\File;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IURLGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OCA\Cookbook\Controller\Implementation\RecipeImplementation
 * @covers \OCA\Cookbook\Exception\UserFolderNotWritableException
 * @covers \OCA\Cookbook\Exception\RecipeExistsException
 * @covers \OCA\Cookbook\Exception\NoRecipeNameGivenException
 */
class RecipeImplementationTest extends TestCase {
	/** @var IRequest|MockObject */
	private $request;
	/** @var MockObject|RecipeService */
	private $recipeService;
	/** @var IURLGenerator|MockObject */
	private $urlGenerator;
	/** @var DbCacheService|MockObject */
	private $dbCacheService;
	/** @var RestParameterParser|MockObject */
	private $restParser;
	/** @var RecipeJSONOutputFilter|MockObject */
	private $recipeFilter;
	/** @var RecipeStubFilter|MockObject */
	private $stubFilter;
	/** @var AcceptHeaderParsingHelper|MockObject */
	private $acceptHeaderParser;

	/**
	 * @var RecipeImplementation
	 */
	private $sut;

	public function setUp(): void {
		parent::setUp();

		$this->request = $this->createMock(IRequest::class);
		$this->recipeService = $this->createMock(RecipeService::class);
		$this->dbCacheService = $this->createMock(DbCacheService::class);
		$this->urlGenerator = $this->createMock(IURLGenerator::class);
		$this->restParser = $this->createMock(RestParameterParser::class);
		$this->recipeFilter = $this->createMock(RecipeJSONOutputFilter::class);
		$this->stubFilter = $this->createMock(RecipeStubFilter::class);
		$this->acceptHeaderParser = $this->createMock(AcceptHeaderParsingHelper::class);

		/** @var IL10N|Stub */
		$l = $this->createStub(IL10N::class);
		$l->method('t')->willReturnArgument(0);

		$this->sut = new RecipeImplementation(
			$this->request,
			$this->recipeService,
			$this->dbCacheService,
			$this->urlGenerator,
			$this->restParser,
			$this->recipeFilter,
			$this->stubFilter,
			$this->acceptHeaderParser,
			$l
		);
	}

	private function ensureCacheCheckTriggered(): void {
		$this->dbCacheService->expects($this->once())->method('triggerCheck');
	}



	public function testImportFailed(): void {
		$this->ensureCacheCheckTriggered();

		$this->restParser->method('getParameters')->willReturn([]);

		/**
		 * @var JSONResponse $ret
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
		 * @var JSONResponse $ret
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
	 * @param mixed $cat
	 * @param mixed $recipes
	 */
	public function testCategory($cat, $recipes): void {
		$this->ensureCacheCheckTriggered();

		$this->recipeService->method('getRecipesByCategory')->with($cat)->willReturn($recipes);

		$expected = $this->getExpectedRecipes($recipes);

		/**
		 * @var JSONResponse $ret
		 */
		$ret = $this->sut->getAllInCategory(urlencode($cat));

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
		 * @var JSONResponse $ret
		 */
		$ret = $this->sut->getAllInCategory(urlencode($cat));

		$this->assertEquals(500, $ret->getStatus());
		$this->assertEquals($errorMsg, $ret->getData());
	}

	/**
	 * @dataProvider dataProviderTags
	 * @param mixed $keywords
	 * @param mixed $recipes
	 */
	public function testTags($keywords, $recipes): void {
		$this->ensureCacheCheckTriggered();

		$this->recipeService->method('getRecipesByKeywords')->with($keywords)->willReturn($recipes);

		$expected = $this->getExpectedRecipes($recipes);

		/**
		 * @var JSONResponse $ret
		 */
		$ret = $this->sut->getAllWithTags(urlencode($keywords));

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
		 * @var JSONResponse $ret
		 */
		$ret = $this->sut->getAllWithTags(urlencode($keywords));

		$this->assertEquals(500, $ret->getStatus());
		$this->assertEquals($errorMsg, $ret->getData());
	}

	/**
	 * @dataProvider dpSearch
	 * @todo no implementation in controller
	 * @param mixed $query
	 * @param mixed $recipes
	 */
	public function testSearch($query, $recipes): void {
		$this->ensureCacheCheckTriggered();

		$this->recipeService->expects($this->once())->method('findRecipesInSearchIndex')->with($query)->willReturn($recipes);

		$expected = $this->getExpectedRecipes($recipes);

		/**
		 * @var JSONResponse $res
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
		 * @var JSONResponse $res
		 */
		$res = $this->sut->search(urlencode($query));

		$this->assertEquals(500, $res->getStatus());
		$this->assertEquals($errorMsg, $res->getData());
	}

	public function testUpdate(): void {
		$this->ensureCacheCheckTriggered();

		$data = ['a', 'new', 'array'];
		/**
		 * @var File|MockObject $file
		 */
		$file = $this->createMock(File::class);
		$file->method('getParent')->willReturnSelf();
		$file->method('getId')->willReturn(50);

		$this->restParser->method('getParameters')->willReturn($data);
		$this->recipeService->expects($this->once())->method('addRecipe')->with($data)->willReturn($file);
		$this->dbCacheService->expects($this->once())->method('addRecipe')->with($file);

		$ret = $this->sut->update(1);

		$this->assertEquals(200, $ret->getStatus());
		$this->assertEquals(50, $ret->getData());
	}

	public function testUpdateNoName(): void {
		$this->ensureCacheCheckTriggered();

		$data = ['a', 'new', 'array'];

		$errorMsg = "No name was given for the recipe.";
		$ex = new NoRecipeNameGivenException($errorMsg);

		$this->restParser->method('getParameters')->willReturn($data);
		$this->recipeService->expects($this->once())->method('addRecipe')->with($data)->willThrowException($ex);
		$this->dbCacheService->expects($this->never())->method('addRecipe');

		$ret = $this->sut->update(1);

		$this->assertEquals(422, $ret->getStatus());
		$this->assertEquals($errorMsg, $ret->getData()['msg']);
	}

	public function testUpdateConflictingName(): void {
		$this->ensureCacheCheckTriggered();

		$recipe = ['a', 'recipe', 'as', 'array'];

		$errorMsg = "Another recipe with that name already exists";
		$ex = new RecipeExistsException($errorMsg);

		$this->restParser->method('getParameters')->willReturn($recipe);
		$this->recipeService->expects($this->once())->method('addRecipe')->with($recipe)->willThrowException($ex);
		$this->dbCacheService->expects($this->never())->method('addRecipe');

		$ret = $this->sut->update(1);

		$this->assertEquals(409, $ret->getStatus());
		$this->assertEquals($errorMsg, $ret->getData()['msg']);
	}

	public function testCreate(): void {
		$this->ensureCacheCheckTriggered();

		$recipe = ['a', 'recipe', 'as', 'array'];
		$this->restParser->method('getParameters')->willReturn($recipe);

		/**
		 * @var File|MockObject $file
		 */
		$file = $this->createMock(File::class);
		$id = 23;
		$file->method('getId')->willReturn($id);
		$file->method('getParent')->willReturnSelf();

		$this->recipeService->expects($this->once())->method('addRecipe')->with($recipe)->willReturn($file);

		$this->dbCacheService->expects($this->once())->method('addRecipe')->with($file);

		$ret = $this->sut->create();

		$this->assertEquals(200, $ret->getStatus());
		$this->assertEquals($id, $ret->getData());
	}

	public function testCreateNoName(): void {
		$this->ensureCacheCheckTriggered();

		$recipe = ['a', 'recipe', 'as', 'array'];
		$this->restParser->method('getParameters')->willReturn($recipe);

		$errorMsg = "The error that was triggered";
		$ex = new NoRecipeNameGivenException($errorMsg);

		$this->recipeService->expects($this->once())->method('addRecipe')->with($recipe)->willThrowException($ex);

		$this->dbCacheService->expects($this->never())->method('addRecipe');

		$ret = $this->sut->create();

		$this->assertEquals(422, $ret->getStatus());
		$this->assertEquals($errorMsg, $ret->getData()['msg']);
	}

	public function testCreateExisting(): void {
		$this->ensureCacheCheckTriggered();

		$recipe = ['a', 'recipe', 'as', 'array'];
		$this->restParser->method('getParameters')->willReturn($recipe);

		$ex = new RecipeExistsException('message');
		$this->recipeService->expects($this->once())->method('addRecipe')->with($recipe)->willThrowException($ex);

		$this->dbCacheService->expects($this->never())->method('addRecipe');

		$ret = $this->sut->create();

		$this->assertEquals(409, $ret->getStatus());
		$expected = [
			'msg' => 'message',
			'file' => __FILE__,
			'line' => $ex->getLine(),
		];
		$this->assertEquals($expected, $ret->getData());
	}

	public function testShow(): void {
		$this->ensureCacheCheckTriggered();

		$id = 123;
		$recipe = [
			'name' => "My Name",
			'description' => 'a useful description',
			'id' => $id,
		];
		$this->recipeService->method('getRecipeById')->with($id)->willReturn($recipe);
		$this->recipeService->method('getPrintImage')->willReturn(true);
		$imageUrl = "/path/to/image/of/id/123";

		$this->urlGenerator->method('linkToRoute')->with(
			'cookbook.recipe.image',
			$this->callback(function ($p) use ($id) {
				return isset($p['size']) && $p['id'] === $id;
			})
		)->willReturn($imageUrl);
		$expected = $recipe;
		$expected['printImage'] = true;
		$expected['imageUrl'] = $imageUrl;

		$this->recipeFilter->method('filter')->willReturnArgument(0);

		/**
		 * @var JSONResponse $ret
		 */
		$ret = $this->sut->show($id);

		$this->assertEquals(200, $ret->getStatus());
		$this->assertEquals($expected, $ret->getData());
	}

	public function testShowFailure(): void {
		$this->ensureCacheCheckTriggered();

		$id = 123;
		$this->recipeService->method('getRecipeById')->with($id)->willReturn(null);

		/**
		 * @var JSONResponse $ret
		 */
		$ret = $this->sut->show($id);

		$this->assertEquals(404, $ret->getStatus());
	}

	public function testDestroy(): void {
		$this->ensureCacheCheckTriggered();
		$id = 123;

		$this->recipeService->expects($this->once())->method('deleteRecipe')->with($id);
		/**
		 * @var JSONResponse $ret
		 */
		$ret = $this->sut->destroy($id);

		$this->assertEquals(200, $ret->getStatus());
	}

	public function testDestroyFailed(): void {
		$this->ensureCacheCheckTriggered();
		$id = 123;
		$errorMsg = 'This is the error message.';
		$this->recipeService->expects($this->once())->method('deleteRecipe')->with($id)->willThrowException(new Exception($errorMsg));

		/**
		 * @var JSONResponse $ret
		 */
		$ret = $this->sut->destroy($id);

		$this->assertEquals(502, $ret->getStatus());
		$this->assertEquals($errorMsg, $ret->getData());
	}

	/**
	 * @dataProvider dataProviderImage
	 * @todo Assert on image data/file name
	 * @todo Avoid business code in controller
	 * @param mixed $setSize
	 * @param mixed $size
	 */
	public function testImage($setSize, $size): void {
		$this->ensureCacheCheckTriggered();

		if ($setSize) {
			$_GET['size'] = $size;
		}

		/** @var File|Stub */
		$file = $this->createStub(File::class);
		$id = 123;
		$this->recipeService->method('getRecipeImageFileByFolderId')->with($id, $size)->willReturn($file);

		// Make the tests stable against PHP deprecation warnings
		$file->method('getMTime')->willReturn(100);
		$file->method('getName')->willReturn('image.jpg');

		/**
		 * @var FileDisplayResponse $ret
		 */
		$ret = $this->sut->image($id);

		$this->assertEquals(200, $ret->getStatus());

		// Hack: Get output via IOutput mockup
		/**
		 * @var MockObject|IOutput $output
		 */
		$output = $this->createMock(IOutput::class);
		$file->method('getSize')->willReturn(100);
		$content = 'Some content comes here';
		$file->method('getContent')->willReturn($content);

		$output->method('getHttpResponseCode')->willReturn(Http::STATUS_OK);
		$output->expects($this->atLeastOnce())->method('setOutput')->with($content);

		$ret->callback($output);
	}

	public function dataProviderImage(): array {
		return [
			[false, null],
			[true, null],
			[true, 'full'],
		];
	}

	public function dpImageNotFound() {
		yield [['jpg', 'png'], 406];
		yield [['jpg', 'png', 'svg'], 200];
	}

	/**
	 * @dataProvider dpImageNotFound
	 * @param mixed $accept
	 * @param mixed $expectedStatus
	 */
	public function testImageNotFound($accept, $expectedStatus) {
		$id = 123;

		$ex = new Exception();
		$this->recipeService->method('getRecipeImageFileByFolderId')->willThrowException($ex);

		$headerContent = 'The content of the header as supposed by teh framework';
		$this->request->method('getHeader')->with('Accept')->willReturn($headerContent);
		$this->acceptHeaderParser->method('parseHeader')->willReturnMap([
			[$headerContent, $accept],
		]);

		$ret = $this->sut->image($id);

		$this->assertEquals($expectedStatus, $ret->getStatus());
	}

	/**
	 * @dataProvider dataProviderIndex
	 * @todo no work on controller
	 * @param mixed $recipes
	 * @param mixed $setKeywords
	 * @param mixed $keywords
	 */
	public function testIndex($recipes, $setKeywords, $keywords): void {
		$this->ensureCacheCheckTriggered();

		$this->recipeService->method('getAllRecipesInSearchIndex')->willReturn($recipes);
		$this->recipeService->method('findRecipesInSearchIndex')->willReturn($recipes);

		if ($setKeywords) {
			$_GET['keywords'] = $keywords;
			$this->recipeService->expects($this->once())->method('findRecipesInSearchIndex')->with($keywords);
		} else {
			$this->recipeService->expects($this->once())->method('getAllRecipesInSearchIndex');
		}

		$this->urlGenerator->method('linkToRoute')->will($this->returnCallback(function ($name, $params) {
			if ($name !== 'cookbook.recipe.image') {
				throw new Exception('Must use correct controller');
			}

			$id = $params['id'];
			$size = $params['size'];
			return "/path/to/controller/$id/$size";
		}));

		$this->stubFilter->method('apply')->willReturnArgument(0);

		/**
		 * @var JSONResponse $ret
		 */
		$ret = $this->sut->index();

		$this->assertEquals(200, $ret->getStatus());
		$this->assertEquals($this->updateIndexRecipesAsExpected($recipes), $ret->getData());

		// $this->markTestIncomplete('assertions are missing');
	}

	private function updateIndexRecipesAsExpected($recipes): array {
		$ret = $recipes;
		for ($i = 0; $i < count($ret); $i++) {
			$id = $ret[$i]['recipe_id'];
			$ret[$i]['imageUrl'] = "/path/to/controller/$id/thumb";
			$ret[$i]['imagePlaceholderUrl'] = "/path/to/controller/$id/thumb16";
		}

		return $ret;
	}

	public function dataProviderIndex(): array {
		return [
			'emptyIndex' => [
				[],
				false,
				null,
			],
			'normalIndex' => [
				[
					[
						'recipe_id' => 123,
						'name' => 'First recipe',
					],
					[
						'recipe_id' => 125,
						'name' => 'Second recipe',
					],
				],
				false,
				null,
			],
			'empySearch' => [
				[],
				true,
				'a,b,c',
			],
		];
	}
}
