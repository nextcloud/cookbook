<template>
    <span class="mode-indicator">{{ title }}</span>
</template>

<script>
export default {
    // eslint-disable-next-line vue/multi-word-component-names
    name: "ModeIndicator",
    props: {
        title: {
            type: String,
            default: "",
        },
    },
}
</script>
