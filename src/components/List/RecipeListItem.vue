<template>
    <!--    :to="{-->
    <!--    name: 'recipe',-->
    <!--    params: { recipeId: recipe.identifier.toString() },-->
    <!--    }"-->
    <NcListItem
        :name="name"
        :active="isSelected"
        :to="routingTarget"
        @click="onRecipeSelected(recipe.identifier)"
    >
        <template #subname>
            {{ categoryName }}
        </template>
        <template #icon>
            <!--            <div-->
            <!--                class="rounded-full"-->
            <!--                v-if="recipe.imageUrl"-->
            <!--                style="-->
            <!--                    width: 44px;-->
            <!--                    height: 44px;-->
            <!--                    overflow: hidden;-->
            <!--                    object-fit: cover;-->
            <!--                "-->
            <!--            >-->
            <LazyPicture
                v-if="recipe.imageUrl[0]"
                :lazy-src="recipe.imageUrl[0]"
                :blurred-preview-src="recipe.imagePlaceholderUrl"
                class="rounded"
                width="44px"
                height="44px"
                style="
                    position: relative;
                    overflow: hidden;
                    width: 44px;
                    height: 44px;
                "
            />
            <!--            </div>-->
            <AlertOctagonIcon
                v-else-if="recipe.error"
                :size="20"
                fill-color="#E9322D"
            />
            <StarIcon
                v-else-if="recipe.favorite"
                :size="20"
                fill-color="#FC0"
            />
            <FileDocumentOutlineIcon
                v-else
                :size="20"
                fill-color="var(--color-text-lighter)"
            />
        </template>
        <template #actions>
            <!--            Rename action -->
            <NcActionButton v-if="!renaming" @click="startRenaming">
                <template #icon>
                    <PencilIcon :size="20" />
                </template>
                {{ t('cookbook', 'Rename') }}
            </NcActionButton>
            <NcActionInput
                v-else
                v-model.trim="newName"
                :disabled="!renaming"
                :placeholder="t('cookbook', 'Rename recipe')"
                :show-trailing-button="true"
                @input="onInputChange($event)"
                @submit="onRename"
            >
                <template #icon>
                    <PencilIcon :size="20" />
                </template>
            </NcActionInput>

            <NcActionSeparator />

            <NcActionButton
                v-if="!recipe.readonly"
                :icon="actionDeleteIcon"
                @click="onDeleteRecipe"
            >
                {{ t('cookbook', 'Delete recipe') }}
            </NcActionButton>
        </template>
    </NcListItem>
</template>

<script setup>
import { computed, getCurrentInstance, inject, ref } from 'vue';
import { useRoute } from 'vue-router/composables';
import NcActionButton from '@nextcloud/vue/dist/Components/NcActionButton.js';
import NcActionInput from '@nextcloud/vue/dist/Components/NcActionInput.js';
import NcActionSeparator from '@nextcloud/vue/dist/Components/NcActionSeparator.js';
import NcListItem from '@nextcloud/vue/dist/Components/NcListItem.js';
import AlertOctagonIcon from 'vue-material-design-icons/AlertOctagon.vue';
import FileDocumentOutlineIcon from 'vue-material-design-icons/FileDocumentOutline.vue';
import PencilIcon from 'vue-material-design-icons/Pencil.vue';
import StarIcon from 'vue-material-design-icons/Star.vue';
import LazyPicture from 'cookbook/components/Utilities/LazyPicture.vue';
import { showError } from '@nextcloud/dialogs';
import { useStore } from 'cookbook/store';

const route = useRoute();
const store = useStore();
const log = getCurrentInstance().proxy.$log;

// DI
/** @type {RecipeRepository} */
const recipeRepository = inject('RecipeRepository');
const recipesLinkBasePath = inject('recipes-link-base-path');

const props = defineProps({
    /** @type {Recipe} */
    recipe: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits([
    'recipe-deletion-requested',
    'recipe-selected',
    'start-renaming',
]);

const loading = ref({ recipe: false, category: false });
const newName = ref('');
const renaming = ref(false);

const routingTarget = computed(() => ({
    path: `${recipesLinkBasePath.value}${props.recipe.identifier}`,
    query: route.query,
}));

/**
 * If this recipe is currently selected.
 * @type {import('vue').ComputedRef<boolean>}
 */
const isSelected = computed(
    () =>
        store.state.recipe?.identifier &&
        store.state.recipe.identifier === props.recipe.identifier,
);

// TODO Add indicator for unsaved changes
const name = computed(
    () => props.recipe.name + (props.recipe.unsaved ? ' *' : ''),
);

// TODO set loading state for deletion
const actionDeleteIcon = computed(
    () => `icon-delete${loading.value.delete ? ' loading' : ''}`,
);

function categoryLabel(category) {
    return category && category !== ''
        ? category
        : t('cookbook', 'Uncategorized');
}
const categoryName = computed(() => categoryLabel(props.recipe.recipeCategory));

function onRecipeSelected(recipeId) {
    emit('recipe-selected', recipeId);
}

function startRenaming() {
    renaming.value = true;
    newName.value = props.recipe.name;
    emit('start-renaming', props.recipe.id);
}

/**
 * Update the name of the recipe with id `recipeId` to `newName` in the backend.
 * @param recipeId - Identifier of the recipe
 * @param updatedName - New name of the recipe
 * @return {Promise<void>}
 */
async function updateName(recipeId, updatedName) {
    // TODO Add API endpoint for renaming?
    try {
        const recipe = await recipeRepository.getRecipeById(recipeId);
        recipe.name = updatedName;
        await recipeRepository.updateRecipe(recipeId, recipe);
    } catch (e) {
        log.error(
            t('cookbook', 'Renaming recipe {id} has failed.', { id: recipeId }),
        );
        throw e;
    }
}

function onInputChange(event) {
    this.newName = event.target.value.toString();
}

async function onRename() {
    const updatedName = newName.value.toString();
    if (!updatedName) {
        return;
    }
    loading.value.recipe = true;
    updateName(props.recipe.identifier, updatedName)
        .then(() => {
            newName.value = '';
        })
        .catch((e) => {
            log.error('Failed to rename recipe', e);
            showError(t('cookbook', 'Error while renaming recipe.'));
        })
        .finally(() => {
            loading.value.recipe = false;
        });

    renaming.value = false;
}

/**
 * Handles user-requested recipe deletion.
 * @return {void}
 */
function onDeleteRecipe() {
    emit('recipe-deletion-requested', props.recipe.identifier);
    loading.value.delete = true;
}
</script>
