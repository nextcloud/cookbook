import axios from "@nextcloud/axios"

import { generateUrl } from "@nextcloud/router"

const baseUrl = `${generateUrl("apps/cookbook")}/webapp`

function createNewRecipe(recipe) {
    return axios({
        method: "POST",
        url: `${baseUrl}/recipes`,
        data: recipe,
    })
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
    return axios({
        method: "PUT",
        url: `${baseUrl}/recipes/${id}`,
        data: recipe,
    })
}

function deleteRecipe(id) {
    return axios.delete(`${baseUrl}/recipes/${id}`)
}

function importRecipe(url) {
    return axios({
        method: "POST",
        url: `${baseUrl}/import`,
        data: `url=${url}`,
    })
}

function getAllCategories() {
    return axios.get(`${baseUrl}/categories`)
}

function updateCategoryName(oldName, newName) {
    return axios({
        method: "PUT",
        url: `${baseUrl}/category/${encodeURIComponent(oldName)}`,
        data: { name: newName },
    })
}

function getAllKeywords() {
    return axios.get(`${baseUrl}/keywords`)
}

function getConfig() {
    return axios.get(`${baseUrl}/config`)
}

function updatePrintImageSetting(enabled) {
    return axios({
        method: "POST",
        url: `${baseUrl}/config`,
        data: { print_image: enabled ? 1 : 0 },
    })
}

function updateUpdateInterval(newInterval) {
    return axios({
        method: "POST",
        url: `${baseUrl}/config`,
        data: { update_interval: newInterval },
    })
}

function updateRecipeDirectory(newDir) {
    return axios({
        method: "POST",
        url: `${baseUrl}/config`,
        data: { folder: newDir },
    })
}

function reindex() {
    return axios({
        method: "POST",
        url: `${baseUrl}/reindex`,
    })
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
