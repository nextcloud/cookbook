<template>
    <div class="dates">
        <div
            v-if="showCreatedDate"
            class="date"
            :title="t('cookbook', 'Date created')"
        >
            <span class="mr-1">
                <span class="icon-calendar-dark date-icon" />
                <span>Created:</span>
            </span>
            <span>
                <span class="date-text">{{ dateCreated }}</span>
            </span>
        </div>
        <div
            v-if="showModifiedDate"
            class="date"
            :title="t('cookbook', 'Last modified')"
        >
            <span class="mr-1">
                <span class="icon-rename date-icon" />
                <span>Modified:</span>
            </span>
            <span>
                <span class="date-text">{{ dateModified }}</span>
            </span>
        </div>
    </div>
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
.dates {
    display: flex;
    flex-direction: column;
}

.date {
    display: flex;
    flex-wrap: wrap;
    align-items: stretch;
    margin-right: 1.5em;

    span {
        display: flex;
        align-items: center;
    }
}

.date-icon {
    display: inline-block;
    margin-right: 0.4em;
    background-size: 1em;
    vertical-align: middle;
}

.date-text {
    vertical-align: middle;
}
</style>
