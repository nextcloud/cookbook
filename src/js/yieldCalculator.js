function isValidIngredientSyntax(ingredient) {
    /*
        Explanation of ingredientSyntaxRegExp:
	    ^: Start of string
	    (?:\d+(?:\.\d+)?|\.\d+): Non-capturing group that matches either a positive float value or a positive integer value. The first alternative matches one or more digits, followed by an optional decimal part consisting of a dot and one or more digits. The second alternative matches a decimal point followed by one or more digits.
	    (?:\s.+$|\s\S+$): Non-capturing group that matches a whitespace character followed by any character with unlimited length or any special character with unlimited length. The first alternative matches a whitespace character followed by any character(s) until the end of the string. The second alternative matches a whitespace character followed by any non-whitespace character(s) until the end of the string.
	    $: End of string
    */
    const ingredientSyntaxRegExp = /^(?:\d+(?:\.\d+)?(?:\/\d+)?)\s?.*$/
    // Regular expression to match all possible fractions within a string
    const ingredientFractionRegExp = /\b\d+\/\d+\b/g
    /*
        Explanation of ingredientMultipleSeperatorsRegExp:
        /^                - Start of the string
        -?                - Matches an optional minus sign
        \d+               - Matches one or more digits
        (?:[.,]\d+){2,}   - Non-capturing group that matches a separator (.,) followed by one or more digits.
                            The {2,} quantifier ensures that there are at least two occurrences of this pattern.
        .*                - Matches any characters (except newline) zero or more times.
    */
    const ingredientMultipleSeperatorsRegExp = /^-?\d+(?:[.,]\d+){2,}.*/

    return (
        ingredientSyntaxRegExp.test(ingredient) &&
        !ingredientFractionRegExp.test(ingredient) &&
        !ingredientMultipleSeperatorsRegExp.test(ingredient)
    )
}

function isIngredientsArrayValid(ingredients) {
	return ingredients.every(isValidIngredientSyntax)
}

function recalculateIngredients(ingredients, currentYield, originalYield) {
	return ingredients.map(
		(ingredient, index) => {
			if (isValidIngredientSyntax(ingredient)) {
				// For some cases, where the unit is not separated from the amount: 100g cheese
				const possibleUnit = ingredient
					.split(" ")[0]
					.replace(/[^a-zA-Z]/g, "")
				const amount = parseFloat(
					ingredients[index].split(" ")[0]
				)
				const unitAndIngredient = ingredient
					.split(" ")
					.slice(1)
					.join(" ")
				let newAmount = (amount / originalYield) * currentYield
				newAmount = newAmount.toFixed(2).replace(/[.]00$/, "")

				return `${newAmount}${possibleUnit} ${unitAndIngredient}`
			}

			const factor = (currentYield/originalYield)
			const prefix= ((f) => {
				if(f === 1){
					return ''
				} 
				return `${f.toFixed(2)}x `
			})(factor)
			return `${prefix}${ingredient}`
		}
	)
}

export default {
	isValidIngredientSyntax,
	isIngredientsArrayValid,
	recalculateIngredients
}
