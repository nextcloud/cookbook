<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Cookbook\tests\Unit\Controller\Implementation;

use OCA\Cookbook\Controller\Implementation\AssistantImplementation;
use OCA\Cookbook\Exception\AssistantNotAvailableException;
use OCA\Cookbook\Helper\RestParameterParser;
use OCA\Cookbook\Service\AssistantService;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IL10N;
use OCP\TaskProcessing\Exception\NotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers OCA\Cookbook\Controller\Implementation\AssistantImplementation
 */
class AssistantImplementationTest extends TestCase {
	/** @var AssistantImplementation */
	private $sut;
	/** @var AssistantService|MockObject */
	private $service;
	/** @var RestParameterParser|MockObject */
	private $restParser;

	public function setUp(): void {
		parent::setUp();

		$this->service = $this->createMock(AssistantService::class);
		$this->restParser = $this->createMock(RestParameterParser::class);
		$l = $this->createStub(IL10N::class);
		$l->method('t')->willReturnArgument(0);

		$this->sut = new AssistantImplementation($this->service, $this->restParser, $l);
	}

	public function testCreate(): void {
		$recipe = ['name' => 'Pancakes', 'recipeIngredient' => ['1 cup flour']];
		$this->restParser->method('getParameters')->willReturn([
			'recipe' => $recipe,
			'prompt' => '  Translate to English  ',
		]);

		$this->service->expects($this->once())
			->method('scheduleRecipeEdit')
			->with($recipe, 'Translate to English')
			->willReturn(42);

		/** @var JSONResponse $response */
		$response = $this->sut->create();

		$this->assertEquals(200, $response->getStatus());
		$this->assertEquals(['id' => 42], $response->getData());
	}

	/**
	 * @dataProvider dpCreateInvalid
	 */
	public function testCreateInvalidParameters(array $data): void {
		$this->restParser->method('getParameters')->willReturn($data);
		$this->service->expects($this->never())->method('scheduleRecipeEdit');

		/** @var JSONResponse $response */
		$response = $this->sut->create();

		$this->assertEquals(400, $response->getStatus());
	}

	public static function dpCreateInvalid(): array {
		return [
			'no data' => [[]],
			'no recipe' => [['prompt' => 'Do something']],
			'recipe not an object' => [['recipe' => 'foo', 'prompt' => 'Do something']],
			'no prompt' => [['recipe' => ['name' => 'Foo']]],
			'empty prompt' => [['recipe' => ['name' => 'Foo'], 'prompt' => '   ']],
			'prompt not a string' => [['recipe' => ['name' => 'Foo'], 'prompt' => ['x']]],
		];
	}

	public function testCreateNotAvailable(): void {
		$this->restParser->method('getParameters')->willReturn([
			'recipe' => ['name' => 'Foo'],
			'prompt' => 'Do something',
		]);
		$this->service->method('scheduleRecipeEdit')
			->willThrowException(new AssistantNotAvailableException('Not available'));

		/** @var JSONResponse $response */
		$response = $this->sut->create();

		$this->assertEquals(503, $response->getStatus());
		$this->assertEquals(['msg' => 'Not available'], $response->getData());
	}

	public function testShow(): void {
		$state = ['status' => 'successful', 'recipe' => ['name' => 'Foo']];
		$this->service->expects($this->once())->method('getTaskState')->with(42)->willReturn($state);

		/** @var JSONResponse $response */
		$response = $this->sut->show('42');

		$this->assertEquals(200, $response->getStatus());
		$this->assertEquals($state, $response->getData());
	}

	public function testShowNotFound(): void {
		$this->service->method('getTaskState')->willThrowException(new NotFoundException());

		/** @var JSONResponse $response */
		$response = $this->sut->show(42);

		$this->assertEquals(404, $response->getStatus());
	}

	public function testCancel(): void {
		$this->service->expects($this->once())->method('cancelTask')->with(42);

		/** @var JSONResponse $response */
		$response = $this->sut->cancel('42');

		$this->assertEquals(200, $response->getStatus());
	}

	public function testCancelNotFound(): void {
		$this->service->method('cancelTask')->willThrowException(new NotFoundException());

		/** @var JSONResponse $response */
		$response = $this->sut->cancel(42);

		$this->assertEquals(404, $response->getStatus());
	}
}
