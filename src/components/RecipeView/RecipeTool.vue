<!--
SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors

SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later
-->

<template>
    <li>
        <VueShowdown :markdown="tool" class="markdown-tool" />
    </li>
</template>

<script setup>
defineProps({
    tool: {
        type: String,
        default: '',
    },
});
</script>

<script>
export default {
    name: 'RecipeTool',
};
</script>

<style scoped>
li {
    margin-inline-start: 1.25em;
}

.markdown-tool:deep(a) {
    text-decoration: underline;
}
</style>
