<template>
    <li  :class="{ 'instruction': true, 'done': isDone }" @click="toggleDone">{{ instruction }}</li>
</template>

<script>
export default {

    props: ['instruction'],
    data () {
        return {
            isDone: false
        }
    },
    methods: {
        toggleDone: function() {
            this.isDone = !this.isDone
        },
    },

}
</script>

<style scoped>

.instruction {
    /* I find this more convenient than a ln2br-function */
    white-space: pre-line;
}

</style>
