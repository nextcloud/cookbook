# TODO Tasks

- Make categories and keywords separate tables refferring to ids only
- Add indices to schemata
- Category is 1:n mappiung with recipe -> make column out of it
- Refactor Service files
	- Use Polymophism for checks
	- Separate checks from file operations
- Remove non-used controller
- Usage of PHP objects instead of arrays
- Test cases
- Look for deprecated methods
- Truncate DB to restart from scratch
- Export DB for debugging?
- Abort Search for JSON after fixed numer/time to avoid timeout
- Make a single Service rule all the others (adding/removing/changing causes JSON + DB be updated)
- Refactor database: Why mutiple rows for different users of the very same file? 

# Bugs found
- When no category is given for one recipe, the total number of recipes is falsely calculated
