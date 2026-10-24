// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-or-later

import { mount, flushPromises } from '@vue/test-utils';
import { defineComponent, h } from 'vue';
import { createRouter, createMemoryHistory, RouterView } from 'vue-router';
import { createTestingPinia } from '@pinia/testing';

const App = defineComponent({ render: () => h(RouterView) });
const Blank = defineComponent({ render: () => h('div') });

export async function mountWithRouter(component, initialRoute, options = {}) {
    const routes = [
        { path: options.routePath ?? '/recipe/:id', component },
        { path: '/', component: Blank },
        ...(options.extraRoutes ?? []),
    ];

    const router = createRouter({ history: createMemoryHistory(), routes });
    const pinia = createTestingPinia({ stubActions: false });

    router.push(initialRoute);
    await router.isReady();

    const wrapper = mount(App, {
        global: {
            plugins: [router, pinia],
            config: {
                warnHandler: () => {},
            },
        },
    });
    await flushPromises();

    return { router, wrapper, pinia };
}
