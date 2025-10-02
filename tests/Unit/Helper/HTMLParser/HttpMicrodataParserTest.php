<?php

namespace OCA\Cookbook\tests\Unit\Helper\HTMLParser;

use OCA\Cookbook\Exception\HtmlParsingException;
use OCA\Cookbook\Helper\HTMLParser\HttpMicrodataParser;
use OCP\IL10N;
use PHPUnit\Framework\TestCase;

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

			'caseIssue1209' => ['caseFix1209.html',true,'caseFix1209.json', true],
			'caseIssue1617a' => ['caseIssue1617a.html',true,'caseIssue1617a.json'],
			'caseIssue1617b' => ['caseIssue1617b.html',true,'caseIssue1617b.json'],
			'caseIssue1617c' => ['caseIssue1617c.html',true,'caseIssue1617c.json'],
			'casePR2711' => ['case2711.html', true, 'case2711.json'],
		];
	}

	/**
	 * @dataProvider dataProvider
	 * @covers ::parse
	 * @param mixed $filename
	 * @param mixed $valid
	 * @param mixed $jsonFile
	 * @param bool $skipped
	 */
	public function testHTMLFile($filename, $valid, $jsonFile, $skipped = false): void {
		if ($skipped) {
			$this->markTestIncomplete();
		}

		$l = $this->createStub(IL10N::class);

		$parser = new HttpMicrodataParser($l);

		$content = file_get_contents(__DIR__ . "/res_Microdata/$filename");

		$document = new \DOMDocument();
		$document->loadHTML($content);

		try {
			$res = $parser->parse($document, 'http://example.com');

			$jsonDest = file_get_contents(__DIR__ . "/res_Microdata/$jsonFile");
			$expected = json_decode($jsonDest, true);

			// $this->markTestSkipped();

			// print_r(json_encode($res));

			$this->assertTrue($valid, 'Expected HtmlParsingException as the input was invalid. No exception was thrown.');
			$this->assertEquals($expected, $res);
		} catch (HtmlParsingException $ex) {
			$this->assertFalse($valid, 'Not expected HtmlParsingException was triggered.');
		}
	}

	public function imageAttributes() {
		return [['image'], ['images'], ['thumbnail']];
	}

	/**
	 * @dataProvider imageAttributes
	 * @param mixed $attribute
	 */
	public function testImageVariantsAsAttribute($attribute): void {
		$l = $this->createStub(IL10N::class);
		$parser = new HttpMicrodataParser($l);
		$content = file_get_contents(__DIR__ . '/res_Microdata/caseImageAttribute.html');
		$content = str_replace('%IMAGE_NAME%', $attribute, $content);

		$this->finishTest($parser, $content, 'caseImage.json');
	}

	/**
	 * @dataProvider imageAttributes
	 * @param mixed $attribute
	 */
	public function testImageVariantsAsContent($attribute): void {
		$l = $this->createStub(IL10N::class);
		$parser = new HttpMicrodataParser($l);
		$content = file_get_contents(__DIR__ . '/res_Microdata/caseImageContent.html');
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
	 * @param mixed $attribute
	 */
	public function testIngredientVariants($attribute): void {
		$l = $this->createStub(IL10N::class);
		$parser = new HttpMicrodataParser($l);
		$content = file_get_contents(__DIR__ . '/res_Microdata/caseIngredient.html');
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
	 * @param mixed $attribute
	 */
	public function testInstructionVariants($attribute): void {
		$l = $this->createStub(IL10N::class);
		$parser = new HttpMicrodataParser($l);
		$content = file_get_contents(__DIR__ . '/res_Microdata/caseInstruction.html');
		$content = str_replace('%INSTRUCTION_NAME%', $attribute, $content);

		$this->finishTest($parser, $content, 'caseInstruction.json');
	}

	private function finishTest($parser, $content, $jsonFile): void {
		$document = new \DOMDocument();
		$document->loadHTML($content);

		try {
			$res = $parser->parse($document, 'http://exmapl.com');

			$jsonDest = file_get_contents(__DIR__ . "/res_Microdata/$jsonFile");
			$expected = json_decode($jsonDest, true);

			$this->assertEquals($expected, $res);
		} catch (HtmlParsingException $ex) {
			$this->assertFalse(true);
		}
	}
}
