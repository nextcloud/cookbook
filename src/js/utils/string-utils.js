/**
 * Normalizes a string by removing spaces, accents, replacing special characters that look similar to others
 * (e.g., ł and l or ø and o), depending on the selected options.
 * @param str The string to normalize.
 * @param toLowercase Convert all characters to lowercase.
 * @param removeSpaces Remove spaces in string.
 * @param removeDiacritics Remove all diacritics (accents).
 * @param substituteLetters Substitute some letters by similar looking letters.
 * @returns {string} Normalized string value.
 */
// eslint-disable-next-line import/prefer-default-export
export function normalize(
    str,
    toLowercase = true,
    removeSpaces = true,
    removeDiacritics = true,
    substituteLetters = true,
) {
    let r = ` ${str}`.slice(1);
    if (toLowercase) {
        r = r.toLowerCase();
    }
    if (removeDiacritics) {
        r = r.normalize('NFD').replace(/\p{Diacritic}/gu, '');
    }
    if (removeSpaces) {
        r = r.replace(/\s/g, '');
    }
    if (substituteLetters) {
        r = r.replace(/æ/g, 'ae');
        r = r.replace(/ç/g, 'c');
        r = r.replace(/ę/g, 'e');
        r = r.replace(/į/g, 'i');
        r = r.replace(/ł/g, 'l');
        r = r.replace(/ñ/g, 'n');
        r = r.replace(/ø/g, 'o');
        r = r.replace(/œ/g, 'oe');
        r = r.replace(/ß/g, 'ss');
    }
    return r;
}
