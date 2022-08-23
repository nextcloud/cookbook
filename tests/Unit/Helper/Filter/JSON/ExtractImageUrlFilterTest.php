<?php

namespace OCA\Cookbook\tests\Unit\Helper\Filter\JSON;

use OCA\Cookbook\Helper\Filter\JSON\ExtractImageUrlFilter;
use OCA\Cookbook\Helper\Filter\JSON\SchemaConformityFilter;
use OCP\IL10N;
use OCP\ILogger;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class ExtractImageUrlFilterTest extends TestCase {
	/** @var SchemaConformityFilter */
	private $dut;

	/** @var array */
	private $recipeStub;

	protected function setUp(): void {
		/** @var IL10N|Stub */
		$l = $this->createStub(IL10N::class);
		$l->method('t')->willReturnArgument(0);

		$logger = $this->createStub(ILogger::class);

		$this->dut = new ExtractImageUrlFilter($l, $logger);

		$this->recipeStub = [
			'name' => 'The name',
			'id' => 1234,
		];
	}

	public function testNonExistingImage() {
		$recipe = $this->recipeStub;
		$this->assertTrue($this->dut->apply($recipe));
		$this->recipeStub['image'] = '';
		$this->assertEquals($this->recipeStub, $recipe);
	}

	public function dpImage() {
		yield ['http://example.com/image.jpg', 'http://example.com/image.jpg', false];
		yield [30, '', true];
		yield [['url' => 'http://example.com/image.jpg'], 'http://example.com/image.jpg', true];
		yield [[
			['foo', 'url' => null],
			'http://example.com/image.jpg',
			['test' => 'array']
		], 'http://example.com/image.jpg', true];
		yield [[
			['foo'],
			['http://example.com/image.jpg'],
			['test' => 'array']
		], '', true];
		yield [[
			['foo'],
			'http://example.com/image1.jpg',
			'http://example.com/image2.jpg',
			['test' => 'array']
		], 'http://example.com/image2.jpg', true];
		yield [[
			['foo'],
			'http://example.com/1/2/image1.jpg',
			'http://example.com/1/2/image3.jpg',
			['test' => 'array']
		], 'http://example.com/1/2/image3.jpg', true];
		yield [[
			['foo'],
			['url' => 'http://example.com/1/2/image1.jpg'],
			['url' => 'http://example.com/1/2/image3.jpg'],
			['test' => 'array']
		], 'http://example.com/1/2/image3.jpg', true];
		yield [[
			['foo'],
			'http://example.com/imageA.jpg',
			'http://example.com/imageB.jpg',
			['test' => 'array']
		], 'http://example.com/imageA.jpg', true];
	}

	/**
	 * @dataProvider dpImage
	 * @param mixed $image
	 * @param mixed $expectedImage
	 * @param mixed $isChanged
	 */
	public function testImage($image, $expectedImage, $isChanged) {
		$recipe = $this->recipeStub;
		$recipe['image'] = $image;

		$this->assertEquals($isChanged, $this->dut->apply($recipe));

		$this->recipeStub['image'] = $expectedImage;
		$this->assertEquals($this->recipeStub, $recipe);
	}
}
