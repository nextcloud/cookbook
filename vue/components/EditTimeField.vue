<template>
    <fieldset class="duration">
        <label>{{ fieldLabel }}</label>
        <input type="number" min="0" @change="valueChanged()" v-model="hours" placeholder="00">
        <span>:</span>
        <input type="number" min="0" max="59" @change="valueChanged()" v-model="minutes" placeholder="00">
    </fieldset>
</template>

<script>
export default {
    name: "EditTimeField",
    props: ['fieldName','fieldLabel'],
    data () {
        return {
            hours: '',
            minutes: '',
            modified: false,
        }
    },
    computed: {
    },
    methods: {
        valueChanged: function() {
            let hoursStr = this.hours.toString().padStart(2, '0')
            let minsStr = this.minutes.toString().padStart(2, '0')
            this.$parent.recipe[this.fieldName] = 'PT' + hoursStr + 'H' + minsStr + 'M'
        },
    },
    mounted () {
        // Parse time value
        let timeComps = this.$parent.recipe[this.fieldName].match(/PT([0-9]+)H([0-9]+)M/)
        if (timeComps) {
            this.hours = timeComps[1]
            this.minutes = timeComps[2]
        }
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
    @media(max-width:1199px) { fieldset > label {
        display: block;
        float: none;
    }}

fieldset > span {
    margin: 0 0.5rem;
    line-height: 34px;
}

fieldset > input {
    width: 50px !important;
    text-align: center;
}

</style>
