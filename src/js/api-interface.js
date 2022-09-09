import axios from "@nextcloud/axios"

import { generateUrl } from "@nextcloud/router"

const baseUrl = `${generateUrl("apps/cookbook")}/webapp`

axios.defaults.headers.common.Accept = "application/json"

function createNewRecipe(recipe) {
    return axios.post(`${baseUrl}/recipes`, recipe)
}

function getRecipe(id) {
    return axios.get(`${baseUrl}/recipes/${id}`)
}

function getAllRecipes() {
    return axios.get(`${baseUrl}/recipes`)
}

function getAllRecipesOfCategory(categoryName) {
    return axios.get(`${baseUrl}/category/${categoryName}`)
}

function getAllRecipesWithTag(tags) {
    return axios.get(`${baseUrl}/tags/${tags}`)
}

function searchRecipes(search) {
    return axios.get(`${baseUrl}/search/${search}`)
}

function updateRecipe(id, recipe) {
    return axios.put(`${baseUrl}/recipes/${id}`, recipe)
}

function deleteRecipe(id) {
    return axios.delete(`${baseUrl}/recipes/${id}`)
}

function importRecipe(url) {
    return axios.post(`${baseUrl}/import`, `url=${url}`)
}

function getAllCategories() {
    return axios.get(`${baseUrl}/categories`)
}

function updateCategoryName(oldName, newName) {
    return axios.put(`${baseUrl}/category/${encodeURIComponent(oldName)}`, {
        name: newName,
    })
}

function getAllKeywords() {
    return axios.get(`${baseUrl}/keywords`)
}

function getConfig() {
    return axios.get(`${baseUrl}/config`)
}

function updatePrintImageSetting(enabled) {
    return axios.post(`${baseUrl}/config`, { print_image: enabled ? 1 : 0 })
}

function updateUpdateInterval(newInterval) {
    return axios.post(`${baseUrl}/config`, { update_interval: newInterval })
}

function updateRecipeDirectory(newDir) {
    return axios.post(`${baseUrl}/config`, { folder: newDir })
}

function reindex() {
    return axios.post(`${baseUrl}/reindex`)
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
    },
}
