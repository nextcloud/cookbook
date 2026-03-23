<?php

namespace OCA\Cookbook\Controller;

use OCA\Cookbook\Exception\FolderNotWritableException;
use OCA\Cookbook\Exception\UserNotLoggedInException;
use OCA\Cookbook\Helper\UserFolderHelper;
use OCA\Cookbook\Helper\MyRecipesFolderHelper;
use OCA\Cookbook\Helper\SharedRecipesFolderHelper;
use OCA\Cookbook\Service\DbCacheService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCP\Util;

class MainController extends Controller {
	/** @var string */
	protected $appName;
	/** @var DbCacheService */
	private $dbCacheService;
	/** @var UserFolderHelper */
	private $userFolder;
	/** @var MyRecipesFolderHelper */
	private $myRecipesFolder;
	/** @var SharedRecipesFolderHelper */
	private $sharedRecipesFolder;

	public function __construct(
		string $AppName,
		IRequest $request,
		DbCacheService $dbCacheService,
		UserFolderHelper $userFolder,
		MyRecipesFolderHelper $myRecipesFolder,
		SharedRecipesFolderHelper $sharedRecipesFolder,
	) {
		parent::__construct($AppName, $request);

		$this->appName = $AppName;
		$this->dbCacheService = $dbCacheService;
		$this->userFolder = $userFolder;
		$this->myRecipesFolder = $myRecipesFolder;
		$this->sharedRecipesFolder = $sharedRecipesFolder;
	}

	/**
	 * Load the start page of the app.
	 *
	 * @throws UserNotLoggedInException never
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function index(): TemplateResponse {
		try {
			// Check if the user folders can be accessed
			$this->userFolder->getFolder();
			$this->myRecipesFolder->getFolder();
			$this->sharedRecipesFolder->getFolder();
		} catch (FolderNotWritableException $ex) {
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
