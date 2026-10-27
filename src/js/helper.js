// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

import { showSimpleAlertModal } from 'cookbook/js/modals';

/**
 * Clamps val between the minimum min and maximum max value.
 * @param val The value to be clamped between min and max
 * @param min The upper limit
 * @param max The lower limit
 * @returns {number} min if val is <= min, max if val is >= max and val if min <= val <= max.
 */
function clamp(val, min, max) {
    return Math.min(max, Math.max(min, val));
}

// Check if the two urls point to the same item instance
function isSameItemInstance(url1, url2) {
    if (url1 === url2) {
        return true; // Obviously true if the routes are the same
    }
    const comps1 = url1.split('/');
    const comps2 = url2.split('/');
    if (comps1.length < 2 || comps2.length < 2) {
        return false; // Just a failsafe, this should never happen
    }
    // If the items are different, then the item instance cannot be
    // the same either
    if (comps1[1] !== comps2[1]) {
        return false;
    }
    if (comps1.length < 3 || comps2.length < 3) {
        // ID is the third url component, so can't be the same instance if
        // either of the urls have less than three components
        return false;
    }
    return comps1[2] === comps2[2];
}

/**
 * A simple function to sanitize HTML tags.
 * @param {string} text Input string
 * @returns {string}
 */

function escapeHTML(text) {
    return text.replace(
        /["&'<>]/g,
        (a) =>
            ({
                '&': '&amp;',
                '"': '&quot;',
                "'": '&apos;',
                '<': '&lt;',
                '>': '&gt;',
            })[a],
    );
}

// Fix the decimal separator for languages that use a comma instead of dot
// deprecated
function fixDecimalSeparator(value, io) {
    // value is the string value of the number to process
    // io is either 'i' as in input or 'o' as in output
    if (!value) {
        return '';
    }
    if (io === 'i') {
        // Check if it's an American number where a comma precedes a dot
        //  e.g. 12,500.25
        if (value.indexOf('.') > value.indexOf(',')) {
            return value.replace(',', '');
        }
        return value.replace(',', '.');
    }
    if (io === 'o') {
        return value.toString().replace('.', ',');
    }
    return '';
}

// This will replace the PHP function nl2br in Vue components
// deprecated
function nl2br(text) {
    return text.replace(/\n/g, '<br />');
}

// A simple function that converts a MySQL datetime into a timestamp.
// deprecated
function getTimestamp(date) {
    if (date) {
        return new Date(date);
    }
    return null;
}

let router;

function useRouter(_router) {
    router = _router;
}
// Push a new URL to the router, essentially navigating to that page.
function goTo(url) {
    router.push(url);
}

// Notify the user if notifications are allowed
// deprecated
function notify(title, options) {
    if (!('Notification' in window)) {
        return;
    }
    if (Notification.permission === 'granted') {
        const notification = new Notification(title, options);
    } else if (Notification.permission !== 'denied') {
        Notification.requestPermission((permission) => {
            if (!('permission' in Notification)) {
                Notification.permission = permission;
            }
            if (permission === 'granted') {
                const notification = new Notification(title, options);
            } else {
                showSimpleAlertModal(title, '');
            }
        });
    }
}

export default {
    clamp,
    isSameItemInstance,
    escapeHTML,
    fixDecimalSeparator,
    nl2br,
    getTimestamp,
    useRouter,
    goTo,
    notify,
};
