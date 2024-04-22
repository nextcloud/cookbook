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
        // Change to String as PropType<DividerType>, when updating this component to use typescript
        type: String,
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
    border-width: 1px;
    border-style: solid none none none;
    border-color: var(--color-border-dark);
    margin: 0;
}

.cb-divider-absolute {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
}

.cb-divider-inset {
    margin-left: 72px;
    margin-inline-end: unset;
    margin-inline-start: 72px;
}

.cb-divider-light {
    border-color: var(--color-border);
}

.cb-divider-middle {
    margin-right: 16px;
    margin-left: 16px;
}

.cb-divider-vertical {
    height: 100%;
    border-style: none solid none none;
}

.cb-divider-flexitem {
    height: auto;
    align-self: stretch;
}

.cb-divider-fullwidth {
    width: 100%;
    flex-grow: 1;
}
</style>
