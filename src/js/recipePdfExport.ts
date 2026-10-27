// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

import { jsPDF } from 'jspdf';

type Translate = (app: string, text: string) => string;

interface InfoItem {
    label: string;
    value: string;
}

interface PdfRecipe {
    name?: unknown;
    url?: string | null;
    recipeYield?: unknown;
}

const MARGIN = 18;
const LINE_HEIGHT = 5;

/**
 * Replace characters the built-in PDF fonts cannot render.
 */
export function toPdfSafeText(text: string): string {
    return Array.from(text, (character) => {
        const codePoint = character.codePointAt(0) ?? 0;
        if (codePoint <= 0xff) {
            return character;
        }
        return character === '\u25ba' ? '>' : '?';
    }).join('');
}

/**
 * Build a file name that is valid on common file systems.
 */
export function toPdfFilename(name: unknown): string {
    const filename = String(name ?? 'recipe')
        .replace(/[<>:"/\\|?*]/g, '')
        .split('')
        .filter((character) => character.charCodeAt(0) >= 32)
        .join('')
        .trim()
        .replace(/[. ]+$/g, '');
    return `${filename || 'recipe'}.pdf`;
}

const renderedText = (selector: string): string[] =>
    Array.from(document.querySelectorAll<HTMLElement>(selector))
        .map((element) => element.innerText.trim())
        .filter(Boolean);

const collectInfoItems = (
    recipe: PdfRecipe,
    servings: string | number,
    t: Translate,
): InfoItem[] => {
    const items: InfoItem[] = [];
    if (recipe.recipeYield != null) {
        items.push({
            label: t('cookbook', 'Servings'),
            value: String(servings),
        });
    }
    document
        .querySelectorAll<HTMLElement>('.times .time')
        .forEach((element) => {
            const label = element
                .querySelector<HTMLElement>('h4')
                ?.innerText.trim();
            const value = element
                .querySelector<HTMLElement>('.timeContainer')
                ?.dataset.originalTime?.trim();
            if (label && value) {
                items.push({ label, value });
            }
        });
    return items;
};

const collectInstructions = (): string[] =>
    Array.from(
        document.querySelectorAll<HTMLElement>(
            '.instructions .markdown-instruction',
        ),
    )
        .filter((element) => {
            const child = element.firstElementChild;
            return !(
                element.children.length === 1 &&
                child &&
                /^H[1-6]$/.test(child.tagName)
            );
        })
        .map((element) => element.innerText.trim())
        .filter(Boolean);

/**
 * Render the currently displayed recipe into a PDF and trigger the download.
 *
 * @param recipe The recipe to export
 * @param servings The currently selected number of servings
 * @param t Translation function
 */
export function exportRecipeAsPdf(
    recipe: PdfRecipe,
    servings: string | number,
    t: Translate,
): void {
    const pdf = new jsPDF({ format: 'a4', unit: 'mm' });
    const pageWidth = pdf.internal.pageSize.getWidth();
    const pageHeight = pdf.internal.pageSize.getHeight();
    const contentWidth = pageWidth - MARGIN * 2;
    let cursor = MARGIN;

    const ensureSpace = (height: number) => {
        if (cursor + height > pageHeight - MARGIN) {
            pdf.addPage();
            cursor = MARGIN;
        }
    };

    const addText = (text: string, fontSize = 10) => {
        pdf.setFontSize(fontSize);
        const lines = pdf.splitTextToSize(toPdfSafeText(text), contentWidth);
        for (const line of lines) {
            ensureSpace(LINE_HEIGHT);
            pdf.text(line, MARGIN, cursor);
            cursor += LINE_HEIGHT;
        }
    };

    const addSection = (title: string, entries: string[], entryGap = 0) => {
        if (!entries.length) {
            return;
        }
        ensureSpace(12);
        cursor += 3;
        pdf.setFont('helvetica', 'bold');
        addText(title, 13);
        pdf.setFont('helvetica', 'normal');
        cursor += 2;
        entries.forEach((entry) => {
            addText(entry);
            cursor += entryGap;
        });
    };

    const addInfoBlock = (items: InfoItem[]) => {
        if (!items.length) {
            return;
        }
        const blockHeight = 11;
        const cellWidth = contentWidth / items.length;
        cursor += 3;
        ensureSpace(blockHeight);
        pdf.setDrawColor(210, 210, 210);
        pdf.setLineWidth(0.2);
        pdf.line(MARGIN, cursor, MARGIN + contentWidth, cursor);
        pdf.line(
            MARGIN,
            cursor + blockHeight,
            MARGIN + contentWidth,
            cursor + blockHeight,
        );
        items.forEach((item, index) => {
            const cellX = MARGIN + index * cellWidth;
            pdf.setFont('helvetica', 'normal');
            pdf.setFontSize(7);
            pdf.setTextColor(130, 130, 130);
            pdf.text(
                pdf.splitTextToSize(item.label, cellWidth - 2)[0],
                cellX + 1,
                cursor + 4,
            );
            pdf.setFontSize(9);
            pdf.setTextColor(60, 60, 60);
            pdf.text(
                pdf.splitTextToSize(item.value, cellWidth - 2)[0],
                cellX + 1,
                cursor + 8.5,
            );
        });
        pdf.setTextColor(0, 0, 0);
        cursor += blockHeight + 2;
    };

    pdf.setFont('helvetica', 'bold');
    addText(String(recipe.name ?? t('cookbook', 'Recipe')), 20);
    pdf.setFont('helvetica', 'normal');

    const description = renderedText('.markdown-description').join('\n');
    if (description) {
        cursor += 2;
        addText(description);
    }

    addInfoBlock(collectInfoItems(recipe, servings, t));

    addSection(
        t('cookbook', 'Ingredients'),
        renderedText('.ingredients .ingredient').map((i) => `- ${i}`),
    );
    addSection(
        t('cookbook', 'Tools'),
        renderedText('.tools .markdown-tool').map((tool) => `- ${tool}`),
    );
    addSection(
        t('cookbook', 'Nutrition Information'),
        renderedText('.nutrition-items > li').map((item) =>
            item.replace(/\s+/g, ': '),
        ),
    );

    addSection(
        t('cookbook', 'Instructions'),
        collectInstructions().map(
            (instruction, index) => `${index + 1}. ${instruction}`,
        ),
        2,
    );

    if (recipe.url) {
        cursor += 2;
        addText(`${t('cookbook', 'Source')}: ${recipe.url}`);
    }

    const pageCount = pdf.getNumberOfPages();
    for (let page = 1; page <= pageCount; page++) {
        pdf.setPage(page);
        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(8);
        pdf.text(`${page} / ${pageCount}`, pageWidth - MARGIN, pageHeight - 8, {
            align: 'right',
        });
    }

    pdf.save(toPdfFilename(recipe.name));
}
