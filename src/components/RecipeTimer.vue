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

<script>
import alarmSound from "file-loader!../media/alarm.mp3"

export default {
    name: "RecipeTimer",
    props: {
        value: {
            type: Object,
            default() {
                return { hours: 0, minutes: 0 }
            },
        },
        label: {
            type: String,
            default: "",
        },
        timer: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            countdown: null,
            hours: 0,
            minutes: 0,
            seconds: 0,
            showFullTime: false,
        }
    },
    computed: {
        displayTime() {
            let text = ""
            if (this.showFullTime) {
                text += `${this.hours.toString().padStart(2, "0")}:`
            } else {
                text += `${this.hours.toString()}:`
            }
            text += this.minutes.toString().padStart(2, "0")
            if (this.showFullTime) {
                text += `:${this.seconds.toString().padStart(2, "0")}`
            }
            return text
        },
    },
    watch: {
        value() {
            this.resetTimeDisplay()
        },
    },
    mounted() {
        this.resetTimeDisplay()
        // Start loading the sound early so it's ready to go when we need to
        // play it
        this.audio = new Audio(alarmSound)
    },
    methods: {
        onTimerEnd() {
            window.clearInterval(this.countdown)
            // I'll just use an alert until this functionality is finished
            const $this = this
            window.setTimeout(() => {
                // The short timeout is needed or Vue doesn't have time to update the countdown
                //  display to display 00:00:00
                this.audio.play()
                // eslint-disable-next-line no-alert
                alert(t("cookbook", "Cooking time is up!"))
                // cookbook.notify(t('cookbook', 'Cooking time is up!'))
                $this.countdown = null
                $this.showFullTime = false
                $this.resetTimeDisplay()
            }, 100)
        },
        resetTimeDisplay() {
            if (this.value.hours) {
                this.hours = parseInt(this.value.hours, 10)
            } else {
                this.hours = 0
            }
            if (this.value.minutes) {
                this.minutes = parseInt(this.value.minutes, 10)
            } else {
                this.minutes = 0
            }
            this.seconds = 0
        },
        timerToggle() {
            // We will switch to full time display the first time this method is invoked.
            // There should probably also be a way to reset the timer other than by letting
            //  it run its course...
            if (!this.showFullTime) {
                this.showFullTime = true
            }
            if (this.countdown === null) {
                // Pass this to callback function
                const $this = this
                this.countdown = window.setInterval(() => {
                    $this.seconds -= 1
                    if ($this.seconds < 0) {
                        $this.seconds = 59
                        $this.minutes -= 1
                    }
                    if ($this.minutes < 0) {
                        $this.minutes = 59
                        $this.hours -= 1
                    }
                    if (
                        $this.hours === 0 &&
                        $this.minutes === 0 &&
                        $this.seconds === 0
                    ) {
                        $this.onTimerEnd()
                    }
                }, 1000)
            } else {
                window.clearInterval(this.countdown)
                this.countdown = null
            }
        },
    },
}
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
