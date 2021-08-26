<?php

namespace OCA\Cookbook\tests\Unit\Helper\HTMLParser;

use PHPUnit\Framework\TestCase;
use OCA\Cookbook\Service\JsonService;
use OCP\IL10N;
use OCA\Cookbook\Helper\HTMLParser\HttpJsonLdParser;

class HttpJsonLdParserTest extends TestCase {
    
    
    public function dataProvider(): array {
        return [
            ['res/caseA.html', true, 'res/caseA.json']
        ];
    }
    
    /**
     * @dataProvider dataProvider
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
        } catch (\OCA\Cookbook\Exception\ImportException $ex) {
            $this->assertFalse($valid);
        }
    }
}
