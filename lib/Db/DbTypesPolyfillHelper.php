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

	/** @var string */
	private $date;

	public function __construct(Util $util) {
		switch ($util->getVersion()[0]) {
			case 18:
			case 19:
			case 20:
				$this->int = \Doctrine\DBAL\Types\Type::INTEGER;
				$this->string = \Doctrine\DBAL\Types\Type::STRING;
				$this->date = \Doctrine\DBAL\Types\Type::DATE;
				break;

			default:
				$this->int = \OCP\DB\Types::INTEGER;
				$this->string = \OCP\DB\Types::STRING;
				$this->date = \OCP\DB\Types::DATE;
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

	/**
	 * @return string
	 */
	final public function DATE() {
		return $this->date;
	}
}
