<?php

namespace OCA\Cookbook\tests\Unit\Helper\Filter\JSON;

use OCA\Cookbook\Helper\Filter\JSON\FixSuitableForDietFilter;
use OCA\Cookbook\Helper\TextCleanupHelper;
use OCP\IL10N;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class FixSuitableForDietFilterTest extends TestCase {
	/** @var FixSuitableForDietFilter */
	private $dut;

	/** @var TextCleanupHelper|MockObject */
	private $textCleanupHelper;

	/** @var array */
	private $stub;

	protected function setUp(): void {
		parent::setUp();

		/** @var Stub|IL10N $l */
		$l = $this->createStub(IL10N::class);
		$l->method('t')->willReturnArgument(0);

		/** @var LoggerInterface */
		$logger = $this->createStub(LoggerInterface::class);

		$this->textCleanupHelper = $this->createMock(TextCleanupHelper::class);

		$this->dut = new FixSuitableForDietFilter($l, $logger, $this->textCleanupHelper);

		$this->stub = [
			'name' => 'The name of the recipe',
			'id' => 1234,
		];
	}

	public function testNonExisting(): void {
		$recipe = $this->stub;
		$this->assertTrue($this->dut->apply($recipe));

		$this->stub['suitableForDiet'] = '';
		$this->assertEquals($this->stub, $recipe);
	}

	public static function dp(): array {
		return [
			[12, '', true],
			['VeganDiet,VegetarianDiet', 'VeganDiet,VegetarianDiet', false],
			[['VeganDiet', 'VegetarianDiet'], 'VeganDiet,VegetarianDiet', true],
			['VeganDiet ,,  , GlutenFreeDiet  ', 'VeganDiet,GlutenFreeDiet', true],
			[['VeganDiet ', '', ' GlutenFreeDiet '], 'VeganDiet,GlutenFreeDiet', true],
			['VeganDiet,<i>  VegetarianDiet </i>', 'VeganDiet,VegetarianDiet', true],
			['', '', false],
			[[], '', true],
		];
	}

	/** @dataProvider dp */
	public function testApply($startVal, string $expectedVal, bool $changed): void {
		$recipe = $this->stub;
		$recipe['suitableForDiet'] = $startVal;

		$this->textCleanupHelper->method('cleanUp')->willReturnArgument(0);

		$ret = $this->dut->apply($recipe);

		$this->stub['suitableForDiet'] = $expectedVal;
		$this->assertEquals($changed, $ret);
		$this->assertEquals($this->stub, $recipe);
	}
}
