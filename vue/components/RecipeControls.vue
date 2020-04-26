<template>

    <div>
        <Breadcrumbs class="breadcrumbs" v-if="$store.state.page==='create'" rootIcon="icon-category-organization">
            <Breadcrumb :title="$t('New recipe')" />
            <Breadcrumb class="no-arrow" title="">
                <ActionButton icon="icon-checkmark" class="action-button" :ariaLabel="$t('Save changes')" @click="saveChanges()" />
            </Breadcrumb>
        </Breadcrumbs>
        <Breadcrumbs class="breadcrumbs" v-else-if="$store.state.page==='edit' && $store.state.recipe" rootIcon="icon-category-organization">
            <Breadcrumb :title="$t('Edit recipe')" />
            <Breadcrumb class="no-arrow" title="">
                <ActionButton icon="icon-checkmark" class="action-button" :ariaLabel="$t('Save changes')" @click="saveChanges()" />
            </Breadcrumb>
        </Breadcrumbs>
        <Breadcrumbs class="breadcrumbs" v-else-if="$store.state.page==='recipe' && $store.state.recipe" rootIcon="icon-category-organization">
            <Breadcrumb :title="$t('Home')" :to="'/'" />
            <Breadcrumb :title="$store.state.recipe.name" :to="'/recipe/'+$store.state.recipe.id" />
            <Breadcrumb class="no-arrow" title="">
                <ActionButton icon="icon-rename" class="action-button" :ariaLabel="$t('Edit recipe')" @click="$window.goTo('/recipe/'+$store.state.recipe.id+'/edit')" />
            </Breadcrumb>
            <Breadcrumb class="no-arrow" title="">
                <ActionButton icon="icon-category-office" class="action-button" :ariaLabel="$t('Print recipe')" @click="printRecipe()" />
            </Breadcrumb>
            <Breadcrumb class="no-arrow" title="">
                <ActionButton icon="icon-delete" class="action-button" :ariaLabel="$t('Delete recipe')" @click="deleteRecipe()" />
            </Breadcrumb>
        </Breadcrumbs>
    </div>

</template>

<script>
// Tried loading individual components from dist first, couldn't make it work
import { ActionButton } from '@nextcloud/vue'
import { Breadcrumbs } from '@nextcloud/vue'
import { Breadcrumb } from '@nextcloud/vue'

export default {
    components: {
        ActionButton, Breadcrumbs, Breadcrumb
    },
    data () {
        return {

        }
    },
    methods: {
        deleteRecipe: function() {
            // Confirm delete
            if (!confirm(this.$t('Confirm delete'))) {
                return
            }
            let id = this.$store.state.recipe.id
            let $this = this
            $.ajax({
                url: window.baseUrl + '/api/recipes/' + id,
                method: 'DELETE',
            })
            .done(function(reply) {
                window.goTo('/')
            })
            .fail(function(e) {
                alert($this.$t('Delete failed'))
                if (e && e instanceof Error) {
                    throw e
                }
            })
        },
        printRecipe: function() {
            window.print()
        },
    }
}

</script>

<style scoped>

div {
    font-weight: bold;
}

.breadcrumbs {
    flex-basis: 100%;
}

.no-arrow::before {
    content: '' !important;
}

@media print {
    * {
        display: none !important;
    }
}

</style>
