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

<style>

</style>
