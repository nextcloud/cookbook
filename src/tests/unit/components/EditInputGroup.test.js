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
