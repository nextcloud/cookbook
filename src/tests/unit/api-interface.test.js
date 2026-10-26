// @vitest-environment node

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

import { createServer } from 'node:http';
import { emit } from '@nextcloud/event-bus';

describe('api-interface', () => {
    let server;
    let validToken;
    let receivedTokens;
    let api;

    beforeAll(async () => {
        server = createServer((req, res) => {
            if (req.url === '/index.php/csrftoken') {
                res.end(JSON.stringify({ token: validToken }));
                return;
            }
            receivedTokens.push(req.headers.requesttoken);
            if (req.headers.requesttoken === validToken) {
                res.end();
            } else {
                res.writeHead(412).end(
                    JSON.stringify({ message: 'CSRF check failed' }),
                );
            }
        });
        await new Promise((resolve) => {
            server.listen(0, '127.0.0.1', resolve);
        });

        globalThis.window = globalThis;
        window._oc_webroot = `http://127.0.0.1:${server.address().port}`;

        const apiInterface = await import('../../js/api-interface');
        apiInterface.setApp({ $log: { debug() {}, warn() {} } });
        api = apiInterface.default;
    });

    beforeEach(() => {
        receivedTokens = [];
        validToken = 'old-token';
        emit('csrf-token-update', { token: 'old-token' });
    });

    afterAll(() => {
        server.close();
    });

    it('uses the token announced after a rotation', async () => {
        validToken = 'new-token';
        emit('csrf-token-update', { token: 'new-token' });

        const response = await api.recipes.reindex();

        expect(response.status).toBe(200);
        expect(receivedTokens).toEqual(['new-token']);
    });

    it('recovers from an unannounced token rotation', async () => {
        validToken = 'new-token';

        const retried = await api.recipes.reindex();
        const next = await api.recipes.reindex();

        expect(retried.status).toBe(200);
        expect(next.status).toBe(200);
        expect(receivedTokens).toEqual(['old-token', 'new-token', 'new-token']);
    });
});
