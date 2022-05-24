<?php

namespace OCA\Cookbook\Helper;

use Exception;
use OCP\IL10N;
use DOMDocument;
use LibXMLError;
use OCP\ILogger;
use OCA\Cookbook\Exception\ImportException;

/**
 * This class allows to parse an HTML document into a DOMDocument.
 *
 * Its main purpose is to handle the very verbose libxml extension better.
 */
class HtmlToDomParser {
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
	 * @var int
	 */
	private $state;

	/**
	 * @var ILogger
	 */
	private $logger;

	/**
	 * @var IL10N
	 */
	private $l;

	public function __construct(ILogger $logger, IL10N $il10n) {
		$this->logger = $logger;
		$this->l = $il10n;
	}

	/**
	 * Parse an HTML file logging any error in the syntax
	 *
	 * @param DOMDocument $dom The dom document to use. Just give a new document in doubt.
	 * @param string $url The URL of the parsed recipe
	 * @param string $html The string representation of the HTML file to parse
	 * @throws ImportException If the parsing of the HTML page failed completely
	 * @return DOMDocument The parsed document
	 */
	public function loadHtmlString(DOMDocument $dom, string $url, string $html): DOMDocument {
		$libxml_previous_state = libxml_use_internal_errors(true);
		
		try {
			$parsedSuccessfully = $dom->loadHTML($html);
			
			// Error handling
			$errors = libxml_get_errors();
			try {
				$this->checkXMLErrors($errors, $url);
			} catch (Exception $ex) {
				throw new ImportException($this->l->t('Parsing of HTML failed.'), null, $ex);
			}
			libxml_clear_errors();
			
			if (!$parsedSuccessfully) {
				throw new ImportException($this->l->t('Parsing of HTML failed.'));
			}
		} finally {
			libxml_use_internal_errors($libxml_previous_state);
		}
		
		return $dom;
	}

	/**
	 * Get the error state of the last parsing routine.
	 *
	 * See the constants in the class for possible values.
	 *
	 * @return int
	 */
	public function getState(): int {
		return $this->state;
	}

	/**
	 * Compress the xml errors to small mount of log messages
	 *
	 * The return value indicates what the most critical value was.
	 * The value can be PARSING_SUCCESS, PARSING_WARNING, PARSING_ERROR, or PARSING_FATAL_ERROR.
	 *
	 * @param array $errors The array of all parsed errors
	 * @param string $url The parsed URL
	 * @throws \Exception
	 */
	private function checkXMLErrors(array $errors, string $url): void {
		$grouped = $this->groupErrors($errors);
		
		$this->state = self::PARSING_SUCCESS;

		$this->logAllErrors($grouped, $url);
	}

	/**
	 * Group the errors by the error code
	 *
	 * This will iterate over all errors and count for each error code how often the code is present in the errors.
	 * Also the first error of each code will be stored.
	 *
	 * The returned array will contain an entry for each found error code.
	 * The error code is the corresponding key in the array.
	 *
	 * Each entry in the array is itself an array containing the following entries:
	 * First, there is the key `count` that contains the number of occurrences of the corresponding error code.
	 * Second, there is the key `first` that contains the first error that was found with this error code.
	 * Third, the `level` entry contains the severity level of the error reported.
	 *
	 * @param array $errors The errors to parse
	 * @return array The grouped error codes
	 */
	private function groupErrors(array $errors): array {
		$ret = [];

		/**
		 * @var LibXMLError $error
		 */
		foreach ($errors as $error) {
			if (isset($ret[$error->code])) {
				$ret[$error->code]['count'] ++;
			} else {
				$ret[$error->code] = [
					'count' => 1,
					'first' => $error,
					'level' => $error->level,
				];
			}
		}

		return $ret;
	}
	
	/**
	 * Log the found error groups to the NC core error logger.
	 *
	 * As a side effect, the property $state will be updated to the most severe error found.
	 *
	 * @param array $groupedErrors The grouped errors as defined in groupErrors
	 * @param string $url The URL to import
	 * @return void
	 * @throws \Exception
	 */
	private function logAllErrors(array $groupedErrors, string $url): void {
		foreach ($groupedErrors as $code => $group) {
			switch ($group['level']) {
				case LIBXML_ERR_WARNING:
					$this->logWarning($code, $group, $url);
					break;
				case LIBXML_ERR_ERROR:
					$this->logError($code, $group, $url);
					break;
				case LIBXML_ERR_FATAL:
					$this->logFatalError($code, $group, $url);
					break;
				default:
				throw new \Exception($this->l->t('Unsupported error level during parsing of XML output.'));
			}
		}
	}

	private function logWarning(int $code, array $group, string $url): void {
		$msg = $this->l->t('Warning %u occurred %u times while parsing %s.', [$code, $group['count'], $url]);
		$this->logger->notice($this->formatError($msg, $group['first']));
		$this->state = max($this->state, self::PARSING_WARNING);
	}

	
	private function logError(int $code, array $group, string $url): void {
		$msg = $this->l->t('Error %u occurred %u times while parsing %s.', [$code, $group['count'], $url]);
		$this->logger->warning($this->formatError($msg, $group['first']));
		$this->state = max($this->state, self::PARSING_ERROR);
	}

	private function logFatalError(int $code, array $group, string $url): void {
		$msg = $this->l->t('Fatal error %u occurred %u times while parsing %s.', [$code, $group['count'], $url]);
		$this->logger->error($this->formatError($msg, $group['first']));
		$this->state = max($this->state, self::PARSING_FATAL_ERROR);
	}

	private function formatError(string $errorMessage, LibXMLError $error): string {
		$firstOccurence = $this->l->t('First time it occurred in line %u and column %u', [$error->line, $error->column]);
		$msg = "libxml: $errorMessage $firstOccurence: {$error->message}";
		return $msg;
	}
}
