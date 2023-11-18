<template>
    <div class="location-wrapper">
        <h2 class="location">{{ title }}</h2>
    </div>
</template>

<script>
export default {
    name: 'Location',
};
</script>

<script setup>
import { defineProps } from 'vue';

const props = defineProps({
    title: { type: String, default: '' },
});
</script>
