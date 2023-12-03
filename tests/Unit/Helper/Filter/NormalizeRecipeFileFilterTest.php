<?php

namespace OCA\Cookbook\tests\Unit\Helper\Filter;

use OCA\Cookbook\Helper\Filter\DB\NormalizeRecipeFileFilter;
use OCA\Cookbook\Helper\Filter\DB\RecipeDatesFilter;
use OCP\Files\File;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers OCA\Cookbook\Helper\Filter\NormalizeRecipeFileFilter
 */
class NormalizeRecipeFileFilterTest extends TestCase {
	/** @var MockObject|RecipeDatesFilter */
	private $datesFilter;

	/** @var NormalizeRecipeFileFilter */
	private $dut;

	protected function setUp(): void {
		$this->datesFilter = $this->createMock(RecipeDatesFilter::class);
		$this->dut = new NormalizeRecipeFileFilter($this->datesFilter);
	}

	public function dp() {
		yield [false, false, false];
		yield [false, true, false];
		yield [false, false, false];
		yield [true, true, true];
	}

	/** @dataProvider dp */
	public function testFilter($updateRequested, $changedDates, $shouldWrite) {
		$recipe = ['name' => 'The recipe'];

		$recipeA = $recipe;
		$recipeA['version'] = 'from RecipeDatesFilter';

		/** @var MockObject|File $recipeFile */
		$recipeFile = $this->createMock(File::class);

		if ($shouldWrite) {
			$recipeFile->expects($this->once())->method('putContent')->with(json_encode($recipeA));
			$recipeFile->expects($this->once())->method('touch');
		} else {
			$recipeFile->expects($this->never())->method('putContent');
			$recipeFile->expects($this->never())->method('touch');
		}

		$this->datesFilter->method('apply')->with($recipe, $recipeFile)
			->willReturnCallback(function (&$json, $file) use ($changedDates, $recipeA) {
				$json = $recipeA;
				return $changedDates;
			});

		$ret = $this->dut->filter($recipe, $recipeFile, $updateRequested);

		$this->assertEquals($recipeA, $ret);
	}
}
