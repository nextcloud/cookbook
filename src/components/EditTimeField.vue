<template>
    <fieldset>
        <label>{{ fieldLabel }}</label>
        <input
            type="number"
            min="0"
            v-model="hours"
            placeholder="00"
            @input="handleInput"
        />
        <span>:</span>
        <input
            type="number"
            min="0"
            max="59"
            v-model="minutes"
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
            default: {
                time: [null, null],
                paddedTime: null,
            },
        },
        fieldLabel: String,
    },
    data() {
        return {
            minutes: null,
            hours: null,
        }
    },
    watch: {
        value: function () {
            this.hours = this.value.time[0]
            this.minutes = this.value.time[1]
        },
    },
    methods: {
        handleInput() {
            this.minutes = this.minutes ? this.minutes : 0
            this.hours = this.hours ? this.hours : 0

            // create padded time string
            let hours_p = this.hours.toString().padStart(2, "0")
            let mins_p = this.minutes.toString().padStart(2, "0")

            this.$emit("input", {
                time: [this.hours, this.minutes],
                paddedTime: "PT" + hours_p + "H" + mins_p + "M",
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
    vertical-align: top;
    display: inline-block;
    width: 10em;
    line-height: 18px;
    font-weight: bold;
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
    width: 50px !important;
    text-align: center;
}
</style>
