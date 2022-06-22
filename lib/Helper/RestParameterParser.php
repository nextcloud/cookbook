<?php

namespace OCA\Cookbook\Helper;

use OCP\IL10N;

class RestParameterParser {
	private const CHARSET = 'charset';
	private const CONTENT_TYPE = 'CONTENT_TYPE';
	private const REQUEST_METHOD = 'REQUEST_METHOD';

	/**
	 * @var IL10N
	 */
	private $l;

	public function __construct(IL10N $l10n) {
		$this->l = $l10n;
	}

	/**
	 * Fetch the parameters from the input accordingly
	 *
	 * Depending on the way of transmitting parameters from the browser to the PHP script different ways to recover these have to be done.
	 * This is a helper that should cover this.
	 *
	 * @return array The parameters transmitted
	 */
	public function getParameters(): array {
		if (isset($_SERVER[self::CONTENT_TYPE])) {
			$parts = explode(';', $_SERVER[self::CONTENT_TYPE], 2);

			switch (trim($parts[0])) {
				case 'application/json':
					$enc = $this->getEncoding($_SERVER[self::CONTENT_TYPE]);
					return $this->parseApplicationJSON($enc);
					break;

				case 'multipart/form-data':
					if ($this->isPost()) {
						return $_POST;
					} else {
						throw new \Exception($this->l->t('Cannot parse non-POST multipart encoding. This is a bug.'));
					}
					break;

				case 'application/x-www-form-urlencoded':
					if ($this->isPost()) {
						return $_POST;
					} else {
						$enc = $this->getEncoding($_SERVER[self::CONTENT_TYPE]);
						return $this->parseUrlEncoded($enc);
					}
					break;
			}
		} else {
			throw new \Exception($this->l->t('Cannot detect type of transmitted data. This is a bug, please report it.'));
		}
	}

	/**
	 * Parse data transmitted as application/json
	 * @param $encoding string The encoding to use
	 * @return array
	 */
	private function parseApplicationJSON(string $encoding): array {
		$rawData = file_get_contents('php://input');

		if ($encoding !== 'UTF-8') {
			$rawData = iconv($encoding, 'UTF-8', $rawData);
		}
		return json_decode($rawData, true);
	}

	/**
	 * Parse the URL encoded value transmitted
	 *
	 * This is by far no ideal solution but just a quick hack in order to cope with this situation.
	 * Either use the POST method (where PHP does the parsing) or use application/json
	 *
	 * @param string $encoding The encoding to use
	 * @throws \Exception If the requested string is not well-formatted accoring to the minimal implementation
	 * @return array The values transmitted
	 */
	private function parseUrlEncoded(string $encoding): array {
		$rawData = file_get_contents('php://input');
		if ($encoding !== 'UTF-8') {
			$rawData = iconv($encoding, 'UTF-8', $rawData);
		}

		$ret = [];
		foreach (explode('&', $rawData) as $assignment) {
			$parts = explode('=', $assignment, 2);

			if (count($parts) < 2) {
				throw new \Exception($this->l->t('Invalid URL-encoded string found. Please report a bug.'));
			}

			$key = $parts[0];
			$value = urldecode($parts[1]);

			if (substr_compare($key, '[]', -2, 2)) {
				// $key ends in []
				// Drop '[]' at the end
				$key = substr($key, 0, -2);

				if (!array_key_exists($key, $ret)) {
					$ret[$key] = [];
				}

				$ret[$key][] = $value;
			} else {
				$ret[$key] = $value;
			}
		}

		return $ret;
	}

	/**
	 * Get the encoding from the header
	 * @param string $header The header to parse
	 * @return string The encoding as string
	 */
	private function getEncoding(string $header): string {
		$parts = explode(';', $header);

		// Fallback encoding
		$enc = 'UTF-8';

		for ($i = 1; $i < count($parts); $i++) {
			if (substr_compare(trim($parts[$i]), self::CHARSET, 0, strlen(self::CHARSET))) {
				// parts[$i] begins with charset=
				// Drop string 'charset='
				$enc = substr(trim($parts[1]), strlen(self::CHARSET) + 1);
				break;
			}
		}

		return $enc;
	}

	/**
	 * Check if the request is a POST request
	 * @return bool true, if the request is a POST request.
	 */
	private function isPost(): bool {
		return $_SERVER[self::REQUEST_METHOD] === 'POST';
	}
}
