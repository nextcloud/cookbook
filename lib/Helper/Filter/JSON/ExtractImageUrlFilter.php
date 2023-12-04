<?php

namespace OCA\Cookbook\Helper\Filter\JSON;

use OCP\IL10N;
use Psr\Log\LoggerInterface;

/**
 * Select the best image URL.
 *
 * This filter makes a few sanity checks on the recipe image URL.
 * It tries to guess the image URL with the highest resolution heuristically.
 *
 * It ensures that the image property is no object but a pure URL.
 */
class ExtractImageUrlFilter extends AbstractJSONFilter {
	/** @var IL10N */
	private $l;

	/** @var LoggerInterface */
	private $logger;

	public function __construct(IL10N $l, LoggerInterface $logger) {
		$this->l = $l;
		$this->logger = $logger;
	}

	public function apply(array &$json): bool {
		if (!isset($json['image']) || !$json['image']) {
			$json['image'] = '';
			return true;
		}

		if (is_string($json['image'])) {
			// We do not change anything here
			return false;
		}

		if (!is_array($json['image'])) {
			// It is neither a plain string nor a JSON object
			$this->logger->info($this->l->t('The given image for the recipe %s cannot be parsed. Aborting and skipping it.', [$json['name']]));
			$json['image'] = '';
			return true;
		}

		// We have an array of images or an object
		if (isset($json['image']['url'])) {
			// We have a single object. Use it.
			$json['image'] = $json['image']['url'];
			return true;
		}

		// We have an array of images. Extract the URLs
		$images = array_map(function ($x) {
			if (is_string($x)) {
				return $x;
			}
			if (is_array($x) && isset($x['url']) && $x['url'] && is_string($x['url'])) {
				return $x['url'];
			}
			return null;
		}, $json['image']);
		$images = array_filter($images);

		if (count($images) === 1) {
			reset($images);
			$json['image'] = current($images);
			return true;
		}

		if (count($images) === 0) {
			$this->logger->info($this->l->t('No valid recipe was left after heuristics of recipe %s.', [$json['name']]));
			$json['image'] = '';
			return true;
		}

		$maxSum = -1;
		$changed = false;

		foreach ($images as $img) {
			$regex = preg_match_all('/\d+/', $img, $matches);

			if ($regex !== false && $regex > 0) {
				$sum = array_sum(array_map(fn ($x) => ((int) $x), $matches[0]));

				if ($sum > $maxSum) {
					$json['image'] = $img;
					$maxSum = $sum;
					$changed = true;
				}
			}
		}

		if (!$changed) {
			$this->logger->info($this->l->t('Heuristics failed for image extraction of recipe %s.', [$json['name']]));

			reset($images);
			$json['image'] = current($images);
		}

		return true;
	}
}
