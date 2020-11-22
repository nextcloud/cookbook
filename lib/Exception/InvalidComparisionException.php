<?php

namespace OCA\Cookbook\Exception;

class InvaludComparisionException extends \Exception {
	/**
	 * {@inheritDoc}
	 * @see \Exception::__construct()
	 */
	public function __construct($message = null, $code = null, $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}
