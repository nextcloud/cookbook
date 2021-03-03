<?php

namespace OCA\Cookbook\Db;

use OCP\Util;

class DbTypesPolyfillHelper {
	/**
	 * @var String
	 */
	private $int;
	/**
	 * @var String
	 */
	private $string;
	
	public function __construct(Util $util) {
		switch ($util->getVersion()[0]) {
			case 18:
			case 19:
			case 20:
				$this->int = \Doctrine\DBAL\Types\Type::INTEGER;
				$this->string = \Doctrine\DBAL\Types\Type::STRING;
				break;
				
			default:
				$this->int = \OCP\DB\Types::INTEGER;
				$this->string = \OCP\DB\Types::STRING;
				break;
		}
	}
	
	/**
	 * @return string
	 */
	final public function INT() {
		return $this->int;
	}

	/**
	 * @return string
	 */
	final public function STRING() {
		return $this->string;
	}
}
