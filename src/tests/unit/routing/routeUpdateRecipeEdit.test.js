// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-or-later

import { vi } from 'vitest';
import '../../setup/componentMocks';
import { flushPromises } from '@vue/test-utils';
import { showSimpleConfirmModal } from 'cookbook/js/modals';
import api from 'cookbook/js/api-interface';
import RecipeEdit from 'cookbook/components/RecipeEdit.vue';
import { mountWithRouter } from '../../helpers/mountWithRouter';

describe('RecipeEdit guards edits on route param change (#3497)', () => {
    beforeEach(() => vi.clearAllMocks());

    async function mountEdit(initialRoute = '/recipe/10/edit') {
        const result = await mountWithRouter(RecipeEdit, initialRoute, {
            routePath: '/recipe/:id/edit',
            extraRoutes: [
                { path: '/recipe/create', component: RecipeEdit },
                {
                    path: '/recipe/:id/clone',
                    name: 'recipe-clone',
                    component: RecipeEdit,
                },
            ],
        });
        await flushPromises();
        return result;
    }

    function getEditState(wrapper) {
        return wrapper.findComponent(RecipeEdit).vm.$.setupState;
    }

    it('prompts when navigating to another recipe with dirty form', async () => {
        showSimpleConfirmModal.mockResolvedValue(false);
        const { router, wrapper } = await mountEdit();

        getEditState(wrapper).formDirty = true;

        await router.push('/recipe/20/edit');
        await flushPromises();

        expect(showSimpleConfirmModal).toHaveBeenCalled();
        expect(router.currentRoute.value.path).toBe('/recipe/10/edit');
    });

    it('allows navigation when user confirms discard', async () => {
        showSimpleConfirmModal.mockResolvedValue(true);
        const { router, wrapper } = await mountEdit();

        getEditState(wrapper).formDirty = true;

        await router.push('/recipe/20/edit');
        await flushPromises();

        expect(showSimpleConfirmModal).toHaveBeenCalled();
        expect(router.currentRoute.value.path).toBe('/recipe/20/edit');
        expect(api.recipes.get).toHaveBeenLastCalledWith('20');
    });

    it('does not prompt when form is clean', async () => {
        const { router } = await mountEdit();

        await router.push('/recipe/20/edit');
        await flushPromises();

        expect(showSimpleConfirmModal).not.toHaveBeenCalled();
        expect(router.currentRoute.value.path).toBe('/recipe/20/edit');
    });

    it('initializes clone when switching from edit to clone of same recipe', async () => {
        const data = { id: 10, name: 'Soup', keywords: '', recipeCategory: '' };
        api.recipes.get
            .mockResolvedValueOnce({ data: { ...data } })
            .mockResolvedValueOnce({ data: { ...data } });
        const { router, wrapper } = await mountEdit();

        await router.push('/recipe/10/clone');
        await flushPromises();

        expect(getEditState(wrapper).recipe.id).toBe(0);
    });
});
