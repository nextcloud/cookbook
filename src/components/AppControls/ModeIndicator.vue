<!--
SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors

SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later
-->

<template>
    <span class="mode-indicator">{{ title }}</span>
</template>

<script></script>

<script setup>
import { defineProps } from 'vue';

// Props
defineProps({
    title: { type: String, default: '' },
});
</script>

<script>
export default {
    name: 'ModeIndicator',
};
</script>
