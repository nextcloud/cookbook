<template>
    <fieldset>
        <label>{{ fieldLabel }}</label>
        <input
            v-model="hours"
            type="number"
            min="0"
            placeholder="00"
            @input="handleInput"
        />
        <span>:</span>
        <input
            v-model="minutes"
            type="number"
            min="0"
            max="59"
            placeholder="00"
            @input="handleInput"
        />
    </fieldset>
</template>

<script>
export default {
    name: "EditTimeField",
    props: {
        value: {
            type: Object,
            required: true,
            default: () => ({
                time: [null, null],
                paddedTime: null,
            }),
        },
        fieldLabel: {
            type: String,
            default: "",
        },
    },
    data() {
        return {
            minutes: null,
            hours: null,
        }
    },
    watch: {
        value() {
            ;[this.hours, this.minutes] = this.value.time
        },
    },
    methods: {
        handleInput() {
            this.minutes = this.minutes ? this.minutes : 0
            this.hours = this.hours ? this.hours : 0

            // create padded time string
            const hoursPadded = this.hours.toString().padStart(2, "0")
            const minutesPadded = this.minutes.toString().padStart(2, "0")

            this.$emit("input", {
                time: [this.hours, this.minutes],
                paddedTime: `PT${hoursPadded}H${minutesPadded}M`,
            })
        },
    },
}
</script>

<style scoped>
fieldset {
    margin-bottom: 1em;
}

fieldset > * {
    margin: 0;
    float: left;
}

fieldset > label {
    display: inline-block;
    width: 10em;
    font-weight: bold;
    line-height: 18px;
    vertical-align: top;
}
@media (max-width: 1199px) {
    fieldset > label {
        display: block;
        float: none;
    }
}

fieldset > span {
    margin: 0 0.5rem;
    line-height: 34px;
}

fieldset > input {
    width: 4.5em;
    text-align: center;
}
</style>
