<?php

namespace OCA\Cookbook\Helper;

class DownloadEncodingHelper {
	public function encodeToUTF8(string $data, string $encoding): string {
		$data = iconv($encoding, 'UTF-8', $data);
		return $data;
	}
}
