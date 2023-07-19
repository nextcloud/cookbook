import Vue from "vue"
import axios from "@nextcloud/axios"

import { generateUrl } from "@nextcloud/router"

const instance = axios.create()

const baseUrl = `${generateUrl("apps/cookbook")}/webapp`

// Add a debug log for every request
instance.interceptors.request.use((config) => {
    Vue.$log.debug(
        `[axios] Making "${config.method}" request to "${config.url}"`,
        config,
    )
    const contentType = config.headers["Content-Type"]
    if (contentType && !["application/json", "text/json"].includes(contentType)) {
        Vue.$log.warn(
            `[axios] Request to "${config.url}" is using Content-Type "${contentType}", not JSON`,
        )
    }
    return config
})

instance.interceptors.response.use(
    (response) => {
        Vue.$log.debug("[axios] Received response", response)
        return response
    },
    (error) => {
        Vue.$log.warn("[axios] Received error", error)
        return Promise.reject(error)
    },
)

axios.defaults.headers.common.Accept = "application/json"

function createNewRecipe(recipe) {
    return instance.post(`${baseUrl}/recipes`, recipe)
}

function getRecipe(id) {
    return instance.get(`${baseUrl}/recipes/${id}`)
}

function getAllRecipes() {
    return instance.get(`${baseUrl}/recipes`)
}

function getAllRecipesOfCategory(categoryName) {
    return instance.get(`${baseUrl}/category/${categoryName}`)
}

function getAllRecipesWithTag(tags) {
    return instance.get(`${baseUrl}/tags/${tags}`)
}

function searchRecipes(search) {
    return instance.get(`${baseUrl}/search/${search}`)
}

function updateRecipe(id, recipe) {
    return instance.put(`${baseUrl}/recipes/${id}`, recipe)
}

function deleteRecipe(id) {
    return instance.delete(`${baseUrl}/recipes/${id}`)
}

function importRecipe(url) {
    return instance.post(`${baseUrl}/import`, `url=${url}`)
}

function getAllCategories() {
    return instance.get(`${baseUrl}/categories`)
}

function updateCategoryName(oldName, newName) {
    return instance.put(`${baseUrl}/category/${encodeURIComponent(oldName)}`, {
        name: newName,
    })
}

function getAllKeywords() {
    return instance.get(`${baseUrl}/keywords`)
}

function getConfig() {
    return instance.get(`${baseUrl}/config`)
}

function updatePrintImageSetting(enabled) {
    return instance.post(`${baseUrl}/config`, { print_image: enabled ? 1 : 0 })
}

function updateUpdateInterval(newInterval) {
    return instance.post(`${baseUrl}/config`, { update_interval: newInterval })
}

function updateRecipeDirectory(newDir) {
    return instance.post(`${baseUrl}/config`, { folder: newDir })
}

function updateVisibleInfoBlocks(visibleInfoBlocks) {
    return instance.post(`${baseUrl}/config`, { visibleInfoBlocks })
}

function reindex() {
    return instance.post(`${baseUrl}/reindex`)
}

export default {
    recipes: {
        create: createNewRecipe,
        getAll: getAllRecipes,
        get: getRecipe,
        allInCategory: getAllRecipesOfCategory,
        allWithTag: getAllRecipesWithTag,
        search: searchRecipes,
        update: updateRecipe,
        delete: deleteRecipe,
        import: importRecipe,
        reindex,
    },
    categories: {
        getAll: getAllCategories,
        update: updateCategoryName,
    },
    keywords: {
        getAll: getAllKeywords,
    },
    config: {
        get: getConfig,
        directory: {
            update: updateRecipeDirectory,
        },
        printImage: {
            update: updatePrintImageSetting,
        },
        updateInterval: {
            update: updateUpdateInterval,
        },
        visibleInfoBlocks: {
            update: updateVisibleInfoBlocks,
        },
    },
}
