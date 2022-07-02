<?php

namespace OCA\Cookbook\Helper\HTMLParser;

class AttributeNotFoundException extends \Exception {
	public function __construct($message = null, $code = null, $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}
