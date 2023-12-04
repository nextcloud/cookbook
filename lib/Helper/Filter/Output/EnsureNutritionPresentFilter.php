<?php

namespace OCA\Cookbook\Helper\Filter\Output;

use OCA\Cookbook\Helper\Filter\JSON\AbstractJSONFilter;
use OCP\IL10N;
use Psr\Log\LoggerInterface;

/**
 * Ensure the nutrition field can be detected as a schema.org object
 */
class EnsureNutritionPresentFilter extends AbstractJSONFilter {
	private const NUTRITION = 'nutrition';

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
		$cache = $json;

		if (empty($json['nutrition']) || !is_array($json['nutrition'])) {
			$json['nutrition'] = [];
		}

		if (empty($json['nutrition']['@type'])) {
			$json['nutrition']['@type'] = 'NutritionInformation';
		}

		return $json !== $cache;
	}
}
