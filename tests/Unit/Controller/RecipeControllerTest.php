<?php

namespace OCA\Cookbook\tests\Unit\Controller;

use Exception;
use OCP\IRequest;
use OCP\Files\File;
use OCP\IURLGenerator;
use ReflectionProperty;
use OCP\AppFramework\Http;
use PHPUnit\Framework\TestCase;
use OCP\AppFramework\Http\IOutput;
use OCA\Cookbook\Service\RecipeService;
use OCP\AppFramework\Http\DataResponse;
use OCA\Cookbook\Service\DbCacheService;
use OCA\Cookbook\Helper\RestParameterParser;
use PHPUnit\Framework\MockObject\MockObject;
use OCA\Cookbook\Controller\RecipeController;
use OCA\Cookbook\Exception\NoRecipeNameGivenException;
use OCP\AppFramework\Http\FileDisplayResponse;
use OCA\Cookbook\Exception\RecipeExistsException;

/**
 * @coversDefaultClass \OCA\Cookbook\Controller\RecipeController
 * @covers ::<private>
 * @covers ::<protected>
 */
class RecipeControllerTest extends TestCase {
	/**
	 * @var RecipeService|MockObject
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
	 * @var RecipeController
	 */
	private $sut;

	public function setUp(): void {
		parent::setUp();

		$this->recipeService = $this->createMock(RecipeService::class);
		$this->urlGenerator = $this->createMock(IURLGenerator::class);
		$this->dbCacheService = $this->createMock(DbCacheService::class);
		$this->restParser = $this->createMock(RestParameterParser::class);
		$request = $this->createStub(IRequest::class);

		$this->sut = new RecipeController('cookbook', $request, $this->urlGenerator, $this->recipeService, $this->dbCacheService, $this->restParser);
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstructor(): void {
		$this->ensurePropertyIsCorrect('urlGenerator', $this->urlGenerator);
		$this->ensurePropertyIsCorrect('service', $this->recipeService);
		$this->ensurePropertyIsCorrect('dbCacheService', $this->dbCacheService);
		$this->ensurePropertyIsCorrect('restParser', $this->restParser);
	}

	private function ensurePropertyIsCorrect(string $name, &$val) {
		$property = new ReflectionProperty(RecipeController::class, $name);
		$property->setAccessible(true);
		$this->assertSame($val, $property->getValue($this->sut));
	}

	private function ensureCacheCheckTriggered(): void {
		$this->dbCacheService->expects($this->once())->method('triggerCheck');
	}

	/**
	 * @covers ::update
	 * @todo Foo
	 */
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

	/**
	 * @covers ::update
	 * @todo Foo
	 */
	public function testUpdateNoName(): void {
		$this->ensureCacheCheckTriggered();

		$data = ['a', 'new', 'array'];

		$errorMsg = "No name was given for the recipe.";
		$ex = new NoRecipeNameGivenException($errorMsg);

		$this->restParser->method('getParameters')->willReturn($data);
		$this->recipeService->expects($this->once())->method('addRecipe')->with($data)->willThrowException($ex);
		$this->dbCacheService->expects($this->never())->method('addRecipe');

		$ret = $this->sut->update(1);

		$this->assertEquals(406, $ret->getStatus());
		$this->assertEquals($errorMsg, $ret->getData()['msg']);
	}

	/**
	 * @covers ::create
	 */
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

	/**
	 * @covers ::create
	 */
	public function testCreateNoName(): void {
		$this->ensureCacheCheckTriggered();

		$recipe = ['a', 'recipe', 'as', 'array'];
		$this->restParser->method('getParameters')->willReturn($recipe);

		$errorMsg = "The error that was triggered";
		$ex = new NoRecipeNameGivenException($errorMsg);

		$this->recipeService->expects($this->once())->method('addRecipe')->with($recipe)->willThrowException($ex);

		$this->dbCacheService->expects($this->never())->method('addRecipe');

		$ret = $this->sut->create();

		$this->assertEquals(406, $ret->getStatus());
		$this->assertEquals($errorMsg, $ret->getData()['msg']);
	}

	/**
	 * @covers ::create
	 */
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

	/**
	 * @covers ::show
	 */
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

		/**
		 * @var DataResponse $ret
		 */
		$ret = $this->sut->show($id);

		$this->assertEquals(200, $ret->getStatus());
		$this->assertEquals($expected, $ret->getData());
	}

	/**
	 * @covers ::show
	 */
	public function testShowFailure(): void {
		$this->ensureCacheCheckTriggered();

		$id = 123;
		$this->recipeService->method('getRecipeById')->with($id)->willReturn(null);
		
		/**
		 * @var DataResponse $ret
		 */
		$ret = $this->sut->show($id);

		$this->assertEquals(404, $ret->getStatus());
	}

	/**
	 * @covers ::destroy
	 */
	public function testDestroy(): void {
		$this->ensureCacheCheckTriggered();
		$id = 123;

		$this->recipeService->expects($this->once())->method('deleteRecipe')->with($id);
		/**
		 * @var DataResponse $ret
		 */
		$ret = $this->sut->destroy($id);

		$this->assertEquals(200, $ret->getStatus());
	}

	/**
	 * @covers ::destroy
	 */
	public function testDestroyFailed(): void {
		$this->ensureCacheCheckTriggered();
		$id = 123;
		$errorMsg = 'This is the error message.';
		$this->recipeService->expects($this->once())->method('deleteRecipe')->with($id)->willThrowException(new Exception($errorMsg));

		/**
		 * @var DataResponse $ret
		 */
		$ret = $this->sut->destroy($id);

		$this->assertEquals(502, $ret->getStatus());
		$this->assertEquals($errorMsg, $ret->getData());
	}

	/**
	 * @covers ::image
	 * @dataProvider dataProviderImage
	 * @todo Assert on image data/file name
	 * @todo Avoid business codde in controller
	 */
	public function testImage($setSize, $size): void {
		$this->ensureCacheCheckTriggered();

		if ($setSize) {
			$_GET['size'] = $size;
		}

		$file = $this->createStub(File::class);
		$id = 123;
		$this->recipeService->method('getRecipeImageFileByFolderId')->with($id, $size)->willReturn($file);

		/**
		 * @var FileDisplayResponse $ret
		 */
		$ret = $this->sut->image($id);

		$this->assertEquals(200, $ret->getStatus());

		// Hack: Get output via IOutput mockup
		/**
		 * @var MockObject|Ioutput $output
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

	/**
	 * @covers ::index
	 * @dataProvider dataProviderIndex
	 * @todo no work on controller
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

		/**
		 * @var DataResponse $ret
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
