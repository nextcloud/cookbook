<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OCA\Cookbook\Service;

use OCA\Cookbook\AppInfo\Application;
use OCA\Cookbook\Exception\AssistantNotAvailableException;
use OCA\Cookbook\Exception\InvalidAssistantResponseException;
use OCA\Cookbook\Helper\AssistantRecipeHelper;
use OCP\IL10N;
use OCP\TaskProcessing\Exception\Exception as TaskProcessingException;
use OCP\TaskProcessing\Exception\NotFoundException;
use OCP\TaskProcessing\IManager;
use OCP\TaskProcessing\Task;
use OCP\TaskProcessing\TaskTypes\TextToText;
use Psr\Log\LoggerInterface;

/**
 * Service to edit recipes using the Nextcloud Assistant.
 *
 * The assistant is accessed using the task processing API of the Nextcloud server.
 * As text generation might take a while, the work is split into scheduling a task and
 * fetching its result later on.
 */
class AssistantService {
	public const STATUS_SCHEDULED = 'scheduled';
	public const STATUS_RUNNING = 'running';
	public const STATUS_SUCCESSFUL = 'successful';
	public const STATUS_FAILED = 'failed';
	public const STATUS_CANCELLED = 'cancelled';

	/** @var IManager */
	private $taskProcessingManager;
	/** @var AssistantRecipeHelper */
	private $recipeHelper;
	/** @var IL10N */
	private $l;
	/** @var LoggerInterface */
	private $logger;
	/** @var ?string */
	private $userId;

	public function __construct(
		IManager $taskProcessingManager,
		AssistantRecipeHelper $recipeHelper,
		IL10N $l,
		LoggerInterface $logger,
		?string $UserId,
	) {
		$this->taskProcessingManager = $taskProcessingManager;
		$this->recipeHelper = $recipeHelper;
		$this->l = $l;
		$this->logger = $logger;
		$this->userId = $UserId;
	}

	/**
	 * Check if the assistant can be used to process texts.
	 *
	 * This is the case if there is at least one provider for free text processing
	 * (e.g. the Nextcloud Assistant app with a configured LLM backend).
	 *
	 * @return bool true if the assistant can be used
	 */
	public function isAvailable(): bool {
		try {
			$taskTypes = $this->taskProcessingManager->getAvailableTaskTypes(false, $this->userId);
		} catch (\Throwable $ex) {
			$this->logger->warning('Could not query the available task types of the task processing API', ['exception' => $ex]);
			return false;
		}

		return array_key_exists(TextToText::ID, $taskTypes);
	}

	/**
	 * Schedule a task to apply an instruction to a recipe.
	 *
	 * @param array $recipe The recipe as present in the editor
	 * @param string $instruction The instruction of the user
	 * @return int The id of the scheduled task
	 * @throws AssistantNotAvailableException if the assistant cannot be used
	 */
	public function scheduleRecipeEdit(array $recipe, string $instruction): int {
		/**
		 * Psalm targets PHP 8.0 and misreads the promoted constructor parameters of Task.
		 * @psalm-suppress TooManyArguments
		 */
		$task = new Task(
			TextToText::ID,
			['input' => $this->recipeHelper->buildPrompt($recipe, $instruction)],
			Application::APP_ID,
			$this->userId
		);

		try {
			$this->taskProcessingManager->scheduleTask($task);
		} catch (TaskProcessingException $ex) {
			$this->logger->error('Could not schedule the task for the assistant', ['exception' => $ex]);
			throw new AssistantNotAvailableException($this->l->t('The Nextcloud Assistant is not available.'), 0, $ex);
		}

		return $task->getId()
			?? throw new AssistantNotAvailableException($this->l->t('The Nextcloud Assistant is not available.'));
	}

	/**
	 * Get the current state of a scheduled task.
	 *
	 * The returned array always contains the key `status`.
	 * If the task was finished successfully, the key `recipe` contains the updated fields of the recipe.
	 * If the task failed, the key `error` contains a message for the user.
	 *
	 * @param int $taskId The id of the task as returned by scheduleRecipeEdit
	 * @return array The state of the task
	 * @throws NotFoundException if there is no such task for the current user
	 */
	public function getTaskState(int $taskId): array {
		$task = $this->taskProcessingManager->getUserTask($taskId, $this->userId);

		$status = $task->getStatus();
		if ($status === Task::STATUS_SUCCESSFUL) {
			return $this->getSuccessfulTaskState($task);
		}

		$passthrough = [
			Task::STATUS_SCHEDULED => self::STATUS_SCHEDULED,
			Task::STATUS_RUNNING => self::STATUS_RUNNING,
			Task::STATUS_CANCELLED => self::STATUS_CANCELLED,
		];
		if (isset($passthrough[$status])) {
			return ['status' => $passthrough[$status]];
		}

		$this->logger->warning('The assistant task failed', [
			'taskId' => $taskId,
			'status' => $status,
			'message' => $task->getErrorMessage(),
		]);
		return [
			'status' => self::STATUS_FAILED,
			'error' => $this->l->t('The Nextcloud Assistant could not process the recipe.'),
		];
	}

	/**
	 * Cancel a scheduled task.
	 *
	 * @param int $taskId The id of the task as returned by scheduleRecipeEdit
	 * @throws NotFoundException if there is no such task for the current user
	 */
	public function cancelTask(int $taskId): void {
		// Ensure the task belongs to the current user before cancelling it
		$this->taskProcessingManager->getUserTask($taskId, $this->userId);
		$this->taskProcessingManager->cancelTask($taskId);
	}

	private function getSuccessfulTaskState(Task $task): array {
		$text = $task->getOutput()['output'] ?? '';
		if (!is_string($text)) {
			$text = '';
		}

		try {
			return [
				'status' => self::STATUS_SUCCESSFUL,
				'recipe' => $this->recipeHelper->parseResponse($text),
			];
		} catch (InvalidAssistantResponseException $ex) {
			$this->logger->warning('The answer of the assistant could not be parsed', ['taskId' => $task->getId(), 'cause' => get_debug_type($ex->getPrevious())]);
			return [
				'status' => self::STATUS_FAILED,
				'error' => $ex->getMessage(),
			];
		}
	}
}
