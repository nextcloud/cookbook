<template>
    <span class="mode-indicator">{{ title }}</span>
</template>

<script setup>
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
