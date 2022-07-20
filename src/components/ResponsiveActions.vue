<template>
    <div ref="expandedButtonWrapper" class="actions">
        <div
            v-if="mode==LayoutOption.WIDE"
            v-show="hasMultipleActions || forceMenu"
            :class="{ 'action-item--open': opened }"
            class="action-item"
        >
            <!-- menu content -->
            <ul :id="randomId" tabindex="-1" class="expanded-button-wrapper">
                <template v-if="true">
                    <slot />
                </template>
            </ul>
        </div>

        <div
            v-else-if="mode==LayoutOption.MEDIUM"
            class="action-item"
        >
            <button
                v-for='child in children'
                v-tooltip="{boundariesElement: 'body', content: child.text, placement: 'auto'}"
                class="action-item action-item--single"
                rel="nofollow noreferrer noopener"
                :class="{[child.icon]: child.icon}"
                @click="child.$listeners.click"
            />

            <!-- fake slot to gather main action -->
            <span :aria-hidden="true" hidden>
                <!-- @slot All action elements passed into the default slot will be used -->
                <ul :id="randomId" tabindex="-1" class="compressed-button-wrapper">
                    <template v-if="true">
                        <slot />
                    </template>
                </ul>
            </span>
        </div>

        <!-- more than one action -->
        <div
            v-else-if="mode==LayoutOption.NARROW"
            :class="{ 'action-item--open': opened }"
            class="action-item"
        >
            <!-- If more than one action, create a popovermenu -->
            <Popover
                :delay="0"
                :handle-resize="true"
                :open.sync="opened"
                :placement="placement"
                :boundaries-element="boundariesElement"
                :container="container"
                @show="openMenu"
                @after-show="onOpen"
                @hide="closeMenu"
            >
                <!-- Menu open/close trigger button -->
                <template #trigger>
                    <button
                        ref="menuButton"
                        :disabled="disabled"
                        class="icon action-item__menutoggle"
                        :class="{
                            [defaultIcon]: !iconSlotIsPopulated,
                            'action-item__menutoggle--with-title': menuTitle,
                            'action-item__menutoggle--with-icon-slot':
                                iconSlotIsPopulated,
                            'action-item__menutoggle--default-icon':
                                !iconSlotIsPopulated && defaultIcon === '',
                            'action-item__menutoggle--primary': primary,
                        }"
                        aria-haspopup="true"
                        :aria-label="ariaLabel"
                        :aria-controls="randomId"
                        :aria-expanded="opened ? 'true' : 'false'"
                        test-attr="1"
                        type="button"
                        @focus="onFocus"
                        @blur="onBlur"
                    >
                        <slot v-if="iconSlotIsPopulated" name="icon" />
                        <DotsHorizontal
                            v-else-if="defaultIcon === ''"
                            :size="20"
                            decorative
                        />
                        {{ menuTitle }}
                    </button>
                </template>

                <!-- Menu content -->
                <div
                    v-show="opened"
                    ref="menu"
                    :class="{ open: opened }"
                    tabindex="-1"
                    @keydown.up.exact="focusPreviousAction"
                    @keydown.down.exact="focusNextAction"
                    @keydown.tab.exact="focusNextAction"
                    @keydown.shift.tab.exact="focusPreviousAction"
                    @keydown.page-up.exact="focusFirstAction"
                    @keydown.page-down.exact="focusLastAction"
                    @keydown.esc.exact.prevent="closeMenu"
                    @mousemove="onMouseFocusAction"
                >
                    <!-- menu content -->
                    <ul :id="randomId" tabindex="-1">
                        <template v-if="opened">
                            <slot />
                        </template>
                    </ul>
                </div>
            </Popover>
        </div>
    </div>
</template>
<script>
import DotsHorizontal from "vue-material-design-icons/DotsHorizontal"

/* import Tooltip from "../../directives/Tooltip" */
import Tooltip from "@nextcloud/vue/dist/Directives/Tooltip"
/* import GenRandomId from "../../utils/GenRandomId" */
/* import GenRandomId from "@nextcloud/vue/dist/utils/GenRandomId" */
/* import { t } from "../../l10n" */
/* import Popover from "../Popover" */
import Popover from "@nextcloud/vue/dist/Components/Popover"

const focusableSelector = ".focusable"
const GenRandomId = (length) => {
	return Math.random()
		.toString(36)
		.replace(/[^a-z]+/g, '')
		.slice(0, length || 5)
}

// Used to calculate the width we would have if all text is removed and only icons are shown
const ICON_WIDTH = 44;

// Enum
const LayoutOption = Object.freeze({
    // In the wide mode, show the button icons and text
    WIDE: Symbol("wide"),
    // In medium mode, show only the icons of each button, but keep the text in
    // a tooltip
    MEDIUM: Symbol("medium"),
    // In the narrow mode, show a 3-dot menu with all buttons
    NARROW: Symbol("narrow"),
})


/**
 * The Actions component can be used to display one ore more actions.
 * If only a single action is provided, it will be rendered as an inline icon.
 * For more, a menu indicator will be shown and a popovermenu containing the
 * actions will be opened on click.
 *
 * @since 0.10.0
 */
export default {
    name: "Actions",

    created()  {
        window.addEventListener("resize", this.resizeHandler);

        this.LayoutOption = LayoutOption;
    },

    destroyed()  {
        window.removeEventListener("resize", this.resizeHandler);
    },


    directives: {
        tooltip: Tooltip,
    },

    components: {
        DotsHorizontal,
        Popover,

        // Component to render the first action icon slot content (vnodes)
        VNodes: {
            functional: true,
            render: (h, context) => context.props.vnodes,
        },
    },

    props: {
        /**
         * Specify the open state of the popover menu
         */
        open: {
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
         * Force the title to show for single actions
         */
        forceTitle: {
            type: Boolean,
            default: false,
        },

        /**
         * Specify the menu title
         */
        menuTitle: {
            type: String,
            default: null,
        },

        /**
         * Apply primary styling for this menu
         */
        primary: {
            type: Boolean,
            default: false,
        },

        /**
         * Icon to show for the toggle menu button
         * when more than one action is inside the actions component.
         * Only replace the default three-dot icon if really necessary.
         */
        defaultIcon: {
            type: String,
            default: "",
        },

        /**
         * Aria label for the actions menu
         */
        ariaLabel: {
            type: String,
            default: t("Actions"),
        },

        /**
         * Wanted direction of the menu
         */
        placement: {
            type: String,
            default: "bottom",
        },

        /**
         * DOM element for the actions' popover boundaries
         */
        boundariesElement: {
            type: Element,
            default: () => document.querySelector("body"),
        },

        /**
         * Selector for the actions' popover container
         */
        container: {
            type: String,
            default: "body",
        },

        /**
         * Disabled state of the main button (single action or menu toggle)
         */
        disabled: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            actions: [],
            opened: this.open,
            focusIndex: 0,
            randomId: "menu-" + GenRandomId(),
            // Making children reactive!
            // By binding this here, vuejs will track the object content
            // Needed for firstAction reactivity !!!
            children: this.$children,
            firstAction: {},
            mode: LayoutOption.WIDE,
            childWidth: null,
        }
    },

    computed: {
        /**
         * Is there more than one action?
         * @returns {boolean}
         */
        hasMultipleActions() {
            return this.actions.length > 1
        },
        /**
         * Is there any first action ?
         * And is it allowed as a standalone element ?
         * @returns {boolean}
         */
        isValidSingleAction() {
            return true
            // return this.actions.length === 1 && this.firstActionElement !== null
        },
        /**
         * Return the title of the single action if forced
         * @returns {string}
         */
        singleActionTitle() {
            return this.forceTitle ? this.menuTitle : ""
        },
        isDisabled() {
            return (
                this.disabled ||
                (this.actions.length === 1 &&
                    this.firstAction?.$props?.disabled)
            )
        },
        /**
         * First action vnode
         * @returns {Object} return the first action vue vnode
         */
        firstActionVNode() {
            return this.actions[0]
        },
        /**
         * Binding of the first action to the template
         * @returns {Object} vue template v-bind shortcut
         */
        firstActionBinding() {
            if (
                this.firstActionVNode &&
                this.firstActionVNode.componentOptions
            ) {
                const tag = this.firstActionVNode.componentOptions.tag
                if (tag === "ActionLink") {
                    return {
                        is: "a",
                        href: this.firstAction.href,
                        target: this.firstAction.target,
                        "aria-label": this.firstAction.ariaLabel,
                        ...this.firstAction.$attrs,
                        ...this.firstAction.$props,
                    }
                }
                if (tag === "ActionRouter") {
                    return {
                        is: "router-link",
                        to: this.firstAction.to,
                        exact: this.firstAction.exact,
                        "aria-label": this.firstAction.ariaLabel,
                        ...this.firstAction.$attrs,
                        ...this.firstAction.$props,
                    }
                }
                if (tag === "ActionButton") {
                    return {
                        is: "button",
                        "aria-label": this.firstAction.ariaLabel,
                        ...this.firstAction.$attrs,
                        ...this.firstAction.$props,
                    }
                }
            }
            // other action types are not allowed as standalone buttons
            return null
        },

        // return the event to bind if the firstActionVNode have an action
        firstActionEvent() {
            return this.firstActionVNode?.componentOptions?.listeners?.click
        },
        firstActionEventBinding() {
            return this.firstActionEvent ? "click" : null
        },
        // return the first action icon slot vnodes array
        firstActionIconSlot() {
            return this.firstAction?.$slots?.icon
        },
        firstActionClass() {
            const staticClass =
                this.firstActionVNode && this.firstActionVNode.data.staticClass
            const dynClass =
                this.firstActionVNode && this.firstActionVNode.data.class
            return (staticClass + " " + dynClass).trim()
        },

        iconSlotIsPopulated() {
            return !!this.$slots.icon
        },
    },

    watch: {
        // Watch parent prop
        open(state) {
            if (state === this.opened) {
                return
            }

            this.opened = state
        },
        /**
         * Reactive binding to the first children
         * Since we're here, it means we already passed all the proper checks
         * we can assume the first action is the first children too
         */
        children() {
            if (!this.childWidth) {

                const wrapper = this.$refs.expandedButtonWrapper;
                const children = [...wrapper.querySelector('ul').children];
                this.childWidth = children
                    .map(el => el.offsetWidth)
                    .reduce((a, b) => a + b);
                this.numChildren = children.length;
                this.mode = this.getMode(wrapper.offsetWidth);
            }

            // Fix #2529, slots maybe not available on creation lifecycle
            // first action vue children object
            this.firstAction = this.children[0] ? this.children[0] : {}
        },
    },
    beforeMount() {
        // init actions
        this.initActions()
    },
    mounted() {
        this.$nextTick(() => {
        })
    },
    beforeUpdate() {
        // ! since we're using $slots to manage our actions
        // ! we NEED to update the actions if anything change

        // update children & actions
        // no need to init actions again since we bound it to $children
        // and the array is now reactive
        // init actions
        this.initActions()
    },

    methods: {
        // MENU STATE MANAGEMENT
        getMode(wrapperWidth) {
            if (wrapperWidth >= this.childWidth) {
                return LayoutOption.WIDE;
            } else if (wrapperWidth > ICON_WIDTH * this.numChildren) {
                return LayoutOption.MEDIUM;
            } else {
                return LayoutOption.NARROW;
            }
        },
        resizeHandler(e) {
            const wrapper = this.$refs.expandedButtonWrapper;
            const width = wrapper.offsetWidth;

            this.mode = this.getMode(width);
        },
        openMenu(e) {
            if (this.opened) {
                return
            }

            this.opened = true

            /**
             * Event emitted when the popover menu open state is changed
             * @type {boolean}
             */
            this.$emit("update:open", true)

            /**
             * Event emitted when the popover menu is closed
             */
            this.$emit("open")
        },
        closeMenu(e) {
            if (!this.opened) {
                return
            }

            this.opened = false

            /**
             * Event emitted when the popover menu open state is changed
             * @type {boolean}
             */
            this.$emit("update:open", false)

            /**
             * Event emitted when the popover menu is closed
             */
            this.$emit("close")

            // close everything
            this.opened = false
            this.focusIndex = 0

            // focus back the menu button
            this.$refs.menuButton.focus()
        },

        onOpen(event) {
            this.$nextTick(() => {
                this.focusFirstAction(event)
            })
        },

        // MENU KEYS & FOCUS MANAGEMENT
        // focus nearest focusable item on mouse move
        // DO NOT change the focus if the target is already focused
        // this will prevent issues with input being unfocused
        // on mouse move
        onMouseFocusAction(event) {
            if (document.activeElement === event.target) {
                return
            }

            const menuItem = event.target.closest("li")
            if (menuItem) {
                const focusableItem = menuItem.querySelector(focusableSelector)
                if (focusableItem) {
                    const focusList =
                        this.$refs.menu.querySelectorAll(focusableSelector)
                    const focusIndex = [...focusList].indexOf(focusableItem)
                    if (focusIndex > -1) {
                        this.focusIndex = focusIndex
                        this.focusAction()
                    }
                }
            }
        },
        removeCurrentActive() {
            const currentActiveElement =
                this.$refs.menu.querySelector("li.active")
            if (currentActiveElement) {
                currentActiveElement.classList.remove("active")
            }
        },
        focusAction() {
            // TODO: have a global disabled state for non input elements
            const focusElement =
                this.$refs.menu.querySelectorAll(focusableSelector)[
                    this.focusIndex
                ]
            if (focusElement) {
                this.removeCurrentActive()
                const liMenuParent = focusElement.closest("li.action")
                focusElement.focus()
                if (liMenuParent) {
                    liMenuParent.classList.add("active")
                }
            }
        },
        focusPreviousAction(event) {
            if (this.opened) {
                if (this.focusIndex === 0) {
                    // First element overflows to body-navigation (no preventDefault!) and closes Actions-menu
                    this.closeMenu()
                } else {
                    this.preventIfEvent(event)
                    this.focusIndex = this.focusIndex - 1
                }
                this.focusAction()
            }
        },
        focusNextAction(event) {
            if (this.opened) {
                const indexLength =
                    this.$refs.menu.querySelectorAll(focusableSelector).length -
                    1
                if (this.focusIndex === indexLength) {
                    // Last element overflows to body-navigation (no preventDefault!) and closes Actions-menu
                    this.closeMenu()
                } else {
                    this.preventIfEvent(event)
                    this.focusIndex = this.focusIndex + 1
                }
                this.focusAction()
            }
        },
        focusFirstAction(event) {
            if (this.opened) {
                this.preventIfEvent(event)
                this.focusIndex = 0
                this.focusAction()
            }
        },
        focusLastAction(event) {
            if (this.opened) {
                this.preventIfEvent(event)
                this.focusIndex =
                    this.$el.querySelectorAll(focusableSelector).length - 1
                this.focusAction()
            }
        },

        preventIfEvent(event) {
            if (event) {
                event.preventDefault()
                event.stopPropagation()
            }
        },

        // ACTIONS MANAGEMENT
        // exec the first action
        execFirstAction(event) {
            if (this.firstActionEvent) {
                this.firstActionEvent(event)
            }
        },
        initActions() {
            // filter out invalid slots
            this.actions = (this.$slots.default || []).filter(
                (node) => !!node && !!node.componentOptions
            )
        },
        onFocus(event) {
            this.$emit("focus", event)
        },
        onBlur(event) {
            this.$emit("blur", event)
        },
    },
}
</script>

<style lang="scss" scoped>
@use 'sass:math';
@import '../assets/variables.scss';
.actions {
    // Make actions take full width
    flex-grow: 1;
    // Align actions right
    display: flex;
    justify-content: flex-end;

    // Required to shrink when not enough space
    overflow-x: hidden;
}

.action-item {
    position: relative;

    // put a grey round background when menu is opened
    // or hover-focused
    &--single:hover,
    &--single:focus,
    &--single:active,
    &__menutoggle:hover,
    &__menutoggle:focus,
    &__menutoggle:active {
        // good looking on dark AND white bg, override server styling
        background-color: $icon-focus-bg !important;
        opacity: $opacity_full;
    }

    // TODO: handle this in the future button component
    &__menutoggle:disabled,
    &--single:disabled {
        opacity: 0.3 !important;
    }

    &.action-item--open .action-item__menutoggle {
        background-color: $action-background-hover;
        opacity: $opacity_full;
    }

    // icons
    &--single,
    &__menutoggle {
        width: auto;
        min-width: $clickable-area;
        height: $clickable-area;
        box-sizing: border-box;
        padding: 0;
        border: none;
        margin: 0;
        background-color: transparent;
        border-radius: math.div($clickable-area, 2);
        cursor: pointer;

        &--with-title {
            position: relative;
            padding: 0 $icon-margin;
            padding-left: $clickable-area;
            border: 1px solid var(--color-border-dark);
            // with a title, we need to display this as a real button
            background-color: var(--color-background-dark);
            background-position: $icon-margin center;
            font-size: inherit;
            opacity: $opacity_full;
            white-space: nowrap;

            // non-background icon class
            // image slot
            ::v-deep span {
                position: absolute;
                top: 0;
                left: 0;
                width: 24px;
                height: 24px;
                line-height: $icon-size;
            }
        }
    }

    &::v-deep .material-design-icon {
        width: $clickable-area;
        height: $clickable-area;
        opacity: $opacity_full;

        .material-design-icon__svg {
            vertical-align: middle;
        }
    }

    // icon-more
    &__menutoggle {
        // align menu icon in center
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        line-height: $icon-size;
        opacity: $opacity_normal;

        &--primary {
            border: none;
            background-color: var(--color-primary-element);
            color: var(--color-primary-text);
            opacity: $opacity_full;
            .action-item--open &,
            &:hover,
            &:focus,
            &:active {
                background-color: var(--color-primary-element-light) !important;
                color: var(--color-primary-text) !important;
            }
        }
    }

    &--single {
        opacity: $opacity_normal;
        &:hover,
        &:focus,
        &:active {
            opacity: $opacity_full;
        }
        // hide anything the slot is displaying
        & > [hidden] {
            display: none;
        }
    }

    .expanded-button-wrapper {
        display: flex;
        flex-direction: row;
    }
    .compressed-button-wrapper {
        display: flex;
        flex-direction: row;
        ::v-deep .action-button__text {
            display: none;
        }
    }
}

.ie,
.edge {
    .action-item__menu,
    .action-item__menu .action-item__menu_arrow {
        border: 1px solid var(--color-border);
    }
}
</style>
