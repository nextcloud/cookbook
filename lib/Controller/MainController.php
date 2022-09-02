<?php

namespace OCA\Cookbook\Controller;

use OCP\IRequest;
use OCP\Util;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Controller;
use OCA\Cookbook\Service\DbCacheService;
use OCA\Cookbook\Exception\UserFolderNotWritableException;
use OCA\Cookbook\Exception\UserNotLoggedInException;
use OCA\Cookbook\Helper\UserFolderHelper;

class MainController extends Controller {
	/** @var string */
	protected $appName;
	/** @var DbCacheService */
	private $dbCacheService;
	/** @var UserFolderHelper */
	private $userFolder;

	public function __construct(
		string $AppName,
		IRequest $request,
		DbCacheService $dbCacheService,
		UserFolderHelper $userFolder
	) {
		parent::__construct($AppName, $request);

		$this->appName = $AppName;
		$this->dbCacheService = $dbCacheService;
		$this->userFolder = $userFolder;
	}

	/**
	 * Load the start page of the app.
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @throws UserNotLoggedInException never
	 */
	public function index(): TemplateResponse {
		try {
			// Check if the user folder can be accessed
			$this->userFolder->getFolder();
		} catch (UserFolderNotWritableException $ex) {
			Util::addScript('cookbook', 'cookbook-guest');
			return new TemplateResponse($this->appName, 'invalid_guest');
		}
		/*
		 * The UserNotLoggedInException will not be caught here. It should never happen as the middleware of NC
		 * will prevent the controller to be called. If this does not happen for some reason, let the exception be
		 * thrown and the user most probably has found a bug. A stack trace might help there.
		 */

		$this->dbCacheService->triggerCheck();

		Util::addScript('cookbook', 'cookbook-main');
		return new TemplateResponse($this->appName, 'index');  // templates/index.php
	}
}
