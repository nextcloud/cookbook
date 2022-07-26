<template>
    <div class="location-wrapper">
        <h2 class="location">{{ title }}</h2>
    </div>
</template>

<script>
export default {
    // eslint-disable-next-line vue/multi-word-component-names
    name: "Location",
    props: {
        title: {
            type: String,
            default: "",
        },
    },
}
</script>
