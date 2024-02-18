<template>
    <!--    <p class="dates section">-->
    <div>
        <span
            v-if="showCreatedDate"
            class="date"
            :title="t('cookbook', 'Date created')"
        >
            <span class="icon-calendar-dark date-icon" />
            <span class="date-text">{{ dateCreated }}</span>
        </span>
        <span
            v-if="showModifiedDate"
            class="date"
            :title="t('cookbook', 'Last modified')"
        >
            <span class="icon-rename date-icon" />
            <span class="date-text">{{ dateModified }}</span>
        </span>
    </div>
    <!--    </p>-->
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    dateCreated: {
        type: String,
        default: '',
    },
    dateModified: {
        type: String,
        default: '',
    },
});

const showCreatedDate = computed(() => props.dateCreated);

const showModifiedDate = computed(() => {
    if (!props.dateModified) {
        return false;
    }
    return !(
        props.dateCreated &&
        props.dateModified &&
        props.dateCreated === props.dateModified
    );
});
</script>

<style scoped lang="scss">
.date {
    margin-right: 1.5em;
}

.date-icon {
    display: inline-block;
    margin-right: 0.2em;
    margin-bottom: 0.2em;
    background-size: 1em;
    vertical-align: middle;
}

.date-text {
    vertical-align: middle;
}
</style>
