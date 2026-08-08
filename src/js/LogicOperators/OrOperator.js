// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

import BinaryOperator from './BinaryOperator';

/**
 * Implementation for the OR operator.
 * @extends BinaryOperator
 */
class OrOperator extends BinaryOperator {
    get toString() {
        return 'OR';
    }

    /**
     * Applies the OR operation.
     * @param {boolean} result - The result accumulated so far.
     * @param {boolean} current - The current value to apply.
     * @returns {boolean} The result after applying the OR operation.
     */

    apply(result, current) {
        return result || current;
    }
}

export default OrOperator;
