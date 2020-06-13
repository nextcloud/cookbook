<template>
    <fieldset>
        <label>{{ fieldLabel }}</label>
        <ul ref="list">
            <li :class="fieldType" v-for="(entry,idx) in $parent.recipe[fieldName]" :key="fieldName+idx">
                <div v-if="fieldName==='recipeInstructions'" class="step-number">{{ parseInt(idx) + 1 }}</div>
                <input v-if="fieldType==='text'" type="text" v-model="$parent.recipe[fieldName][idx]" @keyup="keyPressed" />
                <textarea v-else-if="fieldType==='textarea'" v-model="$parent.recipe[fieldName][idx]"></textarea>
                <div class="controls">
                    <button class="icon-arrow-up" @click="moveUp(idx)"></button>
                    <button class="icon-arrow-down" @click="moveDown(idx)"></button>
                    <button class="icon-delete" @click="deleteEntry(idx)"></button>
                </div>
            </li>
        </ul>
        <button class="button add-list-item" @click="addNew()"><span class="icon-add"></span> {{ t('cookbook', 'Add') }}</button>
    </fieldset>
</template>

<script>
export default {
    name: "EditInputGroup",
    props: ['fieldType','fieldName','fieldLabel'],
    data () {
        return {
        }
    },
    methods: {
        addNew: function() {
            // This is a dirty hack, but Vue components update with a slight delay so you
            // can't just straight up go and set focus here
            let nextFocus = this.$parent.recipe[this.fieldName].length
            this.$parent.addEntry(this.fieldName)
            let failSafe = 2500
            let $ul = $(this.$refs['list'])
            let $this = this
            let focusMonitor = window.setInterval(function() {
                if ($ul.children('li').length > nextFocus) {
                    if ($this.fieldType === 'text') {
                        $ul.children('li').eq(nextFocus).find('input').focus()
                    } else if ($this.fieldType === 'textarea') {
                        $ul.children('li').eq(nextFocus).find('textarea').focus()
                    }
                    window.clearInterval(focusMonitor)
                }
                failSafe -= 100
                if (!failSafe) {
                    window.clearInterval(focusMonitor)
                }
            }, 100)
        },
        /**
         * Delete an entry from the list
         */
        deleteEntry: function(idx) {
            this.$parent.deleteEntry(this.fieldName, idx)
        },
        /**
         * Catches enter and key down presses and either adds a new row or focuses the one below
         */
        keyPressed(e) {
            // Using keyup for trigger will prevent repeat triggering if key is held down
            if (e.keyCode === 13 || e.keyCode === 10) {
                e.preventDefault()
                let $li = $(e.currentTarget).parents('li')
                let $ul = $li.parents('ul')
                if ($li.index() >= $ul.children('li').length - 1) {
                    this.addNew()
                } else {
                    $ul.children('li').eq($li.index() + 1).find('input').focus()
                }
            }
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

fieldset {
    margin-bottom: 1em;
    width: 100%;
}

fieldset > label {
    display: inline-block;
    width: 10em;
    line-height: 18px;
    font-weight: bold;
    word-spacing: initial;
}

fieldset > ul {
    margin-top: 1rem;
}

fieldset > ul + button {
    width: 36px;
    text-align: center;
    padding: 0;
    float: right;
}

fieldset > ul > li {
    display: flex;
    width: 100%;
    margin: 0 0 1em 0;
    padding-right: 0.25em;
}

    li.text > input {
        width: 100%;
        margin: 0;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    li .controls {
        display: flex;
    }

        li .controls > button {
            padding: 0;
            margin: 0;
            width: 34px;
            height: 34px;
            border-radius: 0;
            border-left-color: transparent;
            border-right-color: transparent;
        }

        li .controls > button:last-child {
            border-top-right-radius: var(--border-radius);
            border-bottom-right-radius: var(--border-radius);
            border-right-width: 1px;
        }
            li .controls > button:last-child:not(:hover):not(:focus) {
                border-right-color: var(--color-border-dark);
            }

li.textarea {
    float: right;
    position: relative;
    top: 1px;
    z-index: 1;
}

    li.textarea > textarea {
        min-height: 10em;
        resize: vertical;
        width: calc(100% - 44px);
        margin: 0 0 0 44px;
        border-top-right-radius: 0;
    }

    li.textarea::after {
        display: table;
        content: '';
        clear: both;
    }
    .step-number {
        position: absolute;
        left: 0;
        top: 0;
        height: 36px;
        width: 36px;
        border-radius: 50%;
        border: 1px solid var(--color-border-dark);
        outline: none;
        background-repeat: no-repeat;
        background-position: center;
        background-color: var(--color-background-dark);
        line-height: 36px;
        text-align: center;
    }

.icon-arrow-up {
    background-image: var(--icon-triangle-n-000);
}

.icon-arrow-down {
    background-image: var(--icon-triangle-s-000);
}

button {
    width: auto !important;
    padding: 0 1rem 0 0.75rem !important;
}

</style>
