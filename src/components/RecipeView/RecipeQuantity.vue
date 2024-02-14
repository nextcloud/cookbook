<template>
    <span class="quantity">
        <span>{{ quantity.value }}</span
        ><span
            v-if="
                quantity &&
                quantity.unitText &&
                typeof quantity.unitText === 'string' &&
                quantity.unitText.trim() !== ''
            "
            >&thinsp;
            <span>{{ quantity.unitText }}</span>
        </span>
    </span>
</template>

<script setup>
defineProps({
    /** @type {QuantitativeValue|null} */
    quantity: {
        type: Object,
        default: () => null,
    },
});
</script>
