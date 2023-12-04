<?php

namespace OCA\Cookbook\Helper;

use OCA\Cookbook\Exception\NoDownloadWasCarriedOutException;
use OCP\IL10N;

/**
 * This class is mainly a wrapper for cURL and its PHP extension to download files/content from the internet.
 */
class DownloadHelper {
	/**
	 * Flag that indicates if a download has successfully carried out
	 *
	 * @var bool
	 */
	private $downloaded;
	/**
	 * The content of the last download
	 *
	 * @var string
	 */
	private $content;
	/**
	 * The array of the headers of the last download
	 *
	 * @var array
	 */
	private $headers;
	/**
	 * The HTTP status of the last download
	 *
	 * @var int
	 */
	private $status;

	/** @var IL10N */
	private $l;

	public function __construct(
		IL10N $l
	) {
		$this->downloaded = false;
		$this->l = $l;
		$this->headers = [];
		$this->status = 0;
		$this->content = '';
	}

	/**
	 * Download a file from the internet.
	 *
	 * The content of the file will be stored in RAM, so beware not to download huge files.
	 *
	 * @param string $url The URL of the file to fetch
	 * @param array $options Options to pass on for curl. This allows to fine-tune the transfer.
	 * @throws NoDownloadWasCarriedOutException if the download fails for some reason
	 */
	public function downloadFile(string $url, array $options = []): void {
		$this->downloaded = false;

		$ch = curl_init($url);

		$hp = tmpfile();

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_WRITEHEADER, $hp);
		curl_setopt_array($ch, $options);

		$ret = curl_exec($ch);
		if ($ret === false) {
			$ex = new NoDownloadWasCarriedOutException($this->l->t('Downloading of a file failed returned the following error message: %s', [curl_error($ch)]));
			fclose($hp);
			curl_close($ch);
			throw $ex;
		}

		$this->content = $ret;
		$this->status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

		fseek($hp, 0);
		$this->headers = [];
		for ($buffer = fgets($hp); $buffer !== false; $buffer = fgets($hp)) {
			$this->headers[] = $buffer;
		}

		fclose($hp);
		curl_close($ch);
		$this->downloaded = true;
	}

	/**
	 * Get the content downloaded in the last run.
	 *
	 * Note: You must first trigger the download using downloadFile method.
	 *
	 * @return string The content of the downloaded file
	 *
	 * @throws NoDownloadWasCarriedOutException if there was no successful download carried out before calling this method.
	 */
	public function getContent(): string {
		if ($this->downloaded) {
			return $this->content;
		} else {
			throw new NoDownloadWasCarriedOutException();
		}
	}

	/**
	 * Get the Content-Type header from the last download run.
	 *
	 * Note: You must first trigger the download using downloadFile method.
	 *
	 * @return ?string The content of the Content-Type header or null if no Content-Type header was found
	 * @throws NoDownloadWasCarriedOutException if there was no successful download carried out before calling this method.
	 */
	public function getContentType(): ?string {
		if (! $this->downloaded) {
			throw new NoDownloadWasCarriedOutException();
		}

		foreach ($this->headers as $s) {
			$parts = explode(':', $s, 2);
			if (strtolower(trim($parts[0])) === 'content-type') {
				return trim($parts[1]);
			}
		}

		return null;
	}

	/**
	 * Get the HTTP status code from the last download run.
	 *
	 * Note: You must first trigger the download using downloadFile method.
	 *
	 * @return int The HTTP status code
	 *
	 * @throws NoDownloadWasCarriedOutException if there was no successful download carried out before calling this method.
	 */
	public function getStatus(): int {
		if (! $this->downloaded) {
			throw new NoDownloadWasCarriedOutException();
		}

		return $this->status;
	}
}
