<?php

namespace OCA\Cookbook\Exception;

class UserNotLoggedInException extends \Exception {
	public function __construct($message = '', $code = 0, $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}
