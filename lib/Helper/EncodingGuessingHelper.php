<?php

namespace OCA\Cookbook\Helper;

use OCA\Cookbook\Exception\CouldNotGuessEncodingException;
use OCP\IL10N;

/**
 * This class is a helper to help getting the type of a downloaded HTML file for further encoding and parsing.
 * It extracts the correct text encoding.
 */
class EncodingGuessingHelper {
	/** @var IL10N */
	private $l;

	public function __construct(
		IL10N $l
	) {
		$this->l = $l;
	}
	/**
	 * Extract the text encoding from a HTML file
	 *
	 * @param string $content The content of the file
	 * @param ?string $contentType The ContentType header if present or null to look at the contents
	 * @return string The guessed content encoding
	 */
	public function guessEncoding(string $content, ?string $contentType): string {
		if ($contentType !== null) {
			$guess = $this->guessFromContentType($contentType);

			if ($guess !== null) {
				return $guess;
			}
		}

		$guess = $this->guessFromMainContent($content);
		if ($guess === null) {
			throw new CouldNotGuessEncodingException($this->l->t('No content encoding was detected in the content.'));
		} else {
			return $guess;
		}
	}

	private function guessFromContentType(string $contentType): ?string {
		$parts = explode(';', $contentType);
		$parts = array_map(function ($x) {
			return trim($x);
		}, $parts);

		foreach ($parts as $part) {
			$subparts = explode('=', $part, 2);
			if (strtolower($subparts[0]) === 'charset' && count($subparts) === 2) {
				return $subparts[1];
			}
		}
		// Fallback: We did not find anything in the Content-Type
		return null;
	}

	private function guessFromMainContent(string $content): ?string {
		$regex = "/<meta[^>]* charset=['\"]?([^'\">]*)['\"]?[^>]*>/";
		$ret = preg_match($regex, $content, $matches);
		if ($ret === 1) {
			return $matches[1];
		}
		return null;
	}
}
