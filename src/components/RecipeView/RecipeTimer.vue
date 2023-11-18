<template>
    <div class="time">
        <button
            v-if="timer"
            type="button"
            :class="countdown === null ? 'icon-play' : 'icon-pause'"
            @click="timerToggle"
        ></button>
        <h4>{{ label }}</h4>
        <p>{{ displayTime }}</p>
    </div>
</template>

<script setup>
import { computed, defineProps, onMounted, ref, watch } from 'vue';
import { linkTo } from '@nextcloud/router';
import { showSimpleAlertModal } from 'cookbook/js/modals';

// Properties
const props = defineProps({
    value: {
        type: Object,
        default() {
            return { hours: 0, minutes: 0 };
        },
    },
    label: {
        type: String,
        default: '',
    },
    timer: {
        type: Boolean,
        default: false,
    },
});

// Reactive
const countdown = ref(null);
/**
 * @type {import('vue').Ref<number>}
 */
const hours = ref(0);
/**
 * @type {import('vue').Ref<number>}
 */
const minutes = ref(0);
/**
 * @type {import('vue').Ref<number>}
 */
const seconds = ref(0);
/**
 * @type {import('vue').Ref<boolean>}
 */
const showFullTime = ref(false);
// Create a ref for the audio element
const audio = ref(new Audio());

// Computed properties
const displayTime = computed(() => {
    let text = '';
    if (showFullTime.value) {
        text += `${hours.value.toString().padStart(2, '0')}:`;
    } else {
        text += `${hours.value.toString()}:`;
    }
    text += minutes.value.toString().padStart(2, '0');
    if (showFullTime.value) {
        text += `:${seconds.value.toString().padStart(2, '0')}`;
    }
    return text;
});

// Watchers
watch(
    () => props.value,
    () => {
        resetTimeDisplay();
    },
);

onMounted(() => {
    resetTimeDisplay();
    // Start loading the sound early, so it is ready to go when we need to
    // play it

    // Source for the sound https://pixabay.com/sound-effects/alarm-clock-short-6402/
    // Voted by poll https://nextcloud.christian-wolf.click/nextcloud/apps/polls/s/Wke3s6CscDwQEjPV
    audio.value = new Audio(linkTo('cookbook', 'assets/alarm-continuous.mp3'));

    // For now, the alarm should play continuously until it is dismissed
    // See https://github.com/nextcloud/cookbook/issues/671#issuecomment-1279030452
    audio.value.loop = true;
});

// Methods
const onTimerEnd = () => {
    window.clearInterval(countdown.value);
    window.setTimeout(async () => {
        // The short timeout is needed or Vue doesn't have time to update the countdown
        //  display to display 00:00:00

        // Ensure audio starts at the beginning
        // If it's paused midway, by default it will resume from that point
        audio.value.currentTime = 0;
        // Start playing audio to alert the user that the timer is up
        audio.value.play();

        await showSimpleAlertModal(t('cookbook', 'Cooking time is up!'));

        // Stop audio after the alert is confirmed
        audio.value.pause();

        countdown.value = null;
        showFullTime.value = false;
        resetTimeDisplay();
    }, 100);
};

const resetTimeDisplay = () => {
    if (props.value.hours) {
        hours.value = parseInt(props.value.hours, 10);
    } else {
        hours.value = 0;
    }
    if (props.value.minutes) {
        minutes.value = parseInt(props.value.minutes, 10);
    } else {
        minutes.value = 0;
    }
    seconds.value = 0;
};

const timerToggle = () => {
    // We will switch to full time display the first time this method is invoked.
    // There should probably also be a way to reset the timer other than by letting
    //  it run its course...
    if (!showFullTime.value) {
        showFullTime.value = true;
    }
    if (countdown.value === null) {
        countdown.value = window.setInterval(() => {
            seconds.value -= 1;
            if (seconds.value < 0) {
                seconds.value = 59;
                minutes.value -= 1;
            }
            if (minutes.value < 0) {
                minutes.value = 59;
                hours.value -= 1;
            }
            if (
                hours.value === 0 &&
                minutes.value === 0 &&
                seconds.value === 0
            ) {
                onTimerEnd();
            }
        }, 1000);
    } else {
        window.clearInterval(countdown.value);
        countdown.value = null;
    }
};
</script>

<script>
export default {
    name: 'RecipeTimer',
};
</script>

<style scoped>
.time {
    position: relative;
    flex-grow: 1;
    border: 1px solid var(--color-border-dark);
    border-radius: 3px;
    margin: 1rem 2rem;
    font-size: 1.2rem;
    text-align: center;
}

.time button {
    position: absolute;
    top: 0;
    left: 0;
    width: 36px;
    height: 36px;
    transform: translate(-50%, -50%);
}

.time h4 {
    padding: 0.5rem;
    border-bottom: 1px solid var(--color-border-dark);
    background-color: var(--color-background-dark);
    font-weight: bold;
}

.time p {
    padding: 0.5rem;
}

@media print {
    button {
        display: none !important;
    }
}
</style>
