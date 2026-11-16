<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OCA\Cookbook\Controller\Implementation;

use OCA\Cookbook\Exception\AssistantNotAvailableException;
use OCA\Cookbook\Helper\RestParameterParser;
use OCA\Cookbook\Service\AssistantService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IL10N;
use OCP\TaskProcessing\Exception\NotFoundException;

class AssistantImplementation {
	/** @var AssistantService */
	private $service;
	/** @var RestParameterParser */
	private $restParser;
	/** @var IL10N */
	private $l;

	public function __construct(
		AssistantService $assistantService,
		RestParameterParser $restParser,
		IL10N $l,
	) {
		$this->service = $assistantService;
		$this->restParser = $restParser;
		$this->l = $l;
	}

	/**
	 * Schedule a task to edit a recipe
	 *
	 * The recipe and the instruction are extracted from the request body.
	 *
	 * @return JSONResponse
	 */
	public function create() {
		$data = $this->restParser->getParameters();

		$recipe = $data['recipe'] ?? null;
		$instruction = $data['prompt'] ?? null;

		if (!is_array($recipe) || !is_string($instruction) || trim($instruction) === '') {
			return new JSONResponse([
				'msg' => $this->l->t('A recipe and an instruction for the assistant are required.'),
			], Http::STATUS_BAD_REQUEST);
		}

		try {
			$id = $this->service->scheduleRecipeEdit($recipe, trim($instruction));
		} catch (AssistantNotAvailableException $ex) {
			return new JSONResponse(['msg' => $ex->getMessage()], Http::STATUS_SERVICE_UNAVAILABLE);
		}

		return new JSONResponse(['id' => $id], Http::STATUS_OK);
	}

	/**
	 * Get the state and result of a task
	 *
	 * @param int $id The id of the task
	 * @return JSONResponse
	 */
	public function show($id) {
		try {
			return new JSONResponse($this->service->getTaskState((int)$id), Http::STATUS_OK);
		} catch (NotFoundException $ex) {
			return new JSONResponse(['msg' => $this->l->t('Task not found.')], Http::STATUS_NOT_FOUND);
		}
	}

	/**
	 * Cancel a task
	 *
	 * @param int $id The id of the task
	 * @return JSONResponse
	 */
	public function cancel($id) {
		try {
			$this->service->cancelTask((int)$id);
		} catch (NotFoundException $ex) {
			return new JSONResponse(['msg' => $this->l->t('Task not found.')], Http::STATUS_NOT_FOUND);
		}

		return new JSONResponse('OK', Http::STATUS_OK);
	}
}
