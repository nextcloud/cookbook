<template>
    <ul>
        <label>{{ fieldLabel }}</label>
        <li v-for="(entry,idx) in $parent.recipe[fieldName]" :key="fieldName+idx">
            <input v-if="fieldType==='text'" type="text" v-model="$parent.recipe[fieldName][idx]" />
            <textarea v-else-if="fieldType==='textarea'" v-model="$parent.recipe[fieldName][idx]"></textarea>
            <button class="icon-arrow-up" @click="moveUp(idx)"></button>
            <button class="icon-arrow-down" @click="moveDown(idx)"></button>
            <button class="icon-delete" @click="deleteEntry(idx)"></button>
        </li>
        <li>
            <input v-if="fieldType==='text'" type="text" value="" />
            <textarea v-else-if="fieldType==='textarea'"></textarea>
            <button class="icon-arrow-up"></button>
            <button class="icon-arrow-down"></button>
            <button class="icon-delete"></button>
        </li>
    </ul>
</template>

<script>
export default {
    name: "EditInputGroup",
    props: ['fieldType','fieldName','fieldLabel'],
    data () {
        return {
            values: {},
        }
    },
    methods: {
        deleteEntry: function(idx) {
            this.$parent.deleteEntry(this.fieldName, idx)
        },
        moveDown: function(idx) {
            this.$parent.moveEntryDown(this.fieldName, idx)
        },
        moveUp: function(idx) {
            this.$parent.moveEntryUp(this.fieldName, idx)
        },
    },
}
</script>

<style scoped>

</style>
