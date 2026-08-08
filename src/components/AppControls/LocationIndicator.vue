<!--
SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors

SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later
-->

<template>
    <div class="location-wrapper">
        <h2 class="location">{{ title }}</h2>
    </div>
</template>

<script setup>
import { defineProps } from 'vue';

defineProps({
    title: { type: String, default: '' },
});
</script>

<script>
export default {
    name: 'LocationIndicator',
};
</script>
