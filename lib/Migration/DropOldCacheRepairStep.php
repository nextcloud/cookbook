<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OCA\Cookbook\Migration;

use OCP\Files\IRootFolder;
use OCP\Migration\IOutput;
use OCP\Migration\IRepairStep;

class DropOldCacheRepairStep implements IRepairStep {
	public function __construct(
		private IRootFolder $rootFolder,
	) {
	}

	#[\Override]
	public function getName(): string {
		return 'Drop old cache from Nextcloud root folder';
	}

	#[\Override]
	public function run(IOutput $output): void {
		if ($this->rootFolder->nodeExists('cookbook')) {
			$output->info('Dropping old cookbook storage path /cookbook from installation data.');
			$this->rootFolder->get('cookbook')->delete();
		}
	}
}
