<!--
SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors

SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later
-->

<template>
    <li>
        <div class="title">
            {{ title }}
        </div>
        <div class="data">
            {{ data }}
        </div>
    </li>
</template>

<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    data: {
        type: String,
        required: true,
    },
});
</script>

<script>
export default {
    name: 'RecipeNutritionInfoItem',
};
</script>

<style scoped>
li {
    margin-bottom: 0.5em;
    margin-inline-start: 1.25em;
}

li .title {
    margin-bottom: 0;
    color: var(--color-main-text);
    font-weight: bolder;
}
</style>
