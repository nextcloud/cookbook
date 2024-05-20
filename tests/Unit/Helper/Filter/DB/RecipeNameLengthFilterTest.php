<?php

namespace OCA\Cookbook\tests\Unit\Helper\Filter\DB;

use OCA\Cookbook\Helper\Filter\DB\RecipeNameLengthFilter;
use OCP\Files\File;
use PHPUnit\Framework\TestCase;

/**
 * @covers OCA\Cookbook\Helper\Filter\DB\RecipeDatesFilter
 */
class RecipeNameLengthFilterTest extends TestCase {
	/** @var RecipeNameLengthFilter */
	private $dut;

	protected function setUp(): void {
		$this->dut = new RecipeNameLengthFilter();
	}

	public function dp() {
		yield ['abc', 'abc', false];
		yield ['äöü', 'äöü', false];

		$tenChars = "0123456789";
		$twentyChars = "$tenChars$tenChars";
		$fiftyChars = $twentyChars . $twentyChars . $tenChars;
		$hundredTwenty = $fiftyChars . $fiftyChars . $twentyChars;

		yield [$hundredTwenty, $hundredTwenty, false];
		yield [$hundredTwenty . '1234567', $hundredTwenty . '1234567', false];
		yield [$hundredTwenty . '12345678', $hundredTwenty . '12345678', false];
		yield [$hundredTwenty . '123456789', $hundredTwenty . '12345…', true];
		yield [$hundredTwenty . $hundredTwenty, $hundredTwenty . '01234…', true];

		yield [$hundredTwenty . '123456ä', $hundredTwenty . '123456ä', false];
		yield [$hundredTwenty . '123456ä1', $hundredTwenty . '12345…', true];
		yield [$hundredTwenty . '12345äö', $hundredTwenty . '12345…', true];
		yield [$hundredTwenty . '1234äö', $hundredTwenty . '1234äö', false];
	}

	/** @dataProvider dp */
	public function testFilter($inputName, $expectedName, $changed) {
		$input = $this->getStub($inputName);
		$expected = $this->getStub($expectedName);

		$file = $this->createStub(File::class);

		$ret = $this->dut->apply($input, $file);

		$this->assertSame($expected, $input);
		$this->assertEquals($changed, $ret);
	}

	private function getStub($name) {
		return [
			'id' => 123,
			'name' => $name,
		];
	}
}
