// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-or-later

import helpers from '../../../js/helper';

describe('shouldReloadContent', () => {
    it('returns false for identical URLs', () => {
        expect(helpers.shouldReloadContent('/recipe/1', '/recipe/1')).toBe(
            false,
        );
    });

    it('returns true for different recipe IDs', () => {
        expect(helpers.shouldReloadContent('/recipe/1', '/recipe/2')).toBe(
            true,
        );
    });

    it('returns false for different item types', () => {
        expect(helpers.shouldReloadContent('/recipe/1', '/category/1')).toBe(
            false,
        );
    });

    it('returns true for different edit IDs', () => {
        expect(
            helpers.shouldReloadContent('/recipe/1/edit', '/recipe/2/edit'),
        ).toBe(true);
    });

    it('returns true when navigating from create to edit', () => {
        expect(
            helpers.shouldReloadContent('/recipe/create', '/recipe/1/edit'),
        ).toBe(true);
    });

    it('returns true when navigating from view to create', () => {
        expect(helpers.shouldReloadContent('/recipe/1', '/recipe/create')).toBe(
            true,
        );
    });

    it('returns false for URLs with fewer than 2 components', () => {
        expect(helpers.shouldReloadContent('/', '/recipe/1')).toBe(false);
    });
});
