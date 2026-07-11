<?php

namespace OCA\Cookbook\Helper;

use OCA\Cookbook\Exception\NoDownloadWasCarriedOutException;
use OCP\Http\Client\IClientService;
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

	/** @var IClientService */
	private $clientService;

	public function __construct(
		IL10N $l,
		IClientService $clientService,
	) {
		$this->downloaded = false;
		$this->l = $l;
		$this->headers = [];
		$this->status = 0;
		$this->content = '';
		$this->clientService = $clientService;
	}

	/**
	 * Download a file from the internet.
	 *
	 * The content of the file will be stored in RAM, so beware not to download huge files.
	 *
	 * @param string $url The URL of the file to fetch
	 * @param array $options Options to pass on for curl. This allows to fine-tune the transfer.
	 * @param array $headers Additinal headers to be sent to the server
	 * @throws NoDownloadWasCarriedOutException if the download fails for some reason
	 */
	public function downloadFile(string $url, array $options = [], array $headers = []): void {
		$this->downloaded = false;

		$client = $this->clientService->newClient();

		try {
			$response = $client->get($url, [
				'headers' => $headers,
				'nextcloud' => ['allow_local_address' => false], // default; explicit for clarity
				// do NOT enable allow_local_address
			] + $options);
		} catch (\OCP\Http\Client\LocalServerException $e) {
			throw new NoDownloadWasCarriedOutException($this->l->t('URL resolves to a local/internal address'));
		} catch (\Exception $e) {
			throw new NoDownloadWasCarriedOutException($this->l->t('Error occurred while downloading the file: %s', [$e->getMessage()]));
		}

		$this->content = $response->getBody();
		$this->status = $response->getStatusCode();
		$this->headers = $response->getHeaders();

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
		if (!$this->downloaded) {
			throw new NoDownloadWasCarriedOutException();
		}

		return $this->headers['content-type'][0] ?? null;
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
		if (!$this->downloaded) {
			throw new NoDownloadWasCarriedOutException();
		}

		return $this->status;
	}
}
