<?php

namespace  OCA\Cookbook\tests\Unit\Helper\HTMLParser;

use PHPUnit\Framework\TestCase;
use OCA\Cookbook\Exception\HtmlParsingException;
use OCA\Cookbook\Helper\HTMLParser\HttpMicrodataParser;
use OCP\IL10N;

/**
 * @coversDefaultClass \OCA\Cookbook\Helper\HTMLParser\HttpMicrodataParser
 * @covers \OCA\Cookbook\Helper\HTMLParser\AttributeNotFoundException
 * @covers ::<protected>
 * @covers ::<private>
 * @author christian
 *
 */
class HttpMicrodataParserTest extends TestCase {
	/**
	 * @covers ::__construct
	 * @covers \OCA\Cookbook\Helper\HTMLParser\AbstractHtmlParser
	 */
	public function testConstructor(): void {
		$l = $this->createStub(IL10N::class);

		$parser = new HttpMicrodataParser($l);

		$lProperty = new \ReflectionProperty(HttpMicrodataParser::class, 'l');
		$lProperty->setAccessible(true);
		$lSaved = $lProperty->getValue($parser);
		$this->assertSame($l, $lSaved);
	}

	public function dataProvider(): array {
		return [
			'caseA' => ['caseA.html',true,'caseA.json'],
			'caseB' => ['caseB.html',true,'caseB.json'],
			'caseC' => ['caseC.html',false,null],
			'caseD' => ['caseD.html',true,'caseD.json'],
			'caseE' => ['caseE.html',true,'caseE.json'],
		];
	}

	/**
	 * @dataProvider dataProvider
	 * @covers ::parse
	 */
	public function testHTMLFile($filename, $valid, $jsonFile): void {
		$l = $this->createStub(IL10N::class);

		$parser = new HttpMicrodataParser($l);

		$content = file_get_contents(__DIR__ . "/res_Microdata/$filename");

		$document = new \DOMDocument();
		$document->loadHTML($content);

		try {
			$res = $parser->parse($document);

			$jsonDest = file_get_contents(__DIR__ . "/res_Microdata/$jsonFile");
			$expected = json_decode($jsonDest, true);

			$this->assertTrue($valid);
			$this->assertEquals($expected, $res);
		} catch (HtmlParsingException $ex) {
			$this->assertFalse($valid);
		}
	}

	public function imageAttributes() {
		return [['image'], ['images'], ['thumbnail']];
	}

	/**
	 * @dataProvider imageAttributes
	 */
	public function testImageVariantsAsAttribute($attribute): void {
		$l = $this->createStub(IL10N::class);
		$parser = new HttpMicrodataParser($l);
		$content = file_get_contents(__DIR__ . "/res_Microdata/caseImageAttribute.html");
		$content = str_replace('%IMAGE_NAME%', $attribute, $content);

		$this->finishTest($parser, $content, 'caseImage.json');
	}

	/**
	 * @dataProvider imageAttributes
	 */
	public function testImageVariantsAsContent($attribute): void {
		$l = $this->createStub(IL10N::class);
		$parser = new HttpMicrodataParser($l);
		$content = file_get_contents(__DIR__ . "/res_Microdata/caseImageContent.html");
		$content = str_replace('%IMAGE_NAME%', $attribute, $content);

		$this->finishTest($parser, $content, 'caseImage.json');
	}

	public function ingredientVariantAttributes() {
		yield ['recipeIngredient'];
		yield ['ingredients'];
	}

	/**
	 * @dataProvider ingredientVariantAttributes
	 * @param string $attrtibute
	 */
	public function testIngredientVariants($attribute): void {
		$l = $this->createStub(IL10N::class);
		$parser = new HttpMicrodataParser($l);
		$content = file_get_contents(__DIR__ . "/res_Microdata/caseIngredient.html");
		$content = str_replace('%INGREDIENT_NAME%', $attribute, $content);

		$this->finishTest($parser, $content, 'caseIngredient.json');
	}

	public function instructionVariantAttributes() {
		yield ['recipeInstructions'];
		yield ['instructions'];
		yield ['steps'];
		yield ['guide'];
	}

	/**
	 * @dataProvider instructionVariantAttributes
	 */
	public function testInstructionVariants($attribute): void {
		$l = $this->createStub(IL10N::class);
		$parser = new HttpMicrodataParser($l);
		$content = file_get_contents(__DIR__ . "/res_Microdata/caseInstruction.html");
		$content = str_replace('%INSTRUCTION_NAME%', $attribute, $content);

		$this->finishTest($parser, $content, 'caseInstruction.json');
	}

	private function finishTest($parser, $content, $jsonFile): void {
		$document = new \DOMDocument();
		$document->loadHTML($content);

		try {
			$res = $parser->parse($document);

			$jsonDest = file_get_contents(__DIR__ . "/res_Microdata/$jsonFile");
			$expected = json_decode($jsonDest, true);

			$this->assertEquals($expected, $res);
		} catch (HtmlParsingException $ex) {
			$this->assertFalse(true);
		}
	}
}
