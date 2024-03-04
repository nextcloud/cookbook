<template>
    <div class="times flex flex-wrap gap-4 mx-0 mt-2">
        <RecipeTimer
            v-if="timerTotal && totalTimeVisible"
            :value="timerTotal"
            :timer="false"
            :label="
                //TRANSLATORS Short for total time
                t('cookbook', 'Total')
            "
            class="time"
        />
        <RecipeTimer
            v-if="timerPrep && prepTimeVisible"
            :value="timerPrep"
            :timer="false"
            :label="
                //TRANSLATORS Short for preparation time
                t('cookbook', 'Prep')
            "
            class="time"
        />
        <RecipeTimer
            v-if="timerCook && cookTimeVisible"
            :value="timerCook"
            :timer="true"
            :label="
                //TRANSLATORS Short for cooking time
                t('cookbook', 'Cook')
            "
            class="time"
        />
    </div>
</template>

<script setup>
import RecipeTimer from 'cookbook/components/RecipeView/Timers/RecipeTimer.vue';

defineProps({
    timerCook: {
        type: Object,
        default() {
            return { hours: 0, minutes: 0, seconds: 0 };
        },
    },
    timerPrep: {
        type: Object,
        default() {
            return { hours: 0, minutes: 0, seconds: 0 };
        },
    },
    timerTotal: {
        type: Object,
        default() {
            return { hours: 0, minutes: 0, seconds: 0 };
        },
    },
    cookTimeVisible: {
        type: Boolean,
        default: true,
    },
    prepTimeVisible: {
        type: Boolean,
        default: true,
    },
    totalTimeVisible: {
        type: Boolean,
        default: true,
    },
});
</script>

<style scoped lang="scss">
.times {
    .time {
        max-width: 100%;
        flex-basis: 0;
        flex-grow: 1;
    }
}
</style>
