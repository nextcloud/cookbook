// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-or-later

import { afterEach, describe, expect, it, vi } from 'vitest';

import {
    exportRecipeAsPdf,
    toPdfFilename,
    toPdfSafeText,
} from 'cookbook/js/recipePdfExport';

const instances = [];

vi.mock('jspdf', async (importOriginal) => {
    const { jsPDF: RealPdf } = await importOriginal();
    class TestPdf extends RealPdf {
        constructor(...args) {
            super(...args);
            this.save = vi.fn();
            vi.spyOn(this, 'text');
            instances.push(this);
        }
    }
    return { jsPDF: TestPdf };
});

const t = (app, text) => text;

describe('toPdfSafeText', () => {
    it('keeps latin-1 characters', () => {
        expect(toPdfSafeText('Käse & Brot')).toBe('Käse & Brot');
    });

    it('replaces unsupported characters', () => {
        expect(toPdfSafeText('a\u25bab\u4e2d')).toBe('a>b?');
    });
});

describe('toPdfFilename', () => {
    it('strips forbidden characters', () => {
        expect(toPdfFilename('A/B:C?')).toBe('ABC.pdf');
    });

    it('falls back to a default name', () => {
        expect(toPdfFilename('???')).toBe('recipe.pdf');
        expect(toPdfFilename(undefined)).toBe('recipe.pdf');
    });
});

describe('exportRecipeAsPdf', () => {
    afterEach(() => {
        vi.restoreAllMocks();
        document.body.innerHTML = '';
    });

    it('creates and saves a pdf named after the recipe', () => {
        // happy-dom does not implement innerText
        Object.defineProperty(HTMLElement.prototype, 'innerText', {
            configurable: true,
            get() {
                return this.textContent;
            },
        });
        document.body.innerHTML = `
            <div class="markdown-description">Tasty</div>
            <div class="times"><div class="time"><h4>Cook</h4><span class="timeContainer">10 min</span></div></div>
            <ul class="ingredients"><li class="ingredient">2 eggs</li></ul>
            <div class="instructions"><div class="markdown-instruction">Mix</div></div>`;
        exportRecipeAsPdf(
            { name: 'Pancakes', recipeYield: 4, url: 'https://example.com' },
            4,
            t,
        );

        const pdf = instances.at(-1);
        expect(pdf.save).toHaveBeenCalledWith('Pancakes.pdf');
        const written = pdf.text.mock.calls.map((c) => c[0]).join('|');
        expect(written).toContain('Pancakes');
        expect(written).toContain('- 2 eggs');
        expect(written).toContain('1. Mix');
        expect(written).toContain('Source: https://example.com');
    });
});
