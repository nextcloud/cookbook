/*
    The ingredientFractionRegExp is used to identify fractions in the string.
    This is used to exclude strings that contain fractions from being valid.
*/
const fractionRegExp = /^((\d+\s+)?(\d+)\s*\/\s*(\d+)).*/

function isValidIngredientSyntax(ingredient) {
    /*
        The ingredientSyntaxRegExp checks whether the ingredient string starts with a number, 
        possibly followed by a fractional part or a fraction. Then there should be a space
        and then any sequence of characters.
    */
    const ingredientSyntaxRegExp = /^(?:\d+(?:\.\d+)?(?:\/\d+)?)\s?.*$/

    /*
        The ingredientMultipleSeperatorsRegExp is used to check whether the string contains
        more than one separators (.,) after a number. This is used to exclude strings that 
        contain more than one separator from being valid.
    */
    const ingredientMultipleSeperatorsRegExp = /^-?\d+(?:[.,]\d+){2,}.*/

    return (
        fractionRegExp.test(ingredient) ||
        (ingredientSyntaxRegExp.test(ingredient) &&
            !ingredientMultipleSeperatorsRegExp.test(ingredient))
    )
}

function isIngredientsArrayValid(ingredients) {
    return ingredients.every(isValidIngredientSyntax)
}

function recalculateIngredients(ingredients, currentYield, originalYield) {
    return ingredients.map((ingredient) => {
        const matches = ingredient.match(fractionRegExp)

        if (matches) {
            const [
                ,
                fractionMatch,
                wholeNumberPartRaw,
                numeratorRaw,
                denominatorRaw,
            ] = matches
            const wholeNumberPart = wholeNumberPartRaw
                ? parseInt(wholeNumberPartRaw, 10)
                : 0
            const numerator = parseInt(numeratorRaw, 10)
            const denominator = parseInt(denominatorRaw, 10)

            const decimalAmount = wholeNumberPart + numerator / denominator
            let newAmount = (decimalAmount / originalYield) * currentYield
            newAmount = newAmount.toFixed(2).replace(/[.]00$/, "")

            const newIngredient = ingredient.replace(fractionMatch, newAmount)
            return newIngredient
        }

        if (isValidIngredientSyntax(ingredient)) {
            const possibleUnit = ingredient
                .split(" ")[0]
                .replace(/[^a-zA-Z]/g, "")
            const amount = parseFloat(
                ingredient.split(" ")[0].replace(",", "."),
            )
            const unitAndIngredient = ingredient.split(" ").slice(1).join(" ")

            let newAmount = (amount / originalYield) * currentYield
            newAmount = newAmount.toFixed(2).replace(/[.]00$/, "")

            return `${newAmount}${possibleUnit} ${unitAndIngredient}`
        }

        return ingredient
    })
}

export default {
    isValidIngredientSyntax,
    isIngredientsArrayValid,
    recalculateIngredients,
}
