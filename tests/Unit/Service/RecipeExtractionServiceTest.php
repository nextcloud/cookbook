<?php

namespace OCA\Cookbook\tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use OCA\Cookbook\Helper\HTMLParser\HttpJsonLdParser;
use OCA\Cookbook\Helper\HTMLParser\HttpMicrodataParser;
use OCA\Cookbook\Exception\HtmlParsingException;
use OCP\IL10N;
use OCA\Cookbook\Service\RecipeExtractionService;

class RecipeExtractionServiceTest extends TestCase {

    /**
     * @var IL10N
     */
    private $l;
    
    protected function setUp(): void
    {
        // TODO Auto-generated method stub
        $this->l = $this->createStub(IL10N::class);
    }

    /**
     * @dataProvider dataProvider
     * @param bool $jsonSuccess
     * @param bool $microdataSuccess
     * @param bool $exceptionExpected
     */
    public function testParsingDelegation($jsonSuccess, $microdataSuccess, $exceptionExpected): void {
        $jsonParser = $this->createMock(HttpJsonLdParser::class);
        $microdataParser = $this->createMock(HttpMicrodataParser::class);
        
        $document = $this->createStub(\DOMDocument::class);
        $expectedObject = [new \stdClass()];

        if ($jsonSuccess) {
            $jsonParser->expects($this->once())
                ->method('parse')
                ->with($document)
                ->willReturn($expectedObject);
            
            $microdataParser->expects($this->never())->method('parse');
        } else {
            $jsonParser->expects($this->once())
                ->method('parse')
                ->with($document)
                ->willThrowException(new HtmlParsingException());
            
            if ($microdataSuccess) {
                $microdataParser->expects($this->once())
                    ->method('parse')
                    ->with($document)
                    ->willReturn($expectedObject);
            } else {
                $microdataParser->expects($this->once())
                    ->method('parse')
                    ->with($document)
                    ->willThrowException(new HtmlParsingException());
            }
        }
        
        $sut = new RecipeExtractionService($jsonParser, $microdataParser, $this->l);
        
        try {
            $ret = $sut->parse($document);
            
            $this->assertEquals($expectedObject, $ret);
        } catch (HtmlParsingException $ex) {
            $this->assertTrue($exceptionExpected);
        }
        
    }
    
    public function dataProvider() {
        return [
            [true, false, false],
            [false, true, false],
            [false, false, true],
        ];
    }
    
}
