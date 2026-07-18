<template>
    <fieldset>
        <label>{{ fieldLabel }}</label>
        <input v-model="hours" type="number" min="0" placeholder="00" />
        <span>:</span>
        <input
            v-model="minutes"
            type="number"
            min="0"
            max="59"
            placeholder="00"
        />
        <span>:</span>
        <input
            v-model="seconds"
            type="number"
            min="0"
            max="59"
            placeholder="00"
            @change="console.log('changed')"
        />
    </fieldset>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    fieldLabel: {
        type: String,
        default: '',
    },
});

const value = defineModel({
    type: String,
    required: true,
});

const timeComps = computed(() => {
    const match = value.value.match(/PT(\d+?)H(\d+?)M(\d+?)S/) ?? [0, 0, 0, 0];
    return match.slice(1);
});

function updatePaddedTime(h, m, s) {
    // create padded time string
    const hoursPadded = h.toString().padStart(2, '0');
    const minutesPadded = m.toString().padStart(2, '0');
    const secondsPadded = s.toString().padStart(2, '0');

    value.value = `PT${hoursPadded}H${minutesPadded}M${secondsPadded}S`;
}

const hours = computed({
    get: () => timeComps.value[0],
    set: (v) => {
        // value.value.time[0] = v ?? 0;
        updatePaddedTime(v, minutes.value, seconds.value);
    },
});

const minutes = computed({
    get: () => timeComps.value[1],
    set: (v) => {
        // value.value.time[1] = v ?? 0;
        updatePaddedTime(hours.value, v, seconds.value);
    },
});

const seconds = computed({
    get: () => timeComps.value[2],
    set: (v) => {
        // value.value.time[2] = v ?? 0;
        updatePaddedTime(hours.value, minutes.value, v);
    },
});
</script>

<script>
export default {
    name: 'EditTimeField',
};
</script>

<style scoped>
fieldset {
    margin-bottom: 1em;
}

fieldset > * {
    margin: 0;
    float: inline-start;
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
