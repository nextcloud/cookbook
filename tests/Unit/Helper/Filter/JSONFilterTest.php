<?php

namespace OCA\Cookbook\tests\Unit\Helper\Filter;

use OCA\Cookbook\Helper\Filter\JSON\CleanCategoryFilter;
use OCA\Cookbook\Helper\Filter\JSON\ExtractImageUrlFilter;
use OCA\Cookbook\Helper\Filter\JSON\FixDescriptionFilter;
use OCA\Cookbook\Helper\Filter\JSON\FixDurationsFilter;
use OCA\Cookbook\Helper\Filter\JSON\FixImageSchemeFilter;
use OCA\Cookbook\Helper\Filter\JSON\FixIngredientsFilter;
use OCA\Cookbook\Helper\Filter\JSON\FixInstructionsFilter;
use OCA\Cookbook\Helper\Filter\JSON\FixKeywordsFilter;
use OCA\Cookbook\Helper\Filter\JSON\FixNutritionFilter;
use OCA\Cookbook\Helper\Filter\JSON\FixRecipeYieldFilter;
use OCA\Cookbook\Helper\Filter\JSON\FixToolsFilter;
use OCA\Cookbook\Helper\Filter\JSON\FixUrlFilter;
use OCA\Cookbook\Helper\Filter\JSON\JSONFilter;
use OCA\Cookbook\Helper\Filter\JSON\RecipeIdTypeFilter;
use OCA\Cookbook\Helper\Filter\JSON\RecipeNameFilter;
use OCA\Cookbook\Helper\Filter\JSON\SchemaConformityFilter;
use PHPUnit\Framework\TestCase;

class JSONFilterTest extends TestCase {
	/** @var JSONFilter */
	private $dut;

	private $schemaConformityFilter;
	private $recipeNameFilter;
	private $recipeIdTypeFilter;
	private $extractImageUrlFilter;
	private $fixImageSchemeFilter;
	private $cleanCategoryFilter;
	private $fixRecipeYieldFilter;
	private $fixKeywordsFilter;
	private $fixToolsFilter;
	private $fixIngredientsFilter;
	private $fixInstructionsFilter;
	private $fixDescriptionFilter;
	private $fixUrlFilter;
	private $fixDurationsFilter;
	private $fixNutritionFilter;

	protected function setUp(): void {
		$this->schemaConformityFilter = $this->createStub(SchemaConformityFilter::class);
		$this->recipeNameFilter = $this->createStub(RecipeNameFilter::class);
		$this->recipeIdTypeFilter = $this->createStub(RecipeIdTypeFilter::class);
		$this->extractImageUrlFilter = $this->createStub(ExtractImageUrlFilter::class);
		$this->fixImageSchemeFilter = $this->createStub(FixImageSchemeFilter::class);
		$this->cleanCategoryFilter = $this->createStub(CleanCategoryFilter::class);
		$this->fixRecipeYieldFilter = $this->createStub(FixRecipeYieldFilter::class);
		$this->fixKeywordsFilter = $this->createStub(FixKeywordsFilter::class);
		$this->fixToolsFilter = $this->createStub(FixToolsFilter::class);
		$this->fixIngredientsFilter = $this->createStub(FixIngredientsFilter::class);
		$this->fixInstructionsFilter = $this->createStub(FixInstructionsFilter::class);
		$this->fixDescriptionFilter = $this->createStub(FixDescriptionFilter::class);
		$this->fixUrlFilter = $this->createStub(FixUrlFilter::class);
		$this->fixDurationsFilter = $this->createStub(FixDurationsFilter::class);
		$this->fixNutritionFilter = $this->createStub(FixNutritionFilter::class);


		$this->dut = new JSONFilter(
			$this->schemaConformityFilter,
			$this->recipeNameFilter,
			$this->recipeIdTypeFilter,
			$this->extractImageUrlFilter,
			$this->fixImageSchemeFilter,
			$this->cleanCategoryFilter,
			$this->fixRecipeYieldFilter,
			$this->fixKeywordsFilter,
			$this->fixToolsFilter,
			$this->fixIngredientsFilter,
			$this->fixInstructionsFilter,
			$this->fixDescriptionFilter,
			$this->fixUrlFilter,
			$this->fixDurationsFilter,
			$this->fixNutritionFilter
		);
		//
	}
	public function testSequence() {
		$idx = 0;
		$counter = 0;
		$closure = function () use (&$idx, &$counter) {
			$callback = function ($x) use ($idx, &$counter) {
				$this->assertEquals($idx, $counter);
				$counter += 1;
				return false;
			};
			$idx += 1;
			return $callback;
		};

		$this->schemaConformityFilter->method('apply')->willReturnCallback($closure());
		$this->recipeNameFilter->method('apply')->willReturnCallback($closure());
		$this->recipeIdTypeFilter->method('apply')->willReturnCallback($closure());
		$this->extractImageUrlFilter->method('apply')->willReturnCallback($closure());
		$this->fixImageSchemeFilter->method('apply')->willReturnCallback($closure());
		$this->cleanCategoryFilter->method('apply')->willReturnCallback($closure());
		$this->fixRecipeYieldFilter->method('apply')->willReturnCallback($closure());
		$this->fixKeywordsFilter->method('apply')->willReturnCallback($closure());
		$this->fixToolsFilter->method('apply')->willReturnCallback($closure());
		$this->fixIngredientsFilter->method('apply')->willReturnCallback($closure());
		$this->fixInstructionsFilter->method('apply')->willReturnCallback($closure());
		$this->fixDescriptionFilter->method('apply')->willReturnCallback($closure());
		$this->fixUrlFilter->method('apply')->willReturnCallback($closure());
		$this->fixDurationsFilter->method('apply')->willReturnCallback($closure());
		$this->fixNutritionFilter->method('apply')->willReturnCallback($closure());
		//
		$this->dut->apply([]);
	}
}
