<?php

namespace OCA\Cookbook\Controller;

use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\JSONResponse;

class UtilApiController extends ApiController {
	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 * @return JSONResponse
	 */
	public function getApiVersion(): JSONResponse {
		$response = [
			'cookbook_version' => [0, 9, 14], /* VERSION_TAG do not change this line manually */
			'api_version' => [
				'epoch' => 0,
				'major' => 1,
				'minor' => 0
			]
		];
		return new JSONResponse($response, 200);
	}
}
