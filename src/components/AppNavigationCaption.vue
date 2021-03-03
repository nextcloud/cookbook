<!--
 - This component is based on multiple Nextcloud Vue components available
 - at https://github.com/nextcloud/nextcloud-vue/ which are licensed under
 - the GNU Affero General Public License as published by the Free Software
 - Foundation. The relevant components are `AppNavigationItem.vue` (which
 - is Copyright (c) 2018 John Molakvoæ <skjnldsv@protonmail.com>),
 - `AppNavigationCaption.vue`, and `variables.scss`.
 -
 -->

<docs>

# Usage

### Simple element

* With an icon:

```
<AppNavigationCaption title="My title" icon="icon-category-enabled" />

```
* With a spinning loader instead of the icon:

<AppNavigationCaption title="Loading Item" :loading="true" />

### Element with actions
Wrap the children in a template. If you have more than 2 actions, a popover menu and a menu
button will be automatically created.

```
<AppNavigationCaption title="Item with actions" icon="icon-category-enabled">
	<template slot="actions">
		<ActionButton icon="icon-edit" @click="alert('Edit')">
			Edit
		</ActionButton>
		<ActionButton icon="icon-delete" @click="alert('Delete')">
			Delete
		</ActionButton>
		<ActionLink icon="icon-external" title="Link" href="https://nextcloud.com" />
	</template>
</AppNavigationCaption>
```

</docs>

<template>
    <li class="app-navigation-caption-mod">
        <div class="app-navigation-caption-div">
            <!-- icon if not collapsible -->
            <!-- never show the icon over the collapsible if mobile -->
            <div
                :class="{
                    'icon-loading-small': loading,
                    [icon]: icon && isIconShown,
                }"
                class="app-navigation-caption-icon"
            >
                <slot v-if="!loading" v-show="isIconShown" name="icon" />
            </div>
            <span class="app-navigation-caption__title" :title="title">
                {{ title }}
            </span>
        </div>
        <!-- Actions -->
        <div v-if="hasUtils" class="app-navigation-entry__utils">
            <Actions
                menu-align="right"
                :placement="menuPlacement"
                :open="menuOpen"
                :force-menu="forceMenu"
                :default-icon="menuIcon"
                @update:open="onMenuToggle"
            >
                <slot name="actions" />
            </Actions>
        </div>
    </li>
</template>

<script>
import Actions from "@nextcloud/vue/dist/Components/Actions"
import isMobile from "@nextcloud/vue/dist/Mixins/isMobile"

export default {
    name: "AppNavigationCaption",

    components: {
        Actions,
    },
    mixins: [isMobile],
    props: {
        /**
         * The title of the element.
         */
        title: {
            type: String,
            required: true,
        },
        /**
         * Refers to the icon on the left, this prop accepts a class
         * like 'icon-category-enabled'.
         */
        icon: {
            type: String,
            default: "",
        },
        /**
         * Displays a loading animated icon on the left of the element
         * instead of the icon.
         */
        loading: {
            type: Boolean,
            default: false,
        },
        /**
         * The actions menu open state (synced)
         */
        menuOpen: {
            type: Boolean,
            default: false,
        },
        /**
         * Force the actions to display in a three dot menu
         */
        forceMenu: {
            type: Boolean,
            default: false,
        },
        /**
         * The action's menu default icon
         */
        menuIcon: {
            type: String,
            default: undefined,
        },
        /**
         * The action's menu direction
         */
        menuPlacement: {
            type: String,
            default: "bottom",
        },
    },
    computed: {
        hasUtils() {
            if (this.editing) {
                return false
            }
            if (
                this.$slots.actions ||
                this.$slots.counter ||
                this.editable ||
                this.undo
            ) {
                return true
            }
            return false
        },
        // is the icon shown?
        // we don't show it on mobile if the entry is collapsible
        // we show the collapse toggle directly!
        isIconShown() {
            return !this.collapsible || (this.collapsible && !this.isMobile)
        },
    },
    methods: {
        // sync opened menu state with prop
        onMenuToggle(state) {
            this.$emit("update:menuOpen", state)
        },
        // forward click event
        onClick(event) {
            this.$emit("click", event)
        },
    },
}
</script>

<style lang="scss" scoped>
/* stylelint-disable scss/at-import-partial-extension-blacklist */
@import "@nextcloud/vue/src/assets/variables.scss";
/* stylelint-enable scss/at-import-partial-extension-blacklist */

.app-navigation-caption-mod {
    display: flex;
    overflow: hidden;
    flex: 0 0 auto;
    order: 1;
    opacity: 0.7;
}

// extra top space if it's not the first item on the list
.app-navigation-caption-mod:not(:first-child) {
    margin-top: $clickable-area / 2;
}

// Main entry link
.app-navigation-caption-div {
    z-index: 100; /* above the bullet to allow click */
    display: flex;
    overflow: hidden;
    min-height: $clickable-area;
    box-sizing: border-box;
    flex: 1 1 0;
    padding: 0;
    background-position: $icon-margin center;
    background-repeat: no-repeat;
    background-size: $icon-size $icon-size;
    box-shadow: none !important;
    color: var(--color-text-maxcontrast);
    font-weight: bold;
    line-height: $clickable-area;
    white-space: nowrap;

    .app-navigation-caption-icon {
        display: flex;
        width: $clickable-area;
        height: $clickable-area;
        flex: 0 0 $clickable-area;
        align-items: center;
        justify-content: center;
        background-size: $icon-size $icon-size;
    }
    /* stylelint-disable selector-class-pattern */
    .app-navigation-caption__title {
        overflow: hidden;
        max-width: 100%;
        text-overflow: ellipsis;
        white-space: nowrap;
        // padding-left: 6px;
    }
    /* stylelint-enable selector-class-pattern */
}

/* counter and actions */
/* stylelint-disable selector-class-pattern */
.app-navigation-entry__utils {
    display: flex;
    flex: 0 1 auto;
    align-items: center;
    // visually balance the menu so it's not
    // stuck to the scrollbar
    .action-item {
        margin-right: 2px;
    }
}
/* stylelint-enable selector-class-pattern */
</style>
