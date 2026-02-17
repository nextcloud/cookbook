<?php

namespace OCA\Cookbook\Helper\FileSystem;

use OCP\IL10N;
use Psr\Log\LoggerInterface;

/**
 * This class should help to normalize the characters in the filesystem accordingly.
 * Special chars are replaced by underscores, typically.
 */
class RecipeNameHelper {
	/** @var IL10N */
	private $l;
	/** @var LoggerInterface */
	private $logger;

	/** @var int */
	protected const MAX_LEN = 100;

	public function __construct(
		IL10N $l,
		LoggerInterface $logger,
	) {
		$this->l = $l;
		$this->logger = $logger;
	}

	/**
	 * Replace the name of a recipe such that no special chars are present anymore.
	 *
	 * Additionally, the file name is truncated to 100 chars.
	 *
	 * @param string $recipeName The original recipe name
	 * @return string The cleaned recipe name
	 */
	public function getFolderName(string $recipeName): string {
		$pattern = '/[\\/:?!"\\\\\'|&^#]/';
		$recipeName = preg_replace($pattern, '_', $recipeName);
		if (is_null($recipeName)) {
			throw new \InvalidArgumentException($this->l->t('Recipe name cannot be processed.'));
		}

		if (mb_strlen($recipeName) > self::MAX_LEN) {
			$recipeName = mb_substr($recipeName, 0, self::MAX_LEN - 3) . '___';
		}

		return $recipeName;
	}
}
