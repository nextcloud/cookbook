/**
 * @jest-environment jsdom
 * @jest-environment-options {"customExportConditions": ["node", "node-addons"]}
 */

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

import { jest } from '@jest/globals';
import { flushPromises, mount } from '@vue/test-utils';

jest.unstable_mockModule('../../../js/helper', () => ({ default: {} }));
jest.unstable_mockModule('caret-pos', () => ({ position: () => ({}) }));

const { default: EditInputGroup } = await import(
    '../../../components/FormComponents/EditInputGroup.vue'
);

window.t = (app, text) => text;

const mountGroup = (entries) =>
    mount(EditInputGroup, {
        props: {
            modelValue: entries,
            fieldType: 'textarea',
            createFieldsOnNewlines: true,
            suggestionOptions: [{ recipe_id: 7, title: 'Cake' }],
        },
        global: {
            stubs: {
                SuggestionsPopup: true,
                TriangleSmallUpIcon: true,
                TriangleSmallDownIcon: true,
            },
        },
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
