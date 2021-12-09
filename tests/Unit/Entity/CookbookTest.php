<?php

namespace OCA\Cookbook\tests\Unit\Entity;

use OCA\Cookbook\Entity\Cookbook;
use OCA\Cookbook\Helper\UserFolderHelper;
use OCP\Files\Folder;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class CookbookTest extends TestCase {
	/**
	 * @var Cookbook
	 */
	private $dut;

	/**
	 * @var Stub|UserFolderHelper
	 */
	private $userFolderHelper;

	protected function setUp(): void {
		$this->userFolderHelper = $this->createStub(UserFolderHelper::class);
		$this->dut = new Cookbook($this->userFolderHelper);
	}

	public function testGetFolder() {
		$folder = $this->createStub(Folder::class);

		$this->userFolderHelper->method('getFolder')->willReturn($folder);

		$this->assertSame($folder, $this->dut->getFolder());
	}

	public function testCache() {
		$folderName = "This is the folder name";
		$folder = $this->createStub(Folder::class);

		$this->assertNull($this->dut->getRecipeFolder($folderName));

		$this->dut->cacheRecipeFolder($folderName, $folder);
		$this->assertSame($folder, $this->dut->getRecipeFolder($folderName));
	}
}
