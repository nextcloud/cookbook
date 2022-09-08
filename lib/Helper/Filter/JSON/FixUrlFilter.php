<?php

namespace OCA\Cookbook\Helper\Filter\JSON;

use OCP\IL10N;
use Psr\Log\LoggerInterface;
use OCA\Cookbook\Helper\Filter\AbstractJSONFilter;

/**
 * Fix the URL of a recipe.
 *
 * The URL must be present in all cases.
 * If is is not present, this filter will insert the empty string instead.
 *
 * Apart from that, the URL is sanitized at least.
 */
class FixUrlFilter extends AbstractJSONFilter {
	private const URL = 'url';

	/** @var IL10N */
	private $l;

	/** @var LoggerInterface */
	private $logger;

	public function __construct(
		IL10N $l,
		LoggerInterface $logger
	) {
		$this->l = $l;
		$this->logger = $logger;
	}

	public function apply(array &$json): bool {
		if (!isset($json[self::URL])) {
			$json[self::URL] = '';
			return true;
		}

		$url = filter_var($json[self::URL], FILTER_SANITIZE_URL);
		if (filter_var($url, FILTER_VALIDATE_URL) === false) {
			$url = '';
		}

		$changed = $url !== $json[self::URL];
		$json[self::URL] = $url;
		return $changed;
	}
}
