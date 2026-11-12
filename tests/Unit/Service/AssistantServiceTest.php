<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Cookbook\tests\Unit\Service;

use OCA\Cookbook\Exception\AssistantNotAvailableException;
use OCA\Cookbook\Exception\InvalidAssistantResponseException;
use OCA\Cookbook\Helper\AssistantRecipeHelper;
use OCA\Cookbook\Service\AssistantService;
use OCP\IL10N;
use OCP\TaskProcessing\Exception\NotFoundException;
use OCP\TaskProcessing\Exception\PreConditionNotMetException;
use OCP\TaskProcessing\IManager;
use OCP\TaskProcessing\Task;
use OCP\TaskProcessing\TaskTypes\TextToText;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers OCA\Cookbook\Service\AssistantService
 */
class AssistantServiceTest extends TestCase {
	private const USER_ID = 'alice';

	/** @var AssistantService */
	private $dut;
	/** @var IManager|MockObject */
	private $manager;
	/** @var AssistantRecipeHelper|MockObject */
	private $helper;

	public function setUp(): void {
		parent::setUp();

		$this->manager = $this->createMock(IManager::class);
		$this->helper = $this->createMock(AssistantRecipeHelper::class);
		$l = $this->createStub(IL10N::class);
		$l->method('t')->willReturnArgument(0);
		$logger = $this->createStub(LoggerInterface::class);

		$this->dut = new AssistantService($this->manager, $this->helper, $l, $logger, self::USER_ID);
	}

	/**
	 * @dataProvider dpIsAvailable
	 */
	public function testIsAvailable(array $taskTypes, bool $expected): void {
		$this->manager->method('getAvailableTaskTypes')->willReturn($taskTypes);

		$this->assertSame($expected, $this->dut->isAvailable());
	}

	public static function dpIsAvailable(): array {
		return [
			'no task types' => [[], false],
			'other task types' => [['core:text2image' => ['name' => 'foo']], false],
			'text to text' => [[TextToText::ID => ['name' => 'Free text to text']], true],
		];
	}

	public function testIsAvailableOnError(): void {
		$this->manager->method('getAvailableTaskTypes')->willThrowException(new \RuntimeException('Boom'));

		$this->assertFalse($this->dut->isAvailable());
	}

	public function testScheduleRecipeEdit(): void {
		$recipe = ['name' => 'Pancakes'];
		$this->helper->method('buildPrompt')->with($recipe, 'Do it')->willReturn('The prompt');

		$this->manager->expects($this->once())->method('scheduleTask')
			->with($this->callback(function (Task $task) {
				$this->assertSame(TextToText::ID, $task->getTaskTypeId());
				$this->assertSame(['input' => 'The prompt'], $task->getInput());
				$this->assertSame('cookbook', $task->getAppId());
				$this->assertSame(self::USER_ID, $task->getUserId());
				$task->setId(42);
				return true;
			}));

		$this->assertSame(42, $this->dut->scheduleRecipeEdit($recipe, 'Do it'));
	}

	public function testScheduleRecipeEditFailing(): void {
		$this->helper->method('buildPrompt')->willReturn('The prompt');
		$this->manager->method('scheduleTask')->willThrowException(new PreConditionNotMetException());

		$this->expectException(AssistantNotAvailableException::class);
		$this->dut->scheduleRecipeEdit(['name' => 'Foo'], 'Do it');
	}

	private function createTask(int $status, ?array $output = null, ?string $error = null): Task {
		$task = new Task(TextToText::ID, ['input' => 'prompt'], 'cookbook', self::USER_ID);
		$task->setId(42);
		$task->setStatus($status);
		if ($output !== null) {
			$task->setOutput($output);
		}
		if ($error !== null) {
			$task->setErrorMessage($error);
		}
		return $task;
	}

	/**
	 * @dataProvider dpTaskStates
	 */
	public function testGetTaskStatePending(int $status, string $expected): void {
		$this->manager->method('getUserTask')->with(42, self::USER_ID)->willReturn($this->createTask($status));
		$this->helper->expects($this->never())->method('parseResponse');

		$this->assertEquals(['status' => $expected], $this->dut->getTaskState(42));
	}

	public static function dpTaskStates(): array {
		return [
			[Task::STATUS_SCHEDULED, AssistantService::STATUS_SCHEDULED],
			[Task::STATUS_RUNNING, AssistantService::STATUS_RUNNING],
			[Task::STATUS_CANCELLED, AssistantService::STATUS_CANCELLED],
		];
	}

	/**
	 * @dataProvider dpFailedStates
	 */
	public function testGetTaskStateFailed(int $status): void {
		$this->manager->method('getUserTask')->willReturn($this->createTask($status, null, 'Provider error'));

		$state = $this->dut->getTaskState(42);

		$this->assertSame(AssistantService::STATUS_FAILED, $state['status']);
		$this->assertNotEmpty($state['error']);
	}

	public static function dpFailedStates(): array {
		return [
			[Task::STATUS_FAILED],
			[Task::STATUS_UNKNOWN],
		];
	}

	public function testGetTaskStateSuccessful(): void {
		$this->manager->method('getUserTask')->willReturn(
			$this->createTask(Task::STATUS_SUCCESSFUL, ['output' => '{"name": "Foo"}'])
		);
		$this->helper->method('parseResponse')->with('{"name": "Foo"}')->willReturn(['name' => 'Foo']);

		$this->assertEquals([
			'status' => AssistantService::STATUS_SUCCESSFUL,
			'recipe' => ['name' => 'Foo'],
		], $this->dut->getTaskState(42));
	}

	public function testGetTaskStateUnparsable(): void {
		$this->manager->method('getUserTask')->willReturn(
			$this->createTask(Task::STATUS_SUCCESSFUL, ['output' => 'Nonsense'])
		);
		$this->helper->method('parseResponse')->willThrowException(new InvalidAssistantResponseException('Cannot parse'));

		$this->assertEquals([
			'status' => AssistantService::STATUS_FAILED,
			'error' => 'Cannot parse',
		], $this->dut->getTaskState(42));
	}

	public function testGetTaskStateNotFound(): void {
		$this->manager->method('getUserTask')->willThrowException(new NotFoundException());

		$this->expectException(NotFoundException::class);
		$this->dut->getTaskState(42);
	}

	public function testCancelTask(): void {
		$this->manager->expects($this->once())->method('getUserTask')->with(42, self::USER_ID)
			->willReturn($this->createTask(Task::STATUS_RUNNING));
		$this->manager->expects($this->once())->method('cancelTask')->with(42);

		$this->dut->cancelTask(42);
	}

	public function testCancelTaskNotFound(): void {
		$this->manager->method('getUserTask')->willThrowException(new NotFoundException());
		$this->manager->expects($this->never())->method('cancelTask');

		$this->expectException(NotFoundException::class);
		$this->dut->cancelTask(42);
	}
}
