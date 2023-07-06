import api from "cookbook/js/api-interface"

import { generateUrl } from "@nextcloud/router"

const baseUrl = generateUrl("apps/cookbook")

function extractAllRecipeLinkIds(content) {
    const re = /(?:^|[^#])#r\/([0-9]+)/g
    let ret = []
    let matches
    for (
        matches = re.exec(content);
        matches !== null;
        matches = re.exec(content)
    ) {
        ret.push(matches[1])
    }

    // Make the ids unique, see https://stackoverflow.com/a/14438954/882756
    function onlyUnique(value, index, self) {
        return self.indexOf(value) === index
    }
    ret = ret.filter(onlyUnique)

    return ret
}

async function getRecipesFromLinks(linkIds) {
    return Promise.all(
        linkIds.map(async (x) => {
            let recipe
            try {
                recipe = await api.recipes.get(x)
            } catch (ex) {
                recipe = null
            }
            return recipe
        }),
    )
}

function cleanUpRecipeList(recipes) {
    return recipes.filter((r) => r !== null).map((x) => x.data)
}

function getRecipeUrl(id) {
    return `${baseUrl}/#/recipe/${id}`
}

function insertMarkdownLinks(content, recipes) {
    let ret = content
    recipes.forEach((r) => {
        const { id } = r

        // Replace link urls in dedicated links (like [this example](#r/123))
        ret = ret.replace(`](${id})`, `](${getRecipeUrl(id)})`)

        // Replace plain references with recipe name
        const rePlain = RegExp(
            `(^|\\s|[,._+&?!-])#r/${id}($|\\s|[,._+&?!-])`,
            "g",
        )
        // const re = /(^|\s|[,._+&?!-])#r\/(\d+)(?=$|\s|[.,_+&?!-])/g
        ret = ret.replace(
            rePlain,
            `$1[${r.name} (\\#r/${id})](${getRecipeUrl(id)})$2`,
        )
    })
    return ret
}

async function normalizeNamesMarkdown(content) {
    // console.log(`Content: ${content}`)
    const linkIds = extractAllRecipeLinkIds(content)
    let recipes = await getRecipesFromLinks(linkIds)
    recipes = cleanUpRecipeList(recipes)
    // console.log("List of recipes", recipes)

    const markdown = insertMarkdownLinks(content, recipes)
    // console.log("Formatted markdown:", markdown)

    return markdown
}

export default normalizeNamesMarkdown
