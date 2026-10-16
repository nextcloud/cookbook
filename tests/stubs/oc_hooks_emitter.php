<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OC\Hooks;

class Emitter {
	public function emit(string $class, string $value, array $option) {
	}

	/**
	 * @param \Closure $closure
	 */
	public function listen(string $class, string $value, $closure) {
	}
}
