<template>
    <span class="mode-indicator">{{ title }}</span>
</template>

<script>
export default {
    name: 'ModeIndicator',
};
</script>

<script setup>
import { defineProps } from 'vue';

defineProps({
    title: { type: String, default: '' },
});
</script>
