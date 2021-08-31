<?php

namespace OCA\Cookbook\Service;

use OCA\Cookbook\Exception\ImportException;
use OCA\Cookbook\Helper\HTMLFilter\AbstractHtmlFilter;
use OCA\Cookbook\Helper\HTMLFilter\HtmlEntityDecodeFilter;
use OCP\IL10N;
use OCP\ILogger;

class HtmlDownloadService {
	
	/**
	 * Indicates the parsing was successfully terminated
	 * @var integer
	 */
	public const PARSING_SUCCESS = 0;
	/**
	 * Indicates the parsing terminated with warnings
	 * @var integer
	 */
	public const PARSING_WARNING = 1;
	/**
	 * Indicates the parsing terminated with an error
	 * @var integer
	 */
	public const PARSING_ERROR = 2;
	/**
	 * Indicates that the parsing terminated with a fatal error
	 * @var integer
	 */
	public const PARSING_FATAL_ERROR = 3;
	
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
	 * @var \DOMDocument
	 */
	private $dom;
	
	/**
	 * The parsed HTML content
	 * @var string
	 */
	private $html;
	
	public function __construct(HtmlEntityDecodeFilter $htmlEntityDecodeFilter,
		ILogger $logger, IL10N $l10n) {
		$this->htmlFilters = [ $htmlEntityDecodeFilter ];
		$this->logger = $logger;
		$this->l = $l10n;
		$this->dom = null;
		$this->html = null;
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
		// Reset in case of exception
		$this->dom = null;
		$this->html = null;
		
		$this->html = $this->fetchHtmlPage($url);
		
		// Filter the HTML code
		/** @var AbstractHtmlFilter $filter */
		foreach ($this->htmlFilters as $filter) {
			$filter->apply($this->html);
		}
		
		return $this->loadHtmlString($url);
	}
	
	/**
	 * Get the HTML docuemnt after it has been downloaded and parsed with downloadRecipe()
	 * @return \DOMDocument The loaded HTML document or null if document could not be loaded successfully
	 */
	public function getDom(): ?\DOMDocument {
		return $this->dom;
	}

	/**
	 * @return string The parsed HTML or null if no parsing was successful
	 */
	public function getHtml(): ?string {
		return $this->html;
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
	
	/**
	 * @param string $url The URL of the parsed recipe
	 * @throws ImportException If the parsing of the HTML page failed completely
	 * @return int Indicator of the parsing state
	 */
	private function loadHtmlString(string $url): int {
		$this->dom = new \DOMDocument();
		
		$libxml_previous_state = libxml_use_internal_errors(true);
		
		try {
			$parsedSuccessfully = $this->dom->loadHTML($this->html);
			
			// Error handling
			$errors = libxml_get_errors();
			$result = $this->checkXMLErrors($errors, $url);
			libxml_clear_errors();
			
			if (!$parsedSuccessfully) {
				throw new ImportException($this->l->t('Parsing of HTML failed.'));
			}
		} finally {
			libxml_use_internal_errors($libxml_previous_state);
		}
		
		return $result;
	}
	
	/**
	 * Compress the xml errors to small mount of log messages
	 *
	 * The return value indicates what the most critical value was.
	 * The value can be PARSING_SUCCESS, PARSING_WARNING, PARSING_ERROR, or PARSING_FATAL_ERROR.
	 *
	 * @param array $errors The array of all parsed errors
	 * @param string $url The parsed URL
	 * @return int Indicator what the most severe issue was
	 */
	private function checkXMLErrors(array $errors, string $url): int {
		$error_counter = [];
		$by_error_code = [];
		
		foreach ($errors as $error) {
			if (array_key_exists($error->code, $error_counter)) {
				$error_counter[$error->code] ++;
			} else {
				$error_counter[$error->code] = 1;
				$by_error_code[$error->code] = $error;
			}
		}
		
		$return = self::PARSING_SUCCESS;
		
		/**
		 * @var int $code
		 * @var int $count
		 */
		foreach ($error_counter as $code => $count) {
			/** @var \LibXMLError $error */
			$error = $by_error_code[$code];
			
			switch ($error->level) {
				case LIBXML_ERR_WARNING:
					$error_message = $this->l->t('Warning %u occurred %u times while parsing %s.', [$error->code, $count, $url]);
					$return = max($return, self::PARSING_WARNING);
					break;
				case LIBXML_ERR_ERROR:
					$error_message = $this->l->t('Error %u occurred %u times while parsing %s.', [$error->code, $count, $url]);
					$return = max($return, self::PARSING_ERROR);
					break;
				case LIBXML_ERR_FATAL:
					$error_message = $this->l->t('Fatal error %u occurred %count$u times while parsing %s.', [$error->code, $count, $url]);
					$return = max($return, self::PARSING_FATAL_ERROR);
					break;
				default:
					throw new \Exception($this->l->t('Unsupported error level during parsing of XML output.'));
			}
			
			$last_occurence = $this->l->t('Last time it occurred in line %u and column %u', [$error->line, $error->column]);
			
			$error_message = "libxml: $error_message $last_occurence: " . $error->message;
			$this->logger->warning($error_message);
		}
		
		return $return;
	}
}
