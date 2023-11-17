<template>
    <h2>{{ t("cookbook", "The page was not found") }}</h2>
</template>

<script setup>
import { onMounted } from 'vue';
import { useStore } from '../store';

const store = useStore();

onMounted(() => {
        store.dispatch("setPage", { page: "notfound" });
});
</script>

<script>
export default {
    name: 'NotFound'
};
</script>

<style scoped>
h2 {
    margin: 1rem;
}
</style>
