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
