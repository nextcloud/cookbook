<?php

namespace OCA\Cookbook\Controller;

use OCA\Cookbook\Service\ApiVersion;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\Attribute\CORS;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class UtilApiController extends ApiController {
	public function __construct(
		string $AppName,
		IRequest $request,
		private ApiVersion $apiVersion,
	) {
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
			'cookbook_version' => $this->apiVersion->getAppVersion(),
			'api_version' => $this->apiVersion->getAPIVersion(),
		];

		return new JSONResponse($response, 200);
	}
}
