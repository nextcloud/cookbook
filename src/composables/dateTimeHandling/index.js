import moment from '@nextcloud/moment';

/**
 * The schema.org standard requires the dates formatted as Date (https://schema.org/Date)
 * or DateTime (https://schema.org/DateTime). This follows the ISO 8601 standard.
 * @param dt
 * @returns {null|moment.Moment|*}
 */
export function parseDateTime(dt) {
    if (!dt) return null;
    const date = moment(dt, moment.ISO_8601);
    if (!date.isValid()) {
        return null;
    }
    return date;
}
