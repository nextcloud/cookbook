<template>
    <div class="time">
        <button
            v-if="timer"
            type="button"
            class="print-hidden"
            @click="timerToggle"
        >
            <span :class="countdown === null ? 'icon-play' : 'icon-pause'" />
        </button>
        <h4>{{ label }}</h4>
        <div
            class="timeContainer"
            :class="countdownStarted ? 'timer-running' : ''"
        >
            <!-- eslint-disable-next-line vue/no-v-html -->
            <p v-html="displayTime" />
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { linkTo } from '@nextcloud/router';
import { showSimpleAlertModal } from 'cookbook/js/modals';
import helper from 'cookbook/js/helper';

// Properties
const props = defineProps({
    value: {
        type: Object,
        default() {
            return { hours: 0, minutes: 0, seconds: 0 };
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
const countdownStarted = ref(false);
// Create a ref for the audio element
const audio = ref(new Audio());

// Methods
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
    if (props.value.seconds) {
        seconds.value = parseInt(props.value.seconds, 10);
    } else {
        seconds.value = 0;
    }
};

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
        countdownStarted.value = false;
        resetTimeDisplay();
    }, 100);
};

const timerToggle = () => {
    // We will switch to full time display the first time this method is invoked.
    // There should probably also be a way to reset the timer other than by letting
    //  it run its course...
    if (!countdownStarted.value) {
        countdownStarted.value = true;
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

/**
 * Add style to the unit of the value.
 * @param str Complete translated string with value and unit
 * @param value The value without the unit
 * @param isPadded If value should be padded with zeros to ensure it has two digits
 * @returns {string} Complete styled string with value and unit
 */
const styleUnit = (str, value, isPadded = true) => {
    // Remove value
    const unit = str.replace(`${value.toString()}`, '');
    // Style unit
    let text = `<span class="timerUnit">${helper.escapeHTML(unit)}</span>`;
    // Reassemble value and unit.
    // Make sure that value is a number to prevent XSS attacks (due to the v-html directive)
    if (isPadded) {
        text = `${Number(value).toString().padStart(2, '0')}${text}`;
    } else {
        text = `${Number(value).toString()}${text}`;
    }
    text = `<nobr>${text}</nobr>`;
    return text;
};

// Computed properties
const displayHours = computed(() => {
    let hoursText = '';
    // TRANSLATORS hours part of timer text
    hoursText += n('cookbook', '{hours}h', '{hours}h', hours.value, {
        hours: `${hours.value.toString()}`,
    });

    return styleUnit(hoursText, hours.value);
});

const displayMinutes = computed(() => {
    let minutesText = '';
    // TRANSLATORS minutes part of timer text
    minutesText += n('cookbook', '{minutes}m', '{minutes}m', minutes.value, {
        minutes: `${minutes.value.toString()}`,
    });

    return styleUnit(minutesText, minutes.value);
});

const displaySeconds = computed(() => {
    let secondsText = '';
    // TRANSLATORS seconds part of timer text
    secondsText += n('cookbook', '{seconds}s', '{seconds}s', seconds.value, {
        seconds: `${seconds.value.toString()}`,
    });

    return styleUnit(secondsText, seconds.value);
});

const displayTime = computed(() => {
    let text = '';
    if (props.value.hours && props.value.hours > 0) {
        text += displayHours.value;
    }
    if (
        (countdownStarted.value &&
            ((props.value.hours && props.value.hours > 0) ||
                (props.value.minutes && props.value.minutes > 0))) ||
        (!countdownStarted.value &&
            props.value.minutes &&
            props.value.minutes > 0)
    ) {
        text += displayMinutes.value;
    }
    if (
        (countdownStarted.value &&
            ((props.value.hours && props.value.hours > 0) ||
                (props.value.minutes && props.value.minutes > 0) ||
                (props.value.seconds && props.value.seconds > 0))) ||
        (!countdownStarted.value &&
            props.value.seconds &&
            props.value.seconds > 0)
    ) {
        text += displaySeconds.value;
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
</script>

<script>
export default {
    name: 'RecipeTimer',
};
</script>

<style scoped>
.time {
    position: relative;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    text-align: center;

    button {
        position: absolute;
        top: 0;
        right: -16px;
        display: inline-flex;
        width: 32px;
        height: 32px;
        min-height: 32px;
        justify-content: center;
        padding: 0.25em 0.1em;
        transform: translate(0, -50%);
        vertical-align: middle;
    }

    & > .timeContainer {
        display: flex;
        height: 100%;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 0.5rem 0.25rem;

        border-top: 1px solid var(--color-border-dark);

        &.timer-running {
            font-size: 1.2em;
        }
    }

    /* The :deep selector prevents a data attribute from being added to the scoped element. */
    :deep(.timerUnit) {
        color: var(--color-text-lighter);
        font-size: 0.8em;
        margin-inline-end: 0.3em;
    }
}
</style>
