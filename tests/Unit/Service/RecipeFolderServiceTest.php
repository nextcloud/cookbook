<?php

namespace OCA\Cookbook\tests\Unit\Service;

use OCA\Cookbook\Entity\Cookbook;
use OCP\Files\Folder;
use OCA\Cookbook\Entity\Recipe;
use OCA\Cookbook\Helper\FilesystemHelper;
use PHPUnit\Framework\TestCase;
use OCA\Cookbook\Helper\RecipeFolderHelper;
use PHPUnit\Framework\MockObject\MockObject;
use OCA\Cookbook\Service\RecipeFolderService;
use PHPUnit\Framework\MockObject\Stub;

class RecipeFolderServiceTest extends TestCase {
	
	/**
	 * @var RecipeFolderService
	 */
	private $dut;

	/** @var RecipeFolderHelper|MockObject */
	private $recipeFolderHelper;

	/** @var FilesystemHelper|MockObject */
	private $filesystemHelper;

	/** @var Stub|Recipe */
	private $recipe;

	/** @var Folder|MockObject */
	private $recipeFolder;

	protected function setUp(): void {
		parent::setUp();

		$this->recipeFolder = $this->createMock(Folder::class);
		$this->recipe = $this->createStub(Recipe::class);
		$this->recipe->method('getFolder')->willReturn($this->recipeFolder);

		$this->filesystemHelper = $this->createMock(FilesystemHelper::class);
		
		$this->recipeFolderHelper = $this->createMock(RecipeFolderHelper::class);

		$this->dut = new RecipeFolderService($this->recipeFolderHelper, $this->filesystemHelper);
	}
	
	public function testGetRecipe() {
		$value = 'This is the value in the file';

		$this->recipeFolderHelper->method('getContent')->with($this->recipeFolder, 'recipe.json')
		->willReturn($value);

		$this->assertEquals($value, $this->dut->getRecipe($this->recipe));
	}

	public function testPutRecipe() {
		$newValue = 'This is the new value of the recipe file';

		$this->recipeFolderHelper->expects($this->once())->method('putContent')
		->with($this->recipeFolder, 'recipe.json', $newValue);

		$this->dut->putRecipe($this->recipe, $newValue);
	}
	
	public function testGetImage() {
		$value = 'Some binary data';
		$this->recipeFolderHelper->method('getContent')->with($this->recipeFolder, 'full.jpg')
		->willReturn($value);
		$this->assertEquals($value, $this->dut->getImage($this->recipe));
	}
	
	public function testGetThumbnail() {
		$value = 'Some binary data';
		$this->recipeFolderHelper->method('getContent')->with($this->recipeFolder, 'thumb.jpg')
		->willReturn($value);
		$this->assertEquals($value, $this->dut->getThumbnail($this->recipe));
	}
	
	public function testGetSmallThumbnail() {
		$value = 'Some binary data';
		$this->recipeFolderHelper->method('getContent')->with($this->recipeFolder, 'thumb16.jpg')
		->willReturn($value);
		$this->assertEquals($value, $this->dut->getSmallThumbnail($this->recipe));
	}
	
	public function testPutImage() {
		$newValue = 'This is the new value of the recipe file';

		$this->recipeFolderHelper->expects($this->once())->method('putContent')
		->with($this->recipeFolder, 'full.jpg', $newValue);

		$this->dut->putImage($this->recipe, $newValue);
	}
	
	public function testPutThumbnail() {
		$newValue = 'This is the new value of the recipe file';

		$this->recipeFolderHelper->expects($this->once())->method('putContent')
		->with($this->recipeFolder, 'thumb.jpg', $newValue);

		$this->dut->putThumbnail($this->recipe, $newValue);
	}
	
	public function testPutSmallThumbnail() {
		$newValue = 'This is the new value of the recipe file';

		$this->recipeFolderHelper->expects($this->once())->method('putContent')
		->with($this->recipeFolder, 'thumb16.jpg', $newValue);

		$this->dut->putSmallThumbnail($this->recipe, $newValue);
	}

	public function testMake() {
		$folderName = 'The name of the folder';
		$this->recipe->method('getFolderName')->willReturn($folderName);

		$cookbookFolder = $this->createStub(Folder::class);
		$cookbook = $this->createStub(Cookbook::class);
		/** @var Stub|Cookbook $cookbook */
		$cookbook->method('getFolder')->willReturn($cookbookFolder);

		$this->recipe->method('getCookbook')->willReturn($cookbook);

		$this->filesystemHelper->expects($this->once())->method('ensureFolderExists')
		->with($folderName, $cookbookFolder);

		$this->dut->make($this->recipe);
	}

	public function testRemoveImages() {
		$this->filesystemHelper->expects($this->exactly(3))->method('ensureNodeDeleted')
		->withConsecutive(
			['full.jpg', $this->recipeFolder],
			['thumb.jpg', $this->recipeFolder],
			['thumb16.jpg', $this->recipeFolder],
		);

		$this->dut->removeImages($this->recipe);
	}

	public function testRemove() {
		$this->recipeFolder->expects($this->once())->method('delete');
		$this->dut->remove($this->recipe);
	}

	public function testCreateFolder() {
		$folderName = 'The name of the folder';

		/** @var MockObject|Cookbook $cookbook */
		$cookbook = $this->createMock(Cookbook::class);
		$this->recipe->method('getCookbook')->willReturn($cookbook);
		$this->recipe->method('getFolderName')->willReturn($folderName);

		$cookbook->expects($this->never())->method('cacheRecipeFolder');
		$cookbook->method('getRecipeFolder')->with($folderName)->willReturn($this->recipeFolder);

		$this->dut->createFolder($this->recipe);
	}

	public function testCreateFolderNonExisting() {
		$folderName = 'The name of the folder';

		$cookbookFolder = $this->createStub(Folder::class);

		/** @var MockObject|Cookbook $cookbook */
		$cookbook = $this->createMock(Cookbook::class);
		$this->recipe->method('getCookbook')->willReturn($cookbook);
		$this->recipe->method('getFolderName')->willReturn($folderName);
		
		$cookbook->method('getFolder')->willReturn($cookbookFolder);
		$cookbook->method('getRecipeFolder')->with($folderName)->willReturn(null);

		$this->filesystemHelper->expects($this->once())->method('ensureFolderExists')
		->with($folderName, $cookbookFolder)->willReturn($this->recipeFolder);

		$cookbook->expects($this->once())->method('cacheRecipeFolder')
		->with($folderName, $this->recipeFolder);

		$this->dut->createFolder($this->recipe);
	}
}
