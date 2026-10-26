// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-or-later

import { vi } from 'vitest';

window.t = vi.fn((_app, msg) => msg);

const stub = { template: '<span><slot /></span>' };

vi.mock('cookbook/js/api-interface', () => ({
    default: {
        recipes: {
            get: vi.fn().mockResolvedValue({
                data: { id: 1, name: 'Test', description: '' },
            }),
            getAll: vi.fn().mockResolvedValue({ data: [] }),
        },
        categories: { getAll: vi.fn().mockResolvedValue({ data: [] }) },
        keywords: { getAll: vi.fn().mockResolvedValue({ data: [] }) },
    },
}));

vi.mock('cookbook/js/modals', () => ({
    showSimpleAlertModal: vi.fn(),
    showSimpleConfirmModal: vi.fn().mockResolvedValue(true),
}));
vi.mock('cookbook/js/title-rename', () => ({ default: vi.fn((s) => s) }));
vi.mock('cookbook/js/yieldCalculator', () => ({
    default: { recalculateIngredients: vi.fn(() => []) },
}));
vi.mock('cookbook/bus', () => ({
    default: { on: vi.fn(), off: vi.fn(), emit: vi.fn() },
}));

vi.mock('@nextcloud/dialogs', () => ({
    showError: vi.fn(),
    showSuccess: vi.fn(),
}));
vi.mock('@nextcloud/vue/components/NcButton', () => ({ default: stub }));
vi.mock('@nextcloud/vue/components/NcActions', () => ({ default: stub }));
vi.mock('@nextcloud/vue/components/NcActionButton', () => ({ default: stub }));

vi.mock('cookbook/composables/dateTimeHandling', () => ({
    parseDateTime: vi.fn(() => null),
}));

vi.mock('cookbook/js/helper', () => ({
    default: {
        shouldReloadContent: vi.fn(() => true),
        goTo: vi.fn(),
    },
}));

vi.mock('icons/ContentCopy.vue', () => ({ default: stub }));
vi.mock('icons/Numeric.vue', () => ({ default: stub }));
vi.mock('cookbook/components/Utilities/LoadingIndicator.vue', () => ({
    default: stub,
}));
vi.mock('cookbook/components/RecipeView/RecipeImages.vue', () => ({
    default: stub,
}));
vi.mock('cookbook/components/RecipeView/RecipeIngredient.vue', () => ({
    default: stub,
}));
vi.mock('cookbook/components/RecipeView/RecipeInstruction.vue', () => ({
    default: stub,
}));
vi.mock('cookbook/components/RecipeKeyword.vue', () => ({ default: stub }));
vi.mock('cookbook/components/RecipeView/RecipeNutritionInfoItem.vue', () => ({
    default: stub,
}));
vi.mock('cookbook/components/RecipeView/RecipeTimer.vue', () => ({
    default: stub,
}));
vi.mock('cookbook/components/RecipeView/RecipeTool.vue', () => ({
    default: stub,
}));

vi.mock('cookbook/components/FormComponents/EditImageField.vue', () => ({
    default: stub,
}));
vi.mock('cookbook/components/FormComponents/EditInputField.vue', () => ({
    default: stub,
}));
vi.mock('cookbook/components/FormComponents/EditInputGroup.vue', () => ({
    default: stub,
}));
vi.mock('cookbook/components/FormComponents/EditMultiselect.vue', () => ({
    default: stub,
}));
vi.mock(
    'cookbook/components/FormComponents/EditMultiselectInputGroup.vue',
    () => ({ default: stub }),
);
vi.mock('cookbook/components/FormComponents/EditTimeField.vue', () => ({
    default: stub,
}));
