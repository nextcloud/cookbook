<!--
  - @copyright Copyright (c) 2019 John Molakvoæ <skjnldsv@protonmail.com>
  -
  - @author John Molakvoæ <skjnldsv@protonmail.com>
  - @author Marco Ambrosini <marcoambrosini@icloud.com>
  - @author Marcel Robitaille <mail@marcelrobitaille.me>
  -
  - @license GNU AGPL version 3 or any later version
  -
  - This program is free software: you can redistribute it and/or modify
  - it under the terms of the GNU Affero General Public License as
  - published by the Free Software Foundation, either version 3 of the
  - License, or (at your option) any later version.
  -
  - This program is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  - GNU Affero General Public License for more details.
  -
  - You should have received a copy of the GNU Affero General Public License
  - along with this program. If not, see <http://www.gnu.org/licenses/>.
  -
  - This file is modified from:
  - https://github.com/nextcloud/nextcloud-vue/blob/master/src/components/NcActionInput/NcActionInput.vue
  - Some features are stripped to avoid importing large dependencies to reduce
  - bundle size.
  - Please see: https://github.com/nextcloud/cookbook/pull/1116
  -->

<template>
    <li class="action">
        <span class="action-input">
            <slot name="icon">
                <span :class="icon" class="action-input__icon" />
            </slot>
            <form
                ref="form"
                class="action-input__form"
                :disabled="disabled"
                @submit.prevent="onSubmit"
            >
                <input :id="id" type="submit" class="action-input__submit" />

                <input
                    :type="type"
                    :value="value"
                    :placeholder="text"
                    :disabled="disabled"
                    :aria-label="ariaLabel"
                    v-bind="$attrs"
                    :class="{ focusable: isFocusable }"
                    class="action-input__input"
                    @input="onInput"
                    @change="onChange"
                />
                <label v-show="!disabled" :for="id" class="action-input__label">
                    <ArrowRight :size="20" />
                </label>
            </form>
        </span>
    </li>
</template>

<script>
import Vue from "vue"
import ArrowRight from "icons/ArrowRight.vue"

const ActionGlobalMixin = {
    before() {
        // all actions requires a valid text content
        // if none, forbid the component mount and throw error
        if (!this.$slots.default || this.text.trim() === "") {
            Vue.util.warn(
                `${this.$options.name} cannot be empty and requires a meaningful text content`,
                this
            )
            this.$destroy()
            this.$el.remove()
        }
    },

    beforeUpdate() {
        this.text = this.getText()
    },

    data() {
        return {
            // $slots are not reactive.
            // We need to update  the content manually
            text: this.getText(),
        }
    },

    computed: {
        isLongText() {
            return this.text && this.text.trim().length > 20
        },
    },

    methods: {
        getText() {
            return this.$slots.default ? this.$slots.default[0].text.trim() : ""
        },
    },
}

const GenRandomId = (length) =>
    Math.random()
        .toString(36)
        .replace(/[^a-z]+/g, "")
        .slice(0, length || 5)

export default {
    name: "ActionInput",

    components: {
        ArrowRight,
    },

    mixins: [ActionGlobalMixin],

    props: {
        /**
         * id attribute of the checkbox element
         */
        id: {
            type: String,
            default: () => `action-${GenRandomId()}`,
            validator: (id) => id.trim() !== "",
        },
        /**
         * Icon to show with the action, can be either a CSS class or an URL
         */
        icon: {
            type: String,
            default: "",
        },
        /**
         * type attribute of the input field
         */
        type: {
            type: String,
            default: "text",
            validator(type) {
                return ["text"].indexOf(type) > -1
            },
        },
        /**
         * value attribute of the input field
         */
        value: {
            type: [String, Date, Number],
            default: "",
        },
        /**
         * disabled state of the input field
         */
        disabled: {
            type: Boolean,
            default: false,
        },
        /**
         * aria-label attribute of the input field
         */
        ariaLabel: {
            type: String,
            default: "",
        },
    },

    computed: {
        /**
         * determines if the action is focusable
         *
         * @return {boolean} is the action focusable ?
         */
        isFocusable() {
            return !this.disabled
        },
    },

    methods: {
        onInput(event) {
            /**
             * Emitted on input events of the text field
             *
             * @type {Event|Date}
             */
            this.$emit("input", event)
            /**
             * Emitted when the inputs value changes
             * ! DatetimePicker only send the value
             *
             * @type {string|Date}
             */
            this.$emit(
                "update:value",
                event.target ? event.target.value : event
            )
        },
        onSubmit(event) {
            event.preventDefault()
            event.stopPropagation()

            if (!this.disabled) {
                /**
                 * Emitted on submit of the input field
                 *
                 * @type {Event}
                 */
                this.$emit("submit", event)
                return true
            }

            // ignore submit
            return false
        },
        onChange(event) {
            /**
             * Emitted on change of the input field
             *
             * @type {Event}
             */
            this.$emit("change", event)
        },
    },
}
</script>

<style lang="scss" scoped>
@use "sass:math";

// https://uxplanet.org/7-rules-for-mobile-ui-button-design-e9cf2ea54556
// recommended is 48px
// 44px is what we choose and have very good visual-to-usability ratio
$clickable-area: 44px;

// background icon size
// also used for the scss icon font
$icon-size: 16px;

// icon padding for a $clickable-area width and a $icon-size icon
// ( 44px - 16px ) / 2
$icon-margin: math.div($clickable-area - $icon-size, 2);

// transparency background for icons
$icon-focus-bg: rgba(127, 127, 127, 0.25);

// popovermenu arrow width from the triangle center
$arrow-width: 9px;

// opacities
$opacity_disabled: 0.5;
$opacity_normal: 0.7;
$opacity_full: 1;

// menu round background hover feedback
// good looking on dark AND white bg
$action-background-hover: rgba(127, 127, 127, 0.25);

// various structure data used in the
// `AppNavigation` component
$header-height: 50px;
$navigation-width: 300px;

// mobile breakpoint
$breakpoint-mobile: 1024px;

// top-bar spacing
$topbar-margin: 4px;
button:not(.button-vue),
input:not([type="range"]),
textarea {
    margin: 0;
    padding: 7px 6px;

    cursor: text;

    color: var(--color-text-lighter);
    border: 1px solid var(--color-border-dark);
    border-radius: var(--border-radius);
    outline: none;
    background-color: var(--color-main-background);

    font-size: 13px;

    &:not(:disabled):not(.primary) {
        &:hover,
        &:focus,
        &.active {
            /* active class used for multiselect */
            border-color: var(--color-primary-element);
            outline: none;
        }

        &:active {
            color: var(--color-text-light);
            outline: none;
            background-color: var(--color-main-background);
        }
    }

    &:disabled {
        cursor: default;
        opacity: $opacity_disabled;
        color: var(--color-text-maxcontrast);
        background-color: var(--color-background-dark);
    }

    &:required {
        box-shadow: none;
    }

    &:invalid {
        border-color: var(--color-error);
        box-shadow: none !important;
    }

    /* Primary action button, use sparingly */
    &.primary {
        cursor: pointer;
        color: var(--color-primary-text);
        border-color: var(--color-primary-element);
        background-color: var(--color-primary-element);

        &:not(:disabled) {
            &:hover,
            &:focus,
            &:active {
                border-color: var(--color-primary-element-light);
                background-color: var(--color-primary-element-light);
            }
            &:active {
                color: var(--color-primary-text-dark);
            }
        }

        &:disabled {
            cursor: default;
            color: var(--color-primary-text-dark);
            // opacity is already defined to .5 if disabled
            background-color: var(--color-primary-element);
        }
    }
}
@mixin action-active {
    li {
        &.active {
            background-color: var(--color-background-hover);
            border-radius: 6px;
            padding: 0;
        }
    }
}

@mixin action--disabled {
    .action--disabled {
        pointer-events: none;
        opacity: $opacity_disabled;
        &:hover,
        &:focus {
            cursor: default;
            opacity: $opacity_disabled;
        }
        & * {
            opacity: 1 !important;
        }
    }
}

@mixin action-item($name) {
    .action-#{$name} {
        display: flex;
        align-items: flex-start;

        width: 100%;
        height: auto;
        margin: 0;
        padding: 0;
        padding-right: $icon-margin;
        box-sizing: border-box; // otherwise router-link overflows in Firefox

        cursor: pointer;
        white-space: nowrap;

        opacity: $opacity_normal;
        color: var(--color-main-text);
        border: 0;
        border-radius: 0; // otherwise Safari will cut the border-radius area
        background-color: transparent;
        box-shadow: none;

        font-weight: normal;
        font-size: var(--default-font-size);
        line-height: $clickable-area;

        &:hover,
        &:focus {
            opacity: $opacity_full;
        }

        & > span {
            cursor: pointer;
            white-space: nowrap;
        }

        &__icon {
            width: $clickable-area;
            height: $clickable-area;
            opacity: $opacity_full;
            background-position: $icon-margin center;
            background-size: $icon-size;
            background-repeat: no-repeat;
        }

        &::v-deep .material-design-icon {
            width: $clickable-area;
            height: $clickable-area;
            opacity: $opacity_full;

            .material-design-icon__svg {
                vertical-align: middle;
            }
        }

        // long text area
        p {
            max-width: 220px;
            line-height: 1.6em;

            // 14px are currently 1em line-height. Mixing units as '44px - 1.6em' does not work.
            padding: #{math.div($clickable-area - 1.6 * 14px, 2)} 0;

            cursor: pointer;
            text-align: left;

            // in case there are no spaces like long email addresses
            overflow: hidden;
            text-overflow: ellipsis;
        }

        &__longtext {
            cursor: pointer;
            // allow the use of `\n`
            white-space: pre-wrap;
        }

        &__title {
            font-weight: bold;
            text-overflow: ellipsis;
            overflow: hidden;
            white-space: nowrap;
            max-width: 100%;
            display: inline-block;
        }
    }
}
@include action-active;
@include action--disabled;

$input-margin: 4px;

.action-input {
    display: flex;
    align-items: flex-start;

    width: 100%;
    height: auto;
    margin: 0;
    padding: 0;

    cursor: pointer;
    white-space: nowrap;

    color: var(--color-main-text);
    border: 0;
    border-radius: 0; // otherwise Safari will cut the border-radius area
    background-color: transparent;
    box-shadow: none;

    font-weight: normal;

    &::v-deep .material-design-icon {
        width: $clickable-area;
        height: $clickable-area;
        opacity: $opacity_full;

        .material-design-icon__svg {
            vertical-align: middle;
        }
    }

    // do not change the opacity of the datepicker
    &:not(.action-input--picker) {
        opacity: $opacity_normal;
        &:hover,
        &:focus {
            opacity: $opacity_full;
        }
    }

    // only change for the icon then
    &--picker {
        .action-input__icon {
            opacity: $opacity_normal;
        }
        &:hover .action-input__icon,
        &:focus .action-input__icon {
            opacity: $opacity_full;
        }
    }

    & > span {
        cursor: pointer;
        white-space: nowrap;
    }

    &__icon {
        min-width: 0; /* Overwrite icons*/
        min-height: 0;
        /* Keep padding to define the width to
			assure correct position of a possible text */
        padding: #{math.div($clickable-area, 2)} 0 #{math.div(
                $clickable-area,
                2
            )} $clickable-area;

        background-position: #{$icon-margin} center;
        background-size: $icon-size;
    }

    // Forms & text inputs
    &__form {
        display: flex;
        align-items: center;
        flex: 1 1 auto;

        margin: $input-margin 0;
        padding-right: $icon-margin;
    }

    &__submit {
        position: absolute;
        left: -10000px;
        top: auto;
        width: 1px;
        height: 1px;
        overflow: hidden;
    }

    &__label {
        display: flex;
        align-items: center;
        justify-content: center;

        width: #{$clickable-area - $input-margin * 2};
        height: #{$clickable-area - $input-margin * 2};
        box-sizing: border-box;
        margin: 0 0 0 -8px;
        padding: 7px 6px;

        opacity: $opacity_full;
        color: var(--color-text-lighter);
        border: 1px solid var(--color-border-dark);
        border-left-color: transparent;
        border-radius: 0 var(--border-radius) var(--border-radius) 0;
        /* Avoid background under border */
        background-color: var(--color-main-background);
        background-clip: padding-box;

        &,
        * {
            cursor: pointer;
        }
    }

    /* Inputs inside popover supports text, submit & reset */
    &__input {
        flex: 1 1 auto;

        min-width: $clickable-area * 3;
        min-height: #{$clickable-area - $input-margin * 2}; /* twice the element margin-y */
        max-height: #{$clickable-area - $input-margin * 2}; /* twice the element margin-y */
        margin: 0;

        // if disabled, change cursor
        &:disabled {
            cursor: default;
        }

        /* only show confirm borders if input is not focused */
        &:not(:active):not(:hover):not(:focus) {
            &:invalid {
                & + .action-input__label {
                    border-color: var(--color-error);
                    border-left-color: transparent;
                }
            }
            &:not(:disabled) + .action-input__label {
                &:active,
                &:hover,
                &:focus {
                    border-color: var(--color-primary-element);
                    border-radius: var(--border-radius);
                }
            }
        }
        &:active,
        &:hover,
        &:focus {
            &:not(:disabled) + .action-input__label {
                /* above previous input */
                z-index: 2;

                border-color: var(--color-primary-element);
                border-left-color: transparent;
            }
        }
    }

    &__picker::v-deep {
        .mx-input {
            margin: 0;
        }
    }

    &__multi {
        width: 100%;
    }
}

// if a form is the last of the list
// add the same bottomMargin as the right padding
// for visual balance
li:last-child > .action-input {
    padding-bottom: $icon-margin - $input-margin;
}

// same for first item
li:first-child > .action-input {
    padding-top: $icon-margin - $input-margin;
}
</style>
