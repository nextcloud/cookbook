<template>
    <fieldset>
        <label>
            {{ fieldLabel }}
        </label>
        <div class="input-container">
            <input
                type="text"
                :value="value"
                :placeholder="
                    // prettier-ignore
                    t('cookbook', 'Enter URL or select from your Nextcloud instance on the right')
                "
                @input="$emit('input', $event.target.value)"
            />
            <button
                type="button"
                :title="t('cookbook', 'Pick a local image')"
                @click="pickImage"
            >
                <span class="icon-category-multimedia"></span>
            </button>
        </div>
    </fieldset>
</template>

<script setup>
import { FilePickerType, getFilePickerBuilder } from '@nextcloud/dialogs';
import { defineProps } from 'vue';

const emit = defineEmits(['input']);
defineProps({
    value: { type: String, default: '' },
    fieldLabel: { type: String, default: '' },
});

const pickImage = (e) => {
    e.preventDefault();
    const filePicker = getFilePickerBuilder(
        t('cookbook', 'Path to your recipe image'),
    )
        .addMimeTypeFilter('image/jpeg')
        .addMimeTypeFilter('image/png')
        .setType(FilePickerType.Choose)
        .build();
    filePicker.pick().then((path) => {
        emit('input', path);
    });
};
</script>

<script>
export default {
    name: 'EditImageField',
};
</script>

<style scoped>
fieldset {
    display: flex;
    flex-direction: column;
    margin-bottom: 1em;
}

@media (min-width: 1200px) {
    fieldset {
        flex-direction: row;
    }
}

.input-container > * {
    margin: 0;
}

fieldset > .input-container {
    display: flex;
    flex: 1;
    flex-direction: row;
}

fieldset > label {
    display: inline-block;
    width: 10em;
    height: 34px;
    font-weight: bold;
    line-height: 17px;
    vertical-align: top;
}
@media (max-width: 1199px) {
    fieldset > label {
        display: block;
    }
}

.input-container > input {
    flex: 1;
    border-right: 0;
    border-bottom-right-radius: 0;
    border-top-right-radius: 0;
}

.input-container > input + button {
    width: 3em;
    border-radius: 0 var(--border-radius) var(--border-radius) 0;
}

.input-container > input + button > * {
    pointer-events: none;
}
</style>
