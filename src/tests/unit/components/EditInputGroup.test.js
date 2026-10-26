// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

import { enableAutoUnmount, flushPromises, mount } from '@vue/test-utils';

vi.mock('cookbook/js/helper', () => ({ default: {} }));
vi.mock('caret-pos', () => ({ position: () => ({}) }));

const { default: EditInputGroup } = await import(
    'cookbook/components/FormComponents/EditInputGroup.vue'
);

window.t = (app, text) => text;

enableAutoUnmount(afterEach);

const mountGroup = (entries) =>
    mount(EditInputGroup, {
        attachTo: document.body,
        props: {
            modelValue: entries,
            fieldType: 'textarea',
            createFieldsOnNewlines: true,
            suggestionOptions: [{ recipe_id: 7, title: 'Cake' }],
        },
        global: { stubs: { SuggestionsPopup: true } },
    });

const paste = async (field, text) => {
    const event = new Event('paste', { bubbles: true, cancelable: true });
    event.clipboardData = { getData: () => text };
    field.element.dispatchEvent(event);
    await flushPromises();
};

describe('EditInputGroup multi-line paste', () => {
    test('replaces the empty entry it was pasted into', async () => {
        const entries = ['Step one', '', 'Step two'];
        const wrapper = mountGroup(entries);

        await paste(wrapper.findAll('textarea')[1], 'Line A\nLine B');

        expect(entries).toEqual(['Step one', 'Line A', 'Line B', 'Step two']);
    });
});

describe('EditInputGroup recipe link suggestions', () => {
    test('inserts the selected recipe link into the entry being edited', async () => {
        const entries = ['#', 'Step two'];
        const wrapper = mountGroup(entries);
        const field = wrapper.findAll('textarea')[0];
        field.element.setSelectionRange(1, 1);

        await field.trigger('keyup', { key: '#' });
        wrapper
            .findComponent({ name: 'SuggestionsPopup' })
            .vm.$emit('suggestions-selected', { recipe_id: 7 });
        await flushPromises();

        expect(entries).toEqual(['#r/7', 'Step two']);
    });
});

describe('EditInputGroup Enter key', () => {
    test('adds a new entry when pressed in the last entry', async () => {
        const entries = ['Step one'];
        const wrapper = mountGroup(entries);

        await wrapper.find('textarea').trigger('keydown', { key: 'Enter' });
        await flushPromises();

        expect(entries).toEqual(['Step one', '']);
        expect(document.activeElement).toBe(
            wrapper.findAll('textarea')[1].element,
        );
    });

    test('focuses the next entry when pressed in another entry', async () => {
        const entries = ['Step one', 'Step two', 'Step three'];
        const wrapper = mountGroup(entries);

        await wrapper
            .findAll('textarea')[0]
            .trigger('keydown', { key: 'Enter' });
        await flushPromises();

        expect(entries).toEqual(['Step one', 'Step two', 'Step three']);
        expect(document.activeElement).toBe(
            wrapper.findAll('textarea')[1].element,
        );
    });
});
