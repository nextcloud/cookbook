<?php

namespace OCA\Cookbook\tests\Integration\Helper\Filter\JSON;

use OCA\Cookbook\Helper\Filter\JSON\FixInstructionsFilter;
use OCA\Cookbook\Helper\TextCleanupHelper;
use OCA\Cookbook\Service\JsonService;
use OCP\IL10N;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers OCA\Cookbook\Helper\Filter\JSON\FixInstructionsFilter
 * @covers OCA\Cookbook\Exception\InvalidRecipeException
 */
class FixInstructionsFilterTest extends TestCase {
	/** @var FixInstructionsFilter */
	private $dut;

	/** @var TextCleanupHelper */
	private $textCleanupHelper;

	/** @var array */
	private $stub;

	protected function setUp(): void {
		/** @var Stub|IL10N $l */
		$l = $this->createStub(IL10N::class);
		$l->method('t')->willReturnArgument(0);

		/** @var LoggerInterface */
		$logger = $this->createStub(LoggerInterface::class);

		$this->textCleanupHelper = new TextCleanupHelper();
		$jsonService = new JsonService();

		$this->dut = new FixInstructionsFilter($l, $logger, $this->textCleanupHelper, $jsonService);

		$this->stub = [
			'name' => 'The name of the recipe',
			'id' => 1234,
		];
	}

	public function dpParseInstructions() {
		yield 'Instructions in multiple lines' => [
			"a\nb\rc\r\nd",
			['a','b','c','d'], true
		];
		yield 'Instructions with HTML Entities' => [
			"<p>a</p>\n<ul><li>b</li><li>c</li></ul><p>d</p>",
			['a','b','c','d'], true
		];
		yield 'Instructions with empty HTML Entities' => [
			"a\n<p></p><ul><li></li><li></li></ul><p></p>\nb\nc\nd",
			['a','b','c','d'], true
		];

		yield 'Instructions with Markdown' => [
			["a\nb\nc"],
			["a\nb\nc"], false
		];
		yield 'Instructions with untrimmed Markdown' => [
			["a\n  b \td\nc"],
			["a\n b d\nc"], true
		];
	}

	/** @dataProvider dpParseInstructions */
	public function testParseInstructions($originalInstructions, $expected, $changeExpected) {
		$recipe = $this->stub;
		$recipe['recipeInstructions'] = $originalInstructions;

		$changed = $this->dut->apply($recipe);

		$this->stub['recipeInstructions'] = $expected;
		$this->assertEquals($this->stub, $recipe);
		$this->assertEquals($changeExpected, $changed);
	}
}
