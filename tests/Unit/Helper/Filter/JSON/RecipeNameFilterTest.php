<?php

namespace OCA\Cookbook\tests\Unit\Helper\Filter\JSON;

use OCA\Cookbook\Helper\Filter\JSON\RecipeNameFilter;
use OCA\Cookbook\Helper\Filter\JSON\SchemaConformityFilter;
use OCA\Cookbook\Helper\TextCleanupHelper;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class RecipeNameFilterTest extends TestCase {
	/** @var SchemaConformityFilter */
	private $dut;

	/** @var TextCleanupHelper|Stub */
	private $textCleaner;

	protected function setUp(): void {
		$this->textCleaner = $this->createStub(TextCleanupHelper::class);

		$this->dut = new RecipeNameFilter($this->textCleaner);
	}

	public function dpClean() {
		return [
			['The text', 'The cleaned text', 'The cleaned text', true],
			['The text', 'The text', 'The text', false],
			// 70 times 5 chars (&amp;) are 350, thus more than 256
			[str_repeat('&amp;', 70), str_repeat('&', 70), str_repeat('&', 70), true],
		];
	}

	/**
	 * @dataProvider dpClean
	 * @param mixed $oldName
	 * @param mixed $retCleaning
	 * @param mixed $newName
	 * @param mixed $isChanged
	 */
	public function testCleanFilter($oldName, $retCleaning, $newName, $isChanged) {
		$recipeStub = [
			'id' => 1234,
			'description' => 'The description',
		];

		$recipe = $recipeStub;
		$recipe['name'] = $oldName;

		$this->textCleaner->method('cleanUp')->willReturnMap([
			[$oldName, true, true, $retCleaning],
		]);

		$this->assertEquals($isChanged, $this->dut->apply($recipe));

		$recipeExpected = $recipeStub;
		$recipeExpected['name'] = $newName;

		$this->assertEquals($recipeExpected, $recipe);
	}

	public function dpLongNames() {
		$tenChars = 'abcdefghij';
		$fiftyChars = str_repeat($tenChars, 5);
		$twoHundredChars = str_repeat($fiftyChars, 4);

		return [
			'10 chars' => [$tenChars, $tenChars, false],
			'200 chars' => [$twoHundredChars, $twoHundredChars, false],
			'255 chars' => ["$twoHundredChars{$fiftyChars}12345",   "$twoHundredChars{$fiftyChars}12345", false],
			'256 chars' => ["$twoHundredChars{$fiftyChars}123456",  "$twoHundredChars{$fiftyChars}123456", false],
			'257 chars' => ["$twoHundredChars{$fiftyChars}1234567", "$twoHundredChars{$fiftyChars}12345…", true],
			'300 chars' => ["$twoHundredChars{$fiftyChars}$fiftyChars", "$twoHundredChars{$fiftyChars}abcde…", true],
		];
	}

	/**
	 * @dataProvider dpLongNames
	 * @param mixed $oldName
	 * @param mixed $newName
	 * @param mixed $changed
	 */
	public function testLongNames($oldName, $newName, $changed) {
		$recipe = [
			'name' => $oldName,
			'id' => 1234,
			'description' => 'The description'
		];

		$this->textCleaner->method('cleanUp')->willReturnArgument(0);

		$this->assertEquals($changed, $this->dut->apply($recipe));
		$this->assertEquals($newName, $recipe['name']);
	}
}
