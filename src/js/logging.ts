// TODO: Switch to vuejs3-logger when we switch to Vue 3
import VueLogger from 'vuejs-logger';
import moment from '@nextcloud/moment';

const DEFAULT_LOG_LEVEL = 'info';
/**
 * For how many minutes the logging configuration is valid.
 */
const EXPIRY_MINUTES: number = 30;
// localStorage keys
const KEY_ENABLED = 'COOKBOOK_LOGGING_ENABLED';
const KEY_EXPIRY = 'COOKBOOK_LOGGING_EXPIRY';
const KEY_LOG_LEVEL = 'COOKBOOK_LOGGING_LEVEL';

/**
 * Checks if the logging configuration in local storage has expired.
 *
 * Since the expiry entry is added by us after the first run where
 * the enabled entry is detected, this only checks if it has been EXPIRY_MINUTES
 * since the first run, not EXPIRY_MINUTES since the user added the entry
 * This is a reasonable compromise to simplify what the user has to do to enable
 * logging. We don't want them to have to set up the expiry as well.
 * @param timestamp
 * @returns {string} True if the logging configuration has expired. False, otherwise
 */

const isExpired = (timestamp: string): boolean => {
	if (timestamp === null) {
		return false;
	}

	return moment().isAfter(parseInt(timestamp, 10));
};

/**
 * True, if logging is enabled. False, otherwise.
 */
const isEnabled = (): boolean => {
	const DEFAULT = false;
	const userValue = localStorage.getItem(KEY_ENABLED);
	let expiry = localStorage.getItem(KEY_EXPIRY);

	if (expiry && isExpired(expiry)) {
		localStorage.removeItem(KEY_ENABLED);
		localStorage.removeItem(KEY_EXPIRY);

		return DEFAULT;
	}

	if (!userValue) return false;

	// Detect the first load after the user has enabled logging
	// Set the expiry so the logging isn't enabled forever
	if (userValue !== null && expiry === null) {
		expiry = moment().add(EXPIRY_MINUTES, 'm').valueOf().toString();
		localStorage.setItem(KEY_EXPIRY, expiry as string);
	}

	// Local storage converts everything to string
	// Use JSON.parse to transform "false" -> false
	return JSON.parse(userValue) ?? DEFAULT;
};

/**
 * Runs the initial logging setup.
 * @param Vue
 */
export default function setupLogging(Vue) {
	const logLevel = localStorage.getItem(KEY_LOG_LEVEL) ?? DEFAULT_LOG_LEVEL;

	Vue.use(VueLogger, {
		isEnabled: isEnabled(),
		logLevel,
		stringifyArguments: false,
		showLogLevel: true,
		showMethodName: true,
		separator: '|',
		showConsoleColors: true,
	});

	Vue.$log.info(`Setting up logging with log level ${logLevel}`);
}

/**
 * Enables logging and sets log level to "DEBUG".
 */
export function enableLogging(): void {
	localStorage.setItem(KEY_ENABLED, 'true');
	localStorage.setItem(KEY_LOG_LEVEL, 'debug');
}
