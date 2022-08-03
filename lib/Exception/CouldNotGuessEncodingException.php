<?php

namespace OCA\Cookbook\Exception;

class CouldNotGuessEncodingException extends \Exception {
	public function __construct($message = '', $code = 0, $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}
