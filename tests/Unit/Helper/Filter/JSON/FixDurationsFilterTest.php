<?php

namespace OCA\Cookbook\tests\Unit\Helper\Filter\JSON;

use OCA\Cookbook\Exception\InvalidDurationException;
use OCA\Cookbook\Helper\Filter\JSON\FixDurationsFilter;
use OCA\Cookbook\Helper\ISO8601DurationHelper;
use OCP\IL10N;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class FixDurationsFilterTest extends TestCase {
	/** @var FixDurationsFilter */
	private $dut;

	/** @var ISO8601DurationHelper|MockObject */
	private $iso8601DurationHelper;

	/** @var array */
	private $stub;

	protected function setUp(): void {
		/** @var Stub|IL10N */
		$l = $this->createStub(IL10N::class);
		$l->method('t')->willReturnArgument(0);
		$logger = $this->createStub(LoggerInterface::class);

		$this->iso8601DurationHelper = $this->createMock(ISO8601DurationHelper::class);

		$this->dut = new FixDurationsFilter($l, $logger, $this->iso8601DurationHelper);

		$this->stub = [
			'name' => 'The name of the recipe',
			'id' => 1234,
		];
	}

	public function dpNonExisting() {
		return [
			[false, false, false],
			[true, false, false],
			[false, true, false],
			[false, false, true],
		];
	}

	/** @dataProvider dpNonExisting */
	public function testNonExisting($preExists, $cookExists, $totalExists) {
		if ($preExists) {
			$this->stub['prepTime'] = '0:10';
		}
		if ($cookExists) {
			$this->stub['cookTime'] = '0:20';
		}
		if ($totalExists) {
			$this->stub['totalTime'] = '0:30';
		}

		$recipe = $this->stub;
		$this->iso8601DurationHelper->method('parseDuration')->willReturnArgument(0);

		$this->assertTrue($this->dut->apply($recipe));

		$this->stub['prepTime'] ??= null;
		$this->stub['cookTime'] ??= null;
		$this->stub['totalTime'] ??= null;

		$this->assertEquals($this->stub, $recipe);
	}

	public function dpSuccess() {
		return [
			['PT0H10M', 'PT0H20M', 'PT0H30M', 'PT0H10M', 'PT0H20M', 'PT0H30M', false],
			['0:10', 'PT0H20M', 'PT0H30M', 'PT0H10M', 'PT0H20M', 'PT0H30M', true],
			['PT0H10M', '0:20', 'PT0H30M', 'PT0H10M', 'PT0H20M', 'PT0H30M', true],
			['PT0H10M', 'PT0H20M', '0:30', 'PT0H10M', 'PT0H20M', 'PT0H30M', true],
			['0:10', '0:20', '0:30', 'PT0H10M', 'PT0H20M', 'PT0H30M', true],
		];
	}

	/** @dataProvider dpSuccess */
	public function testSuccess(
		$originalPrep, $originalCook, $originalTotal,
		$expectedPrep, $expectedCook, $expectedTotal,
		$expectedChange
	) {
		$this->iso8601DurationHelper->method('parseDuration')->willReturnMap([
			['0:10', 'PT0H10M'],
			['0:20', 'PT0H20M'],
			['0:30', 'PT0H30M'],
			['PT0H10M', 'PT0H10M'],
			['PT0H20M', 'PT0H20M'],
			['PT0H30M', 'PT0H30M'],
		]);

		$this->stub['prepTime'] = $originalPrep;
		$this->stub['cookTime'] = $originalCook;
		$this->stub['totalTime'] = $originalTotal;

		$recipe = $this->stub;

		$this->assertEquals($expectedChange, $this->dut->apply($recipe));

		$this->stub['prepTime'] = $expectedPrep;
		$this->stub['cookTime'] = $expectedCook;
		$this->stub['totalTime'] = $expectedTotal;

		$this->assertEquals($this->stub, $recipe);
	}

	public function dpExceptions() {
		return [
			['invalid', '0:20', '0:30', null, '0:20', '0:30'],
			['0:10', 'invalid', '0:30', '0:10', null, '0:30'],
			['0:10', '0:20', 'invalid', '0:10', '0:20', null],
		];
	}

	/** @dataProvider dpExceptions */
	public function testExceptions(
		$prepTime, $cookTime, $totalTime,
		$expectedPrep, $expectedCook, $expectedTotal
	) {
		$this->iso8601DurationHelper->method('parseDuration')->willReturnCallback(function ($x) {
			if ($x === 'invalid') {
				throw new InvalidDurationException();
			} else {
				return $x;
			}
		});

		$this->stub['prepTime'] = $prepTime;
		$this->stub['cookTime'] = $cookTime;
		$this->stub['totalTime'] = $totalTime;

		$recipe = $this->stub;

		$this->assertTrue($this->dut->apply($recipe));

		$this->stub['prepTime'] = $expectedPrep;
		$this->stub['cookTime'] = $expectedCook;
		$this->stub['totalTime'] = $expectedTotal;

		$this->assertEquals($this->stub, $recipe);
	}
}
