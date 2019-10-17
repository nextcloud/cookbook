<?php

namespace OCA\Cookbook\Tests\Unit\Controller;


use OC\URLGenerator;
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
    private $userId = 'john';

    public function setUp()
    {
        $request = $this->getMockBuilder(IRequest::class)->getMock();
        $dbConnection = $this->getMockBuilder(IDBConnection::class)->getMock();
        $rootFolder = $this->getMockBuilder(IRootFolder::class)->getMock();
        $config = $this->getMockBuilder(IConfig::class)->getMock();
        $urlGenerator = $this->getMockBuilder(URLGenerator::class)->disableOriginalConstructor()->getMock();

        $this->controller = new PageController(
            'cookbook',
            $dbConnection,
            $rootFolder,
            $request,
            $this->userId,
            $config,
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
