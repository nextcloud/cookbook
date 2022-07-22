<?php

namespace OCA\Cookbook\tests\Unit\Helper\Filter\JSON;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\Stub;
use OCA\Cookbook\Helper\Filter\JSON\CleanCategoryFilter;
use OCA\Cookbook\Helper\TextCleanupHelper;
use PHPUnit\Framework\MockObject\MockObject;

class CleanCategoryFilterTest extends TestCase {
	/** @var CleanCategoryFilter */
	private $dut;

	/** @var MockObject|TextCleanupHelper */
	private $textCleaner;

	/** @var array */
	private $recipeStub;

	protected function setUp(): void {
		/** @var Stub|TextCleanupHelper $l */
		$this->textCleaner = $this->createStub(TextCleanupHelper::class);

		$this->dut = new CleanCategoryFilter($this->textCleaner);

		$this->recipeStub = [
			'name' => 'The recipe name',
			'id' => 1234,
		];
	}

	public function testNonExistent() {
		$recipe = $this->recipeStub;

		$this->textCleaner->method('cleanUp')->with('', true, true)->willReturnArgument(0);

		$ret = $this->dut->apply($recipe);

		$this->assertTrue($ret);
		$this->assertEquals('', $recipe['recipeCategory']);
		$this->recipeStub['recipeCategory'] = '';
		$this->assertEquals($this->recipeStub, $recipe);
	}

	public function dpString() {
		return [
			['Category1'],
			['Category with blanks'],
		];
	}

	/**
	 * @dataProvider dpString
	 * @param mixed $category
	 */
	public function testText($category) {
		$this->recipeStub['recipeCategory'] = $category;
		$recipe = $this->recipeStub;

		$this->textCleaner->expects($this->once())->method('cleanUp')
			->with($category, true, true)->willReturnArgument(0);

		$ret = $this->dut->apply($recipe);

		$this->assertFalse($ret);
		$this->assertEquals($this->recipeStub, $recipe);
	}

	public function dpArray() {
		return [
			[['Cat1','Cat2'], 'Cat1'],
			[[1, 'Cat'], ''],
		];
	}

	/** @dataProvider dpArray */
	public function testArray($inputCategories, $expectedCategory) {
		$recipe = $this->recipeStub;
		$recipe['recipeCategory'] = $inputCategories;

		$this->textCleaner->expects($this->once())->method('cleanUp')
			->with($expectedCategory, true, true)->willReturnArgument(0);

		$ret = $this->dut->apply($recipe);

		$this->recipeStub['recipeCategory'] = $expectedCategory;
		$this->assertTrue($ret);
		$this->assertEquals($this->recipeStub, $recipe);
	}

	public function dpCleanUp() {
		return [
			['Cat1', 'Cat1', 'Cat1', false],
			['Cat&amp;foo', 'Cat&amp;foo', 'Cat&foo', true],
			[['Cat1', 'Cat2'], 'Cat1', 'Cat1', true],
			[['Cat1&amp;Cat2', 'Cat3'], 'Cat1&amp;Cat2', 'Cat1&Cat2', true],
		];
	}

	/** @dataProvider dpCleanUp */
	public function testCleanup($originalCategory, $selectedCategory, $result, $changed) {
		$recipe = $this->recipeStub;
		$recipe['recipeCategory'] = $originalCategory;

		$this->textCleaner->method('cleanUp')->with($selectedCategory, true, true)->willReturn($result);

		$this->assertEquals($changed, $this->dut->apply($recipe));
		$this->recipeStub['recipeCategory'] = $result;
		$this->assertEquals($this->recipeStub, $recipe);
	}
}
