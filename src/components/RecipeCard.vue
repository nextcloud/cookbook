<template>
        <div class="recipe-card" v-if="recipe !== null">
            <router-link :to="'/recipe/' + recipe.recipe_id">
                <lazy-picture
                    v-if="recipe.imageUrl"
                    class="recipe-thumbnail"
                    :lazy-src="recipe.imageUrl"
                    :blurred-preview-src="
                        recipe.imagePlaceholderUrl
                    "
                    :width="105"
                    :height="105"
                />
                <div class="recipe-info-container">
                    <span class="recipe-title">{{
                        recipe.name
                    }}</span>
                    <div
                        class="recipe-info-container-bottom"
                    >
                        <span class="recipe-info-date"
                            v-if="
                                formatDateTime(
                                    recipe.dateCreated
                                ) != null
                            ">
                            <span class="icon-calendar-dark recipe-info-date-icon" />
                            <span
                                class="recipe-date"
                                >{{
                                    formatDateTime(recipe.dateCreated)
                                }}
                            </span>
                        </span>
                        <span class="recipe-info-date"
                            v-if="
                                recipe.dateModified !==
                                    recipe.dateCreated &&
                                formatDateTime(
                                    recipe.dateModified
                                ) != null
                            ">
                            <span class="icon-rename recipe-info-date-icon" />
                            <span class="recipe-date">{{
                                    formatDateTime(
                                        recipe.dateModified
                                    )
                                }}
                            </span>
                        </span>
                    </div>
                </div>
            </router-link>
        </div>
</template>

<script>
import moment from "@nextcloud/moment"
import LazyPicture from "./LazyPicture.vue"

export default {
    name: "RecipeCard",
    components: {
        LazyPicture,
    },
    props: {
        recipe: {
            type: Object,
            default: () => null,
        },
    },
    methods: {
        /* The schema.org standard requires the dates formatted as Date (https://schema.org/Date)
         * or DateTime (https://schema.org/DateTime). This follows the ISO 8601 standard.
         */
        formatDateTime(dt) {
            if (!dt) return null
            const date = moment(dt, moment.ISO_8601)
            if (!date.isValid()) {
                return null
            }
            return date.format("L, LT").toString()
        },
    },
}
</script>

<style scoped>

.recipe-card {
    width: 300px;
    max-width: 100%;
    margin: 0.5rem 1rem 1rem;
}
.recipe-card a {
    display: block;
    height: 105px;
    border-radius: 3px;
    box-shadow: 0 0 3px #aaa;
}
.recipe-card a:hover {
    box-shadow: 0 0 5px #888;
}

.recipe-card .recipe-thumbnail {
    position: relative;
    overflow: hidden;
    width: 105px;
    height: 105px;
    background-color: #bebdbd;
    border-radius: 3px 0 0 3px;
    float: left;
}

.recipe-card span {
    display: block;
}

.recipe-info-container {
    display: flex;
    height: 100%;
    flex-direction: column;
    padding: 0.5rem;
}

.recipe-title {
    overflow: hidden;
    flex-grow: 1;
    font-weight: 500;
    line-height: 2.6ex;
    text-overflow: ellipsis;
}

.recipe-card .recipe-info-date {
    display: flex;
}
.recipe-info-date-icon {
    height: 1.4ex;
    min-height: 0;
    background-size: contain;
}

.recipe-date {
    height: 2.7ex;
    color: var(--color-text-lighter);
    font-size: 10px;
    line-height: 2ex;
}
</style>
