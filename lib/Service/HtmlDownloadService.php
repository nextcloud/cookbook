<?php

namespace OCA\Cookbook\Service;

use DOMDocument;
use OCA\Cookbook\Exception\CouldNotGuessEncodingException;
use OCA\Cookbook\Exception\ImportException;
use OCA\Cookbook\Exception\NoDownloadWasCarriedOutException;
use OCA\Cookbook\Helper\DownloadEncodingHelper;
use OCA\Cookbook\Helper\DownloadHelper;
use OCA\Cookbook\Helper\EncodingGuessingHelper;
use OCA\Cookbook\Helper\HTMLFilter\AbstractHtmlFilter;
use OCA\Cookbook\Helper\HTMLFilter\HtmlEncodingFilter;
use OCA\Cookbook\Helper\HTMLFilter\HtmlEntityDecodeFilter;
use OCA\Cookbook\Helper\HtmlToDomParser;
use OCP\IL10N;
use Psr\Log\LoggerInterface;

class HtmlDownloadService {
	/**
	 * @var array
	 */
	private $htmlFilters;

	/**
	 * @var IL10N
	 */
	private $l;

	/** @var LoggerInterface */
	private $logger;

	/**
	 * @var HtmlToDomParser
	 */
	private $htmlParser;

	/** @var DownloadHelper */
	private $downloadHelper;

	/** @var EncodingGuessingHelper */
	private $encodingGuesser;

	/** @var DownloadEncodingHelper */
	private $downloadEncodingHelper;

	/**
	 * @var DOMDocument
	 */
	private $dom;

	public function __construct(
		HtmlEntityDecodeFilter $htmlEntityDecodeFilter,
		HtmlEncodingFilter $htmlEncodingFilter,
		IL10N $l10n,
		LoggerInterface $logger,
		HtmlToDomParser $htmlParser,
		DownloadHelper $downloadHelper,
		EncodingGuessingHelper $encodingGuesser,
		DownloadEncodingHelper $downloadEncodingHelper,
	) {
		$this->htmlFilters = [
			$htmlEntityDecodeFilter,
			$htmlEncodingFilter,
		];
		$this->l = $l10n;
		$this->logger = $logger;
		$this->htmlParser = $htmlParser;
		$this->downloadHelper = $downloadHelper;
		$this->encodingGuesser = $encodingGuesser;
		$this->downloadEncodingHelper = $downloadEncodingHelper;
	}

	/**
	 * Download a recipe URL and extract the JSON from it
	 *
	 * The return value is one of the constants of the class PARSE_SUCCESS, PARSE_WARNING,
	 * PARSE_ERROR, or PARSE_FATAL_ERROR.
	 *
	 * @param int The indicator if the HTML page was correctly parsed
	 * @return int The state indicating the result of the parsing (@see HtmlToDomParser)
	 * @throws ImportException If obtaining of the URL was not possible
	 */
	public function downloadRecipe(string $url): int {
		$html = $this->fetchHtmlPage($url);

		// Filter the HTML code
		/** @var AbstractHtmlFilter $filter */
		foreach ($this->htmlFilters as $filter) {
			$filter->apply($html);
		}

		$dom = new DOMDocument();
		$this->dom = $this->htmlParser->loadHtmlString($dom, $url, $html);

		return $this->htmlParser->getState();
	}

	/**
	 * Get the HTML docuemnt after it has been downloaded and parsed with downloadRecipe()
	 * @return ?DOMDocument The loaded HTML document or null if document could not be loaded successfully
	 */
	public function getDom(): ?DOMDocument {
		return $this->dom;
	}

	/**
	 * Fetch an HTML page from the internet
	 *
	 * @param string $url The URL of the page to fetch
	 *
	 * @throws ImportException If the given URL was not fetched
	 *
	 * @return string The content of the page as a plain string
	 */
	private function fetchHtmlPage(string $url): string {
		$host = parse_url($url);

		if (!$host) {
			throw new ImportException($this->l->t('Could not parse URL'));
		}

		$opt = [
			CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Linux x86_64; rv:129.0) Gecko/20100101 Firefox/129.0',
		];

		$langCode = $this->l->getLocaleCode();
		$langCode = str_replace('_', '-', $langCode);

		$headers = [
			'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/png,image/svg+xml,*/*;q=0.8',
			"Accept-Language: $langCode,en;q=0.5",
			'DNT: 1',
			// 'Alt-Used: www.thefooddictator.com',
			'Connection: keep-alive',
			'Cookie: nitroCachedPage=1',
			'Upgrade-Insecure-Requests: 1',
			'Sec-Fetch-Dest: document',
			'Sec-Fetch-Mode: navigate',
			'Sec-Fetch-Site: none',
			'Sec-Fetch-User: ?1',
			'Priority: u=0, i',
			'TE: trailers'
		];

		try {
			$this->downloadHelper->downloadFile($url, $opt, $headers);
		} catch (NoDownloadWasCarriedOutException $ex) {
			throw new ImportException($this->l->t('Exception while downloading recipe from %s.', [$url]), 0, $ex);
		}

		$status = $this->downloadHelper->getStatus();

		if ($status < 200 || $status >= 300) {
			throw new ImportException($this->l->t('Download from %s failed as HTTP status code %d is not in expected range.', [$url, $status]));
		}

		$html = $this->downloadHelper->getContent();

		try {
			$enc = $this->encodingGuesser->guessEncoding($html, $this->downloadHelper->getContentType());
			$html = $this->downloadEncodingHelper->encodeToUTF8($html, $enc);
		} catch (CouldNotGuessEncodingException $ex) {
			$this->logger->notice($this->l->t('Could not find a valid encoding when parsing %s.', [$url]));
		}

		return $html;
	}
}
