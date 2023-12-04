// TODO: Switch to vuejs3-logger when we switch to Vue 3
import VueLogger from 'vuejs-logger';
import moment from '@nextcloud/moment';

const DEFAULT_LOG_LEVEL = 'info';
// How many minutes the logging configuration is valid for
const EXPIRY_MINUTES = 30;
// localStorage keys
const KEY_ENABLED = 'COOKBOOK_LOGGING_ENABLED';
const KEY_EXPIRY = 'COOKBOOK_LOGGING_EXPIRY';
const KEY_LOG_LEVEL = 'COOKBOOK_LOGGING_LEVEL';

// Check if the logging configuration in local storage has expired
//
// Since the expiry entry is added by us after the first run where
// the enabled entry is detected, this only checks if it has been EXPIRY_MINUTES
// since the first run, not EXPIRY_MINUTES since the user added the entry
// This is a reasonable comprimise to simplify what the user has to do to enable
// logging. We don't want them to have to setup the expiry as well
const isExpired = (timestamp) => {
    if (timestamp === null) {
        return false;
    }

    return moment().isAfter(parseInt(timestamp, 10));
};

const isEnabled = () => {
    const DEFAULT = false;
    const userValue = localStorage.getItem(KEY_ENABLED);
    const expiry = localStorage.getItem(KEY_EXPIRY);

    // Detect the first load after the user has enabled logging
    // Set the expiry so the logging isn't enabled forever
    if (userValue !== null && expiry === null) {
        localStorage.setItem(
            KEY_EXPIRY,
            moment().add(EXPIRY_MINUTES, 'm').valueOf(),
        );
    }

    if (isExpired(expiry)) {
        localStorage.removeItem(KEY_ENABLED);
        localStorage.removeItem(KEY_EXPIRY);

        return DEFAULT;
    }

    // Local storage converts everything to string
    // Use JSON.parse to transform "false" -> false
    return JSON.parse(userValue) ?? DEFAULT;
};

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

export function enableLogging() {
    localStorage.setItem(KEY_ENABLED, true);
}
