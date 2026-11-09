<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Cookbook\tests\Unit\Service;

use OCA\Cookbook\Db\RecipeDb;
use OCA\Cookbook\Exception\ImportException;
use OCA\Cookbook\Exception\NoRecipeNameGivenException;
use OCA\Cookbook\Helper\DownloadHelper;
use OCA\Cookbook\Helper\FileSystem\RecipeNameHelper;
use OCA\Cookbook\Helper\Filter\JSON\JSONFilter;
use OCA\Cookbook\Helper\ImageService\ImageConversionHelper;
use OCA\Cookbook\Helper\UserConfigHelper;
use OCA\Cookbook\Helper\UserFolderHelper;
use OCA\Cookbook\Service\HtmlDownloadService;
use OCA\Cookbook\Service\ImageService;
use OCA\Cookbook\Service\RecipeExtractionService;
use OCA\Cookbook\Service\RecipeService;
use OCP\Files\IRootFolder;
use OCP\IL10N;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RecipeServiceTest extends TestCase {
	/** @var RecipeExtractionService|Stub */
	private $extractionService;
	/** @var JSONFilter|Stub */
	private $jsonFilter;
	private RecipeService $sut;

	protected function setUp(): void {
		parent::setUp();

		$downloadService = $this->createStub(HtmlDownloadService::class);
		$downloadService->method('getDom')->willReturn(new \DOMDocument());
		$this->extractionService = $this->createStub(RecipeExtractionService::class);
		$this->jsonFilter = $this->createStub(JSONFilter::class);

		$this->sut = new RecipeService(
			'user',
			$this->createStub(IRootFolder::class),
			$this->createStub(RecipeDb::class),
			$this->createStub(UserConfigHelper::class),
			$this->createStub(UserFolderHelper::class),
			$this->createStub(ImageService::class),
			$this->createStub(RecipeNameHelper::class),
			$this->createStub(IL10N::class),
			$this->createStub(LoggerInterface::class),
			$downloadService,
			$this->extractionService,
			$this->jsonFilter,
			$this->createStub(DownloadHelper::class),
			$this->createStub(ImageConversionHelper::class),
		);
	}

	public function testDownloadRecipeWithoutNameFailsAtExtractStep(): void {
		$this->extractionService->method('parse')->willReturn(['description' => 'No name']);

		try {
			$this->sut->downloadRecipe('https://example.com/recipe');
			$this->fail('ImportException expected');
		} catch (ImportException $e) {
			$this->assertEquals('extract_recipe', $e->getStep());
			$this->assertEquals('Field "name" is required', $e->getMessage());
		}
	}

	public function testDownloadRecipeWithNameRemovedByFilterFailsAtExtractStep(): void {
		$this->extractionService->method('parse')->willReturn(['name' => 'Name']);
		$this->jsonFilter->method('apply')->willReturn(['name' => '']);

		try {
			$this->sut->downloadRecipe('https://example.com/recipe');
			$this->fail('ImportException expected');
		} catch (ImportException $e) {
			$this->assertEquals('extract_recipe', $e->getStep());
			$this->assertInstanceOf(NoRecipeNameGivenException::class, $e->getPrevious());
		}
	}
}
