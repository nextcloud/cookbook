<!--
    SPDX-License-Identifier: LicenseRef-cookbook-AGPL AND LicenseRef-MudBlazor-MIT
    SPDX-FileCopyrightText: © 2024 Cookbook team <>

    Based on MudBlazor
    https://github.com/MudBlazor/MudBlazor/blob/d6a126833bde2b10096b59d53b10f19cb9354bdd/src/MudBlazor/Components/Divider/MudDivider.razor
    https://github.com/MudBlazor/MudBlazor/blob/d6a126833bde2b10096b59d53b10f19cb9354bdd/src/MudBlazor/Styles/components/_divider.scss
-->
<template>
    <hr
        class="cb-divider"
        :class="[
            `cb-divider-${dividerType}`,
            {
                'cb-divider-absolute': absolute,
                'cb-divider-flexitem': flexItem,
                'cb-divider-light': light,
                'cb-divider-vertical': vertical,
            },
        ]"
    />
</template>

<script setup>
import DividerType from 'cookbook/components/Utilities/Divider/DividerType';

defineProps({
    absolute: {
        type: Boolean,
        default: false,
    },
    dividerType: {
        type: Object,
        default: DividerType.FullWidth,
    },
    flexItem: {
        type: Boolean,
        default: false,
    },
    light: {
        type: Boolean,
        default: false,
    },
    vertical: {
        type: Boolean,
        default: false,
    },
});
</script>

<style scoped lang="scss">
.cb-divider {
    flex-shrink: 0;
    margin: 0;
    border-style: solid none none none;
    border-width: 1px;
    border-color: var(--color-border-dark);
}

.cb-divider-absolute {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
}

.cb-divider-inset {
    margin-left: 72px;
    margin-inline-start: 72px;
    margin-inline-end: unset;
}

.cb-divider-light {
    border-color: var(--color-border);
}

.cb-divider-middle {
    margin-left: 16px;
    margin-right: 16px;
}

.cb-divider-vertical {
    border-style: none solid none none;
    height: 100%;
}

.cb-divider-flexitem {
    align-self: stretch;
    height: auto;
}

.cb-divider-fullwidth {
    flex-grow: 1;
    width: 100%;
}
</style>
