/**
 * Mode for searching/matching for strings within other strings.
 */
enum SearchMode {
	/**
	 * Both string must be identical
	 */
	Exact = 'exact',
	/**
	 * The string should be contained in the other string.
	 */
	MatchSubstring = 'matchSubstring',
	/**
	 * Perform a fuzzy search of the string within the other string.
	 */
	Fuzzy = 'fuzzy',
}

export default SearchMode;
