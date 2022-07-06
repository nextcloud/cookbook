<?php

namespace OCA\Cookbook\tests\Unit\Helper\Filter\DB;

use OCA\Cookbook\Helper\Filter\DB\RecipeDatesFilter;
use OCP\Files\File;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

/**
 * @covers OCA\Cookbook\Helper\Filter\DB\RecipeDatesFilter
 */
class RecipeDatesFilterTest extends TestCase {
	/** @var RecipeDatesFilter */
	private $dut;

	protected function setUp(): void {
		$this->dut = new RecipeDatesFilter();
	}

	public function dpFromJson() {
		yield ['2022-07-06T11:08:54', null, '2022-07-06T11:08:54', null, false];
		yield [1657098534, 0, '2022-07-06T09:08:54+0000', null, true];
		yield [1657098534, 1657098540, '2022-07-06T09:08:54+0000', '2022-07-06T09:09:00+0000', true];
		yield [null, 1657098540, '2022-07-06T09:09:00+0000', '2022-07-06T09:09:00+0000', true];
		yield [0, 1657098540, '2022-07-06T09:09:00+0000', '2022-07-06T09:09:00+0000', true];
	}

	/**
	 * @dataProvider dpFromJson
	 * @param mixed $created
	 * @param mixed $modified
	 * @param mixed $expectedCreation
	 * @param mixed $expectedModification
	 * @param mixed $updated
	 */
	public function testFilterFromJson($created, $modified, $expectedCreation, $expectedModification, $updated) {
		$recipe = [
			'name' => 'my Recipe',
			'dateCreated' => $created,
			'dateModified' => $modified
		];
		$copy = $recipe;

		$file = $this->createStub(File::class);

		$ret = $this->dut->apply($recipe, $file);

		$this->assertEquals($updated, $ret, 'Reporting of modification status');
		$this->assertEquals($expectedCreation, $recipe['dateCreated'], 'Wrong creation date');
		$this->assertEquals($expectedModification, $recipe['dateModified'], 'Wrong modification date');

		unset($recipe['dateCreated']);
		unset($recipe['dateModified']);
		unset($copy['dateCreated']);
		unset($copy['dateModified']);

		$this->assertEquals($copy, $recipe, 'Other entries must not change.');
	}

	public function dpDateFormats() {
		return [
			['2022-07-05'],
			['2022-07-05T15:30:00'],
			['2022-07-05T15:30:00.123'],
			['2022-07-05T15:30:00z'],
			['2022-07-05T15:30:00Z'],
			['2022-07-05T15:30:00 UTC'],
			['2022-07-05T15:30:00+0100'],
			['2022-07-05T15:30:00-0100'],
			['2022-07-05T15:30:00+01:00'],
			['2022-07-05T15:30:00-01:00'],
			['2022-07-05T15:30:00+01'],
			['2022-07-05T15:30:00-01'],
			['2022-07-05T15:30:00.123+01:00'],
		];
	}

	/**
	 * @dataProvider dpDateFormats
	 * @param mixed $date
	 */
	public function testDateFormats($date) {
		$recipe = [
			'name' => 'my Recipe',
			'dateCreated' => $date,
			'dateModified' => $date
		];
		$copy = $recipe;

		$file = $this->createStub(File::class);

		$ret = $this->dut->apply($recipe, $file);

		$this->assertFalse($ret, 'Reporting of modification status');
		$this->assertEquals($date, $recipe['dateCreated'], 'Wrong creation date');
		$this->assertEquals($date, $recipe['dateModified'], 'Wrong modification date');

		unset($recipe['dateCreated']);
		unset($recipe['dateModified']);
		unset($copy['dateCreated']);
		unset($copy['dateModified']);

		$this->assertEquals($copy, $recipe, 'Other entries must not change.');
	}

	public function dpFromFile() {
		yield ['2022-07-06T09:08:54+0000', false, false, 1657098534, 1657098535, 1657098536];
		yield ['2022-07-06T09:08:55+0000', false, false, 0, 1657098535, 1657098536];
		yield ['2022-07-06T09:08:56+0000', false, false, 0, 0, 1657098536];
		yield ['2022-07-06T09:08:54+0000', true, true, 1657098534, 1657098535, 1657098536];
	}

	/**
	 * @dataProvider dpFromFile
	 * @param mixed $creation
	 * @param mixed $creationPresent
	 * @param mixed $modificationPresent
	 * @param mixed $creationTime
	 * @param mixed $uploadTime
	 * @param mixed $mTime
	 */
	public function testFilterFromFile($creation, $creationPresent, $modificationPresent, $creationTime, $uploadTime, $mTime) {
		$recipe = [
			'name' => 'my Recipe',
		];
		if ($creationPresent) {
			$recipe['dateCreated'] = null;
		}
		if ($modificationPresent) {
			$recipe['dateModified'] = null;
		}

		$copy = $recipe;

		/** @var Stub|File */
		$file = $this->createStub(File::class);
		$file->method('getCreationTime')->willReturn($creationTime);
		$file->method('getUploadTime')->willReturn($uploadTime);
		$file->method('getMTime')->willReturn($mTime);

		$ret = $this->dut->apply($recipe, $file);

		$this->assertTrue($ret, 'Reporting of modification status');

		$this->assertEquals($creation, $recipe['dateCreated'], 'Wrong creation date');
		$this->assertNull($recipe['dateModified'], 'Wrong modification date');

		unset($recipe['dateCreated']);
		unset($recipe['dateModified']);
		unset($copy['dateCreated']);
		unset($copy['dateModified']);

		$this->assertEquals($copy, $recipe, 'Other entries must not change.');
	}
}
