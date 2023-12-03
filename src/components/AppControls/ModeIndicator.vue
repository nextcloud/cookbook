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
