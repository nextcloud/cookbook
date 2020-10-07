<?php
namespace OCA\Cookbook\Tests\Unit\Controller;

use OC\URLGenerator;
use OCA\Cookbook\Service\RecipeService;
use OCP\AppFramework\Http\TemplateResponse;
use OCA\Cookbook\Controller\PageController;
use OCP\Files\IRootFolder;
use OCP\IConfig;
use OCP\IDBConnection;
use OCP\IRequest;
use Test\TestCase;

class PageControllerTest extends TestCase
{
    private $controller;
    private $mockedRecipeService;

    public function setUp(): void
    {
        $request = $this->getMockBuilder(IRequest::class)->getMock();
        $this->mockedRecipeService = $this->getMockBuilder(RecipeService::class)->disableOriginalConstructor()->getMock();
        $urlGenerator = $this->getMockBuilder(URLGenerator::class)->disableOriginalConstructor()->getMock();

        $this->controller = new PageController(
            'cookbook',
            $request,
            $this->mockedRecipeService,
            $urlGenerator
        );
    }

    public function testIndex()
    {
        $result = $this->controller->index();

        $this->assertEquals('index', $result->getTemplateName());
        $this->assertTrue($result instanceof TemplateResponse);
    }
}
