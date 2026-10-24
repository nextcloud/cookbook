// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-or-later

import '../../setup/componentMocks';
import { flushPromises } from '@vue/test-utils';
import api from 'cookbook/js/api-interface';
import RecipeView from 'cookbook/components/RecipeView/RecipeView.vue';
import { mountWithRouter } from '../../helpers/mountWithRouter';

describe('RecipeView onBeforeRouteUpdate fetches correct recipe ID (#3484)', () => {
    beforeEach(() => vi.clearAllMocks());

    it('calls api.recipes.get with the NEW recipe ID on navigation', async () => {
        const { router } = await mountWithRouter(RecipeView, '/recipe/10');

        expect(api.recipes.get).toHaveBeenCalledWith('10');
        vi.clearAllMocks();

        await router.push('/recipe/20');
        await flushPromises();

        expect(api.recipes.get).toHaveBeenCalledWith('20');
    });

    it('fetches correct IDs across sequential navigations', async () => {
        const { router } = await mountWithRouter(RecipeView, '/recipe/1');
        vi.clearAllMocks();

        await router.push('/recipe/2');
        await flushPromises();
        expect(api.recipes.get).toHaveBeenLastCalledWith('2');

        await router.push('/recipe/3');
        await flushPromises();
        expect(api.recipes.get).toHaveBeenLastCalledWith('3');
    });
});
