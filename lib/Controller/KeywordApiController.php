<?php

namespace OCA\Cookbook\Controller;

use OCA\Cookbook\Controller\Implementation\KeywordImplementation;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class KeywordApiController extends ApiController {
	/** @var KeywordImplementation */
	private $impl;

	public function __construct(
		string $AppName,
		IRequest $request,
		KeywordImplementation $keywordImplementation
	) {
		parent::__construct($AppName, $request);

		$this->impl = $keywordImplementation;
	}
	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 *
	 * @return JSONResponse
	 */
	public function keywords() {
		return $this->impl->index();
	}
}
