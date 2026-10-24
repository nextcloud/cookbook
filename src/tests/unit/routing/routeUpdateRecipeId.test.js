/**
 * @jest-environment jsdom
 */

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-or-later

/**
 * Regression test for #3484: when navigating between /recipe/A and /recipe/B,
 * onBeforeRouteUpdate fires before Vue Router updates the reactive route object.
 * The setup function must use to.params.id (the target), not route.params.id (stale).
 */
import { mount, flushPromises } from '@vue/test-utils';
import { defineComponent, computed } from 'vue';
import { createRouter, createMemoryHistory } from 'vue-router';
import { onBeforeRouteUpdate, useRoute, RouterView } from 'vue-router';
import helpers from '../../../js/helper';

const setupSpy = jest.fn();

const FixedRecipeView = defineComponent({
    template: '<div>Recipe {{ routeId }}</div>',
    setup() {
        const route = useRoute();
        const routeId = computed(() => String(route.params.id ?? ''));

        const setup = (id) => {
            const recipeId = id ?? routeId.value;
            setupSpy(recipeId);
        };

        onBeforeRouteUpdate((to, from, next) => {
            next();
            if (helpers.shouldReloadContent(from.fullPath, to.fullPath)) {
                setup(String(to.params.id ?? ''));
            }
        });

        setup();

        return { routeId };
    },
});

const BuggyRecipeView = defineComponent({
    template: '<div>Recipe {{ routeId }}</div>',
    setup() {
        const route = useRoute();
        const routeId = computed(() => String(route.params.id ?? ''));

        const setup = () => {
            setupSpy(routeId.value);
        };

        onBeforeRouteUpdate((to, from, next) => {
            next();
            if (helpers.shouldReloadContent(from.fullPath, to.fullPath)) {
                setup();
            }
        });

        setup();

        return { routeId };
    },
});

const App = defineComponent({
    template: '<router-view />',
    components: { RouterView },
});

const Home = defineComponent({ template: '<div>Home</div>' });

function createTestRouter(recipeComponent) {
    return createRouter({
        history: createMemoryHistory(),
        routes: [
            { path: '/recipe/:id', component: recipeComponent },
            { path: '/', component: Home },
        ],
    });
}

describe('onBeforeRouteUpdate recipe ID passing (#3484)', () => {
    beforeEach(() => {
        setupSpy.mockClear();
    });

    it('fixed: passes the NEW recipe ID to setup during route update', async () => {
        const router = createTestRouter(FixedRecipeView);
        router.push('/recipe/10');
        await router.isReady();

        const wrapper = mount(App, {
            global: { plugins: [router] },
        });

        expect(setupSpy).toHaveBeenCalledWith('10');
        setupSpy.mockClear();

        await router.push('/recipe/20');
        await flushPromises();

        expect(setupSpy).toHaveBeenCalledWith('20');

        wrapper.unmount();
    });

    it('buggy: reads STALE route.params.id without the fix', async () => {
        const router = createTestRouter(BuggyRecipeView);
        router.push('/recipe/10');
        await router.isReady();

        const wrapper = mount(App, {
            global: { plugins: [router] },
        });

        expect(setupSpy).toHaveBeenCalledWith('10');
        setupSpy.mockClear();

        await router.push('/recipe/20');
        await flushPromises();

        // The buggy version reads routeId.value (derived from route.params.id)
        // inside onBeforeRouteUpdate. Due to Vue Router's timing, route.params.id
        // may still be '10' when the guard runs — demonstrating the stale read.
        const calledId = setupSpy.mock.calls[0]?.[0];
        expect(calledId).toBe('10');

        wrapper.unmount();
    });

    it('fixed: handles sequential navigation through multiple recipes', async () => {
        const router = createTestRouter(FixedRecipeView);
        router.push('/recipe/1');
        await router.isReady();

        const wrapper = mount(App, {
            global: { plugins: [router] },
        });

        setupSpy.mockClear();

        await router.push('/recipe/2');
        await flushPromises();
        expect(setupSpy).toHaveBeenLastCalledWith('2');

        await router.push('/recipe/3');
        await flushPromises();
        expect(setupSpy).toHaveBeenLastCalledWith('3');

        wrapper.unmount();
    });
});
