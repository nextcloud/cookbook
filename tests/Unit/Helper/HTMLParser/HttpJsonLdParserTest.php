<?php

namespace OCA\Cookbook\tests\Unit\Helper\HTMLParser;

use PHPUnit\Framework\TestCase;
use OCA\Cookbook\Service\JsonService;
use OCP\IL10N;
use OCA\Cookbook\Helper\HTMLParser\HttpJsonLdParser;
use OCA\Cookbook\Exception\HtmlParsingException;

/**
 * @coversDefaultClass \OCA\Cookbook\Helper\HTMLParser\HttpJsonLdParser
 * @covers ::<protected>
 * @covers ::<private>
 * @author christian
 *
 */
class HttpJsonLdParserTest extends TestCase {
    
    public function dataProvider(): array {
        return [
            'caseA' => ['res/caseA.html', true, 'res/caseA.json'],
            'caseB' => ['res/caseB.html', true, 'res/caseB.json'],
            'caseC' => ['res/caseC.html', false, null],
            'caseD' => ['res/caseD.html', false, null],
            'caseE' => ['res/caseE.html', false, null],
            'caseF' => ['res/caseF.html', true, 'res/caseF.json'],
            'caseG' => ['res/caseG.html', true, 'res/caseG.json'],
            'caseH' => ['res/caseH.html', true, 'res/caseH.json'],
        ];
    }
    
    /**
     * @covers ::__construct
     * @covers \OCA\Cookbook\Helper\HTMLParser\AbstractHtmlParser::__construct
     */
    public function testConstructor(): void {
        $jsonService = $this->createStub(JsonService::class);
        $l = $this->createStub(IL10N::class);
        
        $parser = new HttpJsonLdParser($l, $jsonService);
        
        $lProperty = new \ReflectionProperty(HttpJsonLdParser::class, 'l');
        $lProperty->setAccessible(true);
        $lSaved = $lProperty->getValue($parser);
        $this->assertSame($l, $lSaved);
    }
    
    /**
     * @dataProvider dataProvider
     * @covers ::parse
     * @param string $file
     */
    public function testHTMLFile($file, $valid, $jsonFile): void {
        $jsonService = new JsonService();
        $l = $this->createStub(IL10N::class);
        
        $parser = new HttpJsonLdParser($l, $jsonService);
        
        $content = file_get_contents(__DIR__ . "/$file");
        
        $document = new \DOMDocument();
        $document->loadHTML($content);
        
        try {
            $res = $parser->parse($document);
            
            $jsonDest = file_get_contents(__DIR__ . "/$jsonFile");
            $expected = json_decode($jsonDest, true);
            
            $this->assertTrue($valid);
            $this->assertEquals($expected, $res);
        } catch (HtmlParsingException $ex) {
            $this->assertFalse($valid);
        }
    }
}
