<?php

namespace OCA\Cookbook\Controller;

use OCA\Cookbook\Controller\Implementation\KeywordImplementation;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class KeywordController extends Controller {
	/** @var KeywordImplementation */
	private $impl;

	public function __construct(
		string $AppName,
		IRequest $request,
		KeywordImplementation $keywordImplementation,
	) {
		parent::__construct($AppName, $request);

		$this->impl = $keywordImplementation;
	}
	/**
	 * @NoAdminRequired
	 *
	 * @return JSONResponse
	 */
	#[NoAdminRequired]
	public function keywords() {
		return $this->impl->index();
	}
}
