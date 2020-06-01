<template>
    <fieldset>
        <label>{{ fieldLabel }}</label>
        <input type="number" min="0" v-model="$parent[fieldName][0]" placeholder="00">
        <span>:</span>
        <input type="number" min="0" max="59" v-model="$parent[fieldName][1]" placeholder="00">
    </fieldset>
</template>

<script>
export default {
    name: "EditTimeField",
    props: ['fieldName','fieldLabel'],
    data () {
        return {
        }
    },
    computed: {
    },
    methods: {
    },
    mounted () {
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
