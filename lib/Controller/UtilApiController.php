<?php

namespace OCA\Cookbook\Controller;

use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\Attribute\CORS;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class UtilApiController extends ApiController {
	public function __construct($AppName, IRequest $request) {
		parent::__construct($AppName, $request);
	}

	/**
	 * @return JSONResponse
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	#[CORS]
	public function getApiVersion(): JSONResponse {
		$response = [
			'cookbook_version' => [0, 11, 4], /* VERSION_TAG do not change this line manually */
			'api_version' => [
				'epoch' => 0,
				'major' => 1,
				'minor' => 2
			]
		];

		return new JSONResponse($response, 200);
	}
}
