<?php

namespace OCA\Cookbook\tests\Unit\Helper\Filter\JSON;

use OCA\Cookbook\Exception\InvalidTimestampException;
use OCA\Cookbook\Helper\Filter\JSON\FixTimestampsFilter;
use OCA\Cookbook\Helper\TimestampHelper;
use OCP\IL10N;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class FixTimestampsFilterTest extends TestCase {
	/** @var FixTimestampsFilter */
	private $dut;

	/** @var TimestampHelper|MockObject */
	private $timestampHelper;

	/** @var array */
	private $stub;

	protected function setUp(): void {
		/** @var Stub|IL10N */
		$l = $this->createStub(IL10N::class);
		$l->method('t')->willReturnArgument(0);
		$logger = $this->createStub(LoggerInterface::class);

		$this->timestampHelper = $this->createMock(TimestampHelper::class);

		$this->dut = new FixTimestampsFilter($l, $logger, $this->timestampHelper);

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
			$this->stub['dateCreated'] = '2000-01-01T01:01:00+00:00';
		}
		if ($cookExists) {
			$this->stub['dateModified'] = '2001-01-01T01:01:00+01:00';
		}
		if ($totalExists) {
			$this->stub['datePublished'] = '2002-01-01T01:01:00,123-01:30';
		}

		$recipe = $this->stub;
		$this->timestampHelper->method('parseTimestamp')->willReturnArgument(0);

		$this->assertTrue($this->dut->apply($recipe));

		$this->stub['dateCreated'] ??= null;
		$this->stub['dateModified'] ??= null;
		$this->stub['datePublished'] ??= null;

		$this->assertEquals($this->stub, $recipe);
	}

	public function dpSuccess() {
		return [
			['2000-01-01T01:01:00+00:00', '2001-01-01T01:01:00+01:00', '2002-01-01T01:01:00-01:30',
				'2000-01-01T01:01:00+00:00', '2001-01-01T01:01:00+01:00', '2002-01-01T01:01:00-01:30', false],

			['2000-01-01T01:01:00Z', '2001-01-01T01:01:00+01:00', '2002-01-01T01:01:00,123-01:30',
				'2000-01-01T01:01:00+00:00', '2001-01-01T01:01:00+01:00', '2002-01-01T01:01:00-01:30', true],
			['2001-01-01T01:01:00+01:00', '2000-01-01T01:01:00Z', '2002-01-01T01:01:00,123-01:30',
				'2001-01-01T01:01:00+01:00', '2000-01-01T01:01:00+00:00', '2002-01-01T01:01:00-01:30', true],
			['2001-01-01T01:01:00+01:00', '2002-01-01T01:01:00,123-01:30', '2000-01-01T01:01:00Z',
				'2001-01-01T01:01:00+01:00', '2002-01-01T01:01:00-01:30', '2000-01-01T01:01:00+00:00', true],
		];
	}

	/** @dataProvider dpSuccess */
	public function testSuccess(
		$originalCreated, $originalModified, $originalPublished,
		$expectedCreated, $expectedModified, $expectedPublished,
		$expectedChange
	) {
		$this->timestampHelper->method('parseTimestamp')->willReturnMap([
			['2000-01-01T01:01:00+00:00', '2000-01-01T01:01:00+00:00'],
			['2001-01-01T01:01:00+01:00', '2001-01-01T01:01:00+01:00'],
			['2002-01-01T01:01:00-01:30', '2002-01-01T01:01:00-01:30'],
			['2002-01-01T01:01:00,123-01:30', '2002-01-01T01:01:00-01:30'],
			['2000-01-01T01:01:00Z', '2000-01-01T01:01:00+00:00'],
		]);

		$this->stub['dateCreated'] = $originalCreated;
		$this->stub['dateModified'] = $originalModified;
		$this->stub['datePublished'] = $originalPublished;

		$recipe = $this->stub;

		$this->assertEquals($expectedChange, $this->dut->apply($recipe));

		$this->stub['dateCreated'] = $expectedCreated;
		$this->stub['dateModified'] = $expectedModified;
		$this->stub['datePublished'] = $expectedPublished;

		$this->assertEquals($this->stub, $recipe);
	}

	public function dpExceptions() {
		return [
			['invalid', '2000-01-02T01:01:00Z', '2000-01-03T01:01:00Z', null, '2000-01-02T01:01:00Z', '2000-01-03T01:01:00Z'],
			['2000-01-01T01:01:00Z', 'invalid', '2000-01-03T01:01:00Z', '2000-01-01T01:01:00Z', null, '2000-01-03T01:01:00Z'],
			['2000-01-01T01:01:00Z', '2000-01-02T01:01:00Z', 'invalid', '2000-01-01T01:01:00Z', '2000-01-02T01:01:00Z', null],
		];
	}

	/** @dataProvider dpExceptions */
	public function testExceptions(
		$dateCreated, $dateModified, $datePublished,
		$expectedCreated, $expectedModified, $expectedPublished
	) {
		$this->timestampHelper->method('parseTimestamp')->willReturnCallback(function ($x) {
			if ($x === 'invalid') {
				throw new InvalidTimestampException();
			} else {
				return $x;
			}
		});

		$this->stub['dateCreated'] = $dateCreated;
		$this->stub['dateModified'] = $dateModified;
		$this->stub['datePublished'] = $datePublished;

		$recipe = $this->stub;

		$this->assertTrue($this->dut->apply($recipe));

		$this->stub['dateCreated'] = $expectedCreated;
		$this->stub['dateModified'] = $expectedModified;
		$this->stub['datePublished'] = $expectedPublished;

		$this->assertEquals($this->stub, $recipe);
	}
}
