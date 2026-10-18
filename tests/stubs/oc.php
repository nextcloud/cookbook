<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OC\Hooks {
	interface Emitter {
	}
}

namespace OC {
	class Image {
		public function __construct() {
		}

		/**
		 * @param bool|string $imagePath
		 * @return \GdImage|false
		 */
		public function loadFromFile($imagePath = false) {
		}

		public function fixOrientation(): bool {
		}

		public function resize(int $maxSize): bool {
		}

		public function centerCrop(int $size = 0): bool {
		}

		public function data(): ?string {
		}
	}
}
