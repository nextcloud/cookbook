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
        <span>:</span>
        <input
            v-model="seconds"
            type="number"
            min="0"
            max="59"
            placeholder="00"
            @input="handleInput"
        />
    </fieldset>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';

const emit = defineEmits(['input']);

const props = defineProps({
    value: {
        type: Object,
        required: true,
        default: () => ({
            time: [null, null, null],
            paddedTime: null,
        }),
    },
    fieldLabel: {
        type: String,
        default: '',
    },
});

/**
 * @type {import('vue').Ref<number>}
 */
const hours = ref(null);
/**
 * @type {import('vue').Ref<number>}
 */
const minutes = ref(null);
/**
 * @type {import('vue').Ref<number>}
 */
const seconds = ref(null);

// Methods

const handleInput = () => {
    seconds.value = seconds.value ? seconds.value : 0;
    minutes.value = minutes.value ? minutes.value : 0;
    hours.value = hours.value ? hours.value : 0;

    // create padded time string
    const hoursPadded = hours.value.toString().padStart(2, '0');
    const minutesPadded = minutes.value.toString().padStart(2, '0');
    const secondsPadded = seconds.value.toString().padStart(2, '0');

    emit('input', {
        time: [hours.value, minutes.value, seconds.value],
        paddedTime: `PT${hoursPadded}H${minutesPadded}M${secondsPadded}S`,
    });
};

const setLocalValueFromProps = () => {
    if (props.value?.time) {
        [hours.value, minutes.value, seconds.value] = props.value.time;
    }
};

// Watchers

watch(() => props.value, setLocalValueFromProps);

// Vue lifecycle

onMounted(() => {
    setLocalValueFromProps();
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
