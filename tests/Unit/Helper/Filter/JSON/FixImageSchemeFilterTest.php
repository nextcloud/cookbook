<?php

namespace OCA\Cookbook\tests\Unit\Helper\Filter\JSON;

use OCA\Cookbook\Exception\InvalidRecipeException;
use OCA\Cookbook\Helper\Filter\JSON\FixImageSchemeFilter;
use OCP\IL10N;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class FixImageSchemeFilterTest extends TestCase {
	/** @var FixImageSchemeFilter */
	private $dut;

	protected function setUp(): void {
		/** @var Stub|IL10N $l */
		$l = $this->createStub(IL10N::class);
		$l->method('t')->willReturnArgument(0);

		$this->dut = new FixImageSchemeFilter($l);
	}

	public function dp() {
		return [
			['http://foo', '', 'http://foo', false],
			['https://foo', '', 'https://foo', false],
			['//foo', 'http://example.com', 'http://foo', true],
			['//foo', 'https://example.com', 'https://foo', true],
			['foo//sdf//sd/x', '', 'foo//sdf//sd/x', false],
			['', 'invalid_url', '', false],
		];
	}

	/**
	 * @dataProvider dp
	 * @param mixed $oldImage
	 * @param mixed $url
	 * @param mixed $newImage
	 * @param mixed $isChanged
	 */
	public function testFilter($oldImage, $url, $newImage, $isChanged) {
		$recipeStub = [
			'name' => 'The name',
			'id' => 1234,
			'url' => $url,
		];

		$recipe = $recipeStub;
		$recipe['image'] = $oldImage;

		$this->assertEquals($isChanged, $this->dut->apply($recipe));

		$recipeExpected = $recipeStub;
		$recipeExpected['image'] = $newImage;

		$this->assertEquals($recipeExpected, $recipe);
	}

	public function dpFailedUrl() {
		return [
			['//recipe.html'],
			['/recipe.html'],
			['foo/http://recipe.html'],
			[''],
			[null],
		];
	}

	/**
	 * @dataProvider dpFailedUrl
	 * @param mixed $url
	 */
	public function testInvalidUrl($url) {
		$recipeStub = [
			'url' => $url,
			'image' => '//image.jpg',
		];

		$recipe = $recipeStub;

		$this->expectException(InvalidRecipeException::class);
		$this->dut->apply($recipe);
	}
}
