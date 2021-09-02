<?php

namespace OCA\Cookbook\Service;

use DOMDocument;
use OCA\Cookbook\Exception\ImportException;
use OCA\Cookbook\Helper\HTMLFilter\AbstractHtmlFilter;
use OCA\Cookbook\Helper\HTMLFilter\HtmlEntityDecodeFilter;
use OCA\Cookbook\Helper\HtmlToDomParser;
use OCP\IL10N;
use OCP\ILogger;

class HtmlDownloadService {
	
	/**
	 * @var array
	 */
	private $htmlFilters;
	
	/**
	 * @var ILogger
	 */
	private $logger;
	
	/**
	 * @var IL10N
	 */
	private $l;
	
	/**
	 * @var HtmlToDomParser
	 */
	private $htmlParser;
	
	/**
	 * @var DOMDocument
	 */
	private $dom;
	
	public function __construct(HtmlEntityDecodeFilter $htmlEntityDecodeFilter,
		ILogger $logger, IL10N $l10n, HtmlToDomParser $htmlParser) {
		$this->htmlFilters = [ $htmlEntityDecodeFilter ];
		$this->logger = $logger;
		$this->l = $l10n;
		$this->htmlParser = $htmlParser;
	}
	
	/**
	 * Download a recipe URL and extract the JSON from it
	 *
	 * The return value is one of the constants of the class PARSE_SUCCESS, PARSE_WARNING,
	 * PARSE_ERROR, or PARSE_FATAL_ERROR.
	 *
	 * @param int The indicator if the HTML page was correctly parsed
	 * @return array The included JSON data array, unfiltered
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
	 * @return \DOMDocument The loaded HTML document or null if document could not be loaded successfully
	 */
	public function getDom(): ?\DOMDocument {
		return $this->dom;
	}

	/**
	 * Fetch a HTML page from the internet
	 * @param string $url The URL of the page to fetch
	 * @throws ImportException If the given URL was not fetched
	 * @return string The content of the page as a plain string
	 */
	private function fetchHtmlPage(string $url): string {
		$host = parse_url($url);
		
		if (!$host) {
			throw new ImportException($this->l->t('Could not parse URL'));
		}
		
		$opts = [
			"http" => [
				"method" => "GET",
				"header" => "User-Agent: Nextcloud Cookbook App"
			]
		];
		
		$context = stream_context_create($opts);
		
		$html = file_get_contents($url, false, $context);
		
		if ($html === false) {
			throw new ImportException($this->l->t('Could not parse HTML code for site {url}', $url));
		}
		
		return $html;
	}
	
}
