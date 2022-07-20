<!--
 - @copyright Copyright (c) 2020 Raimund Schlüßler <raimund.schluessler@mailbox.org>
 -
 - @author Raimund Schlüßler <raimund.schluessler@mailbox.org>
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
 - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 - GNU Affero General Public License for more details.
 -
 - You should have received a copy of the GNU Affero General Public License
 - along with this program. If not, see <http://www.gnu.org/licenses/>.
 -
 -->

<docs>

### General description

This component renders a breadcrumb bar. It adjusts to the available width
by hiding breadcrumbs in a dropdown menu and emits an event when something
is dropped on a creadcrumb.

### Usage

```vue
<template>
	<div>
		<div class="container">
			<Breadcrumbs @dropped="dropped">
				<Breadcrumb title="Home" href="/" @dropped="droppedOnCrumb">
					<template #icon>
						<Folder :size="20" decorative />
					</template>
				</Breadcrumb>
				<Breadcrumb title="Folder 1" href="/Folder 1" />
				<Breadcrumb title="Folder 2" href="/Folder 1/Folder 2" :disable-drop="true" />
				<Breadcrumb title="Folder 3" href="/Folder 1/Folder 2/Folder 3" />
				<Breadcrumb title="Folder 4" href="/Folder 1/Folder 2/Folder 3/Folder 4" />
				<Breadcrumb title="Folder 5" href="/Folder 1/Folder 2/Folder 3/Folder 4/Folder 5" :disable-drop="true">
					<template #menu-icon>
						<MenuDown :size="20" />
					</template>
					<ActionButton @click="alert('Share')">
						<template #icon>
							<ShareVariant :size="20" />
						</template>
						Share
					</ActionButton>
					<ActionButton @click="alert('Download')">
						<template #icon>
							<Download :size="20" />
						</template>
						Download
					</ActionButton>
				</Breadcrumb>
				<template #actions>
					<Button>
						<template #icon>
							<Plus :size="20" />
						</template>
						New
					</Button>
				</template>
			</Breadcrumbs>
		</div>
		<br />
		<div class="dragme" draggable="true" @dragstart="dragStart">
			<span>Drag me onto the breadcrumbs.</span>
		</div>
	</div>
</template>

<script>
import Download from 'vue-material-design-icons/Download'
import Folder from 'vue-material-design-icons/Folder'
import MenuDown from 'vue-material-design-icons/MenuDown'
import Plus from 'vue-material-design-icons/Plus'
import ShareVariant from 'vue-material-design-icons/ShareVariant'

export default {
	components: {
		Download,
		Folder,
		MenuDown,
		Plus,
		ShareVariant,
	},
	methods: {
		dropped(e, path) {
			alert('Global drop on ' + path)
		},
		droppedOnCrumb(e, path) {
			alert('Drop on crumb ' + path)
		},
		dragStart(e) {
			e.dataTransfer.setData('text/plain', 'dragging')
		},
	}
}
</script>
<style>
.container {
	display: inline-flex;
	width: 100%;
}

.dragme {
	display: block;
	width: 100px;
	height: 44px;
	background-color: var(--color-background-dark);
}
</style>
```
</docs>

<script>
import Vue from "vue"
import debounce from "debounce"
import Actions from "@nextcloud/vue/dist/Components/Actions"
import ActionRouter from "@nextcloud/vue/dist/Components/ActionRouter"
import ActionLink from "@nextcloud/vue/dist/Components/ActionLink"
import ValidateSlot from "./validateSlot"
import Breadcrumb from "@nextcloud/vue/dist/Components/Breadcrumb"
import { subscribe, unsubscribe } from "@nextcloud/event-bus"

import IconFolder from "vue-material-design-icons/Folder"

const crumbClass = "vue-crumb"

export default {
    name: "Breadcrumbs",
    components: {
        Actions,
        ActionRouter,
        ActionLink,
        Breadcrumb,
        IconFolder,
    },
    props: {
        /**
         * Set a css icon-class for the icon of the root breadcrumb to be used.
         */
        rootIcon: {
            type: String,
            default: "icon-home",
        },
    },
    data() {
        return {
            /**
             * The breadcrumbs which should be shown in a dropdown Actions menu.
             */
            hiddenCrumbs: [],
            /**
             * Array to track the hidden breadcrumbs by their index.
             * Comparing two crumbs somehow does not work, so we use the indices.
             */
            hiddenIndices: [],

            /**
             * This is the props of the middle Action menu
             * that show the ellipsised breadcrumbs
             */
            menuBreadcrumbProps: {
                // Don't show a title for this breadcrumb, only the Actions menu
                title: "",
                forceMenu: true,
                // Don't allow dropping directly on the actions breadcrumb
                disableDrop: true,
                // Is the menu open or not
                open: false,
            },
        }
    },
    beforeMount() {
        // Filter all invalid items, only Breadcrumb components are allowed
        ValidateSlot(this.$slots.default, ["Breadcrumb"], this)
    },
    beforeUpdate() {
        // Also check before every update
        ValidateSlot(this.$slots.default, ["Breadcrumb"], this)
    },
    created() {
        /**
         * Add a listener so the component reacts on resize
         */
        window.addEventListener(
            "resize",
            debounce(() => {
                this.handleWindowResize()
            }, 100)
        )
        subscribe("navigation-toggled", this.delayedResize)
    },
    mounted() {
        this.handleWindowResize()
    },
    updated() {
        /**
         * Check the size on update
         */
        this.delayedResize()
        /**
         * Check that crumbs to hide are hidden
         */
        this.delayedHideCrumbs()
    },
    beforeDestroy() {
        window.removeEventListener("resize", this.handleWindowResize)
        unsubscribe("navigation-toggled", this.delayedResize)
    },
    methods: {
        /**
         * Check that all crumbs to hide are really hidden
         */
        delayedHideCrumbs() {
            this.$nextTick(() => {
                const crumbs = this.$slots.default || []
                this.hideCrumbs(crumbs)
            })
        },
        /**
         * Close the actions menu
         *
         * @param {Object} e The event
         */
        closeActions(e) {
            // Don't do anything if we leave towards a child element.
            if (this.$refs.actionsBreadcrumb.$el.contains(e.relatedTarget)) {
                return
            }
            this.menuBreadcrumbProps.open = false
        },
        /**
         * Call the resize function after a delay
         */
        delayedResize() {
            this.$nextTick(() => {
                this.handleWindowResize()
            })
        },
        /**
         * Check the width of the breadcrumb and hide breadcrumbs
         * if we overflow otherwise.
         */
        handleWindowResize() {
            console.log("resize")
            // All breadcrumb components passed into the default slot
            const breadcrumbs = this.$slots.default || []
            // If there is no container yet, we cannot determine its size
            if (this.$refs.container) {
                const nrCrumbs = breadcrumbs.length
                const hiddenIndices = []
                const availableWidth = this.$refs.container.offsetWidth
                let totalWidth = this.getTotalWidth(breadcrumbs)
                // If we have breadcumbs actions, we have to take their width into account too.
                if (this.$refs.breadcrumb__actions) {
                    totalWidth += this.$refs.breadcrumb__actions.offsetWidth
                }
                let overflow = totalWidth - availableWidth
                // If we overflow, we have to take the action-item width into account as well.
                overflow += overflow > 0 ? 64 : 0
                let i = 0
                // We start hiding the breadcrumb in the center
                const startIndex = Math.floor(nrCrumbs / 2)
                // Don't hide the first and last breadcrumb
                while (overflow > 0 && i < nrCrumbs - 2) {
                    // We hide elements alternating to the left and right
                    const currentIndex =
                        startIndex +
                        ((i % 2 ? i + 1 : i) / 2) *
                            Math.pow(-1, i + (nrCrumbs % 2))
                    // Calculate the remaining overflow width after hiding this breadcrumb
                    overflow -= this.getWidth(breadcrumbs[currentIndex].elm)
                    hiddenIndices.push(currentIndex)
                    i++
                }
                // We only update the hidden crumbs if they have changed,
                // otherwise we will run into an infinite update loop.
                if (
                    !this.arraysEqual(
                        this.hiddenIndices,
                        hiddenIndices.sort((a, b) => a - b)
                    )
                ) {
                    // Get all breadcrumbs based on the hidden indices
                    this.hiddenCrumbs = hiddenIndices.map((index) => {
                        return breadcrumbs[index]
                    })
                    this.hiddenIndices = hiddenIndices
                }
            }
        },
        /**
         * Checks if two arrays are equal.
         * Only works for primitive arrays, but that's enough here.
         *
         * @param {Array} a The first array
         * @param {Array} b The second array
         * @returns {boolean} Wether the arrays are equal
         */
        arraysEqual(a, b) {
            if (a.length !== b.length) return false
            if (a === b) return true
            if (a === null || b === null) return false

            for (let i = 0; i < a.length; ++i) {
                if (a[i] !== b[i]) {
                    return false
                }
            }
            return true
        },
        /**
         * Calculates the total width of all breadcrumbs
         *
         * @param {Array} breadcrumbs All breadcrumbs
         * @returns {Integer} The total width
         */
        getTotalWidth(breadcrumbs) {
            return breadcrumbs.reduce(
                (width, crumb, index) => width + this.getWidth(crumb.elm),
                0
            )
        },
        /**
         * Calculates the width of the provided element
         *
         * @param {Object} el The element
         * @returns {Integer} The width
         */
        getWidth(el) {
            if (!el.classList) return 0
            const hide = el.classList.contains(`${crumbClass}--hidden`)
            el.style.minWidth = "auto"
            el.classList.remove(`${crumbClass}--hidden`)
            const w = el.offsetWidth
            if (hide) {
                el.classList.add(`${crumbClass}--hidden`)
            }
            el.style.minWidth = ""
            return w
        },
        /**
         * Prevents the default of a provided event
         *
         * @param {Object} e The event
         * @returns {boolean}
         */
        preventDefault(e) {
            if (e.preventDefault) {
                e.preventDefault()
            }
            return false
        },
        /**
         * Handles the drag start.
         * Prevents a breadcrumb from being draggable.
         *
         * @param {Object} e The event
         * @returns {boolean}
         */
        dragStart(e) {
            return this.preventDefault(e)
        },
        /**
         * Handles when something is dropped on the breadcrumb.
         *
         * @param {Object} e The drop event
         * @param {String} path The path of the breadcrumb
         * @param {boolean} disabled Whether dropping is disabled for this breadcrumb
         * @returns {boolean}
         */
        dropped(e, path, disabled) {
            /**
             * Don't emit if dropping is disabled.
             */
            if (!disabled) {
                /**
                 * Event emitted when something is dropped on the breadcrumb.
                 * @type {null}
                 */
                this.$emit("dropped", e, path)
            }
            // Close the actions menu after the drop
            this.menuBreadcrumbProps.open = false

            // Remove all hovering classes
            const crumbs = document.querySelectorAll(`.${crumbClass}`)
            crumbs.forEach((f) => {
                f.classList.remove(`${crumbClass}--hovered`)
            })
            return this.preventDefault(e)
        },
        /**
         * Handles the drag over event
         *
         * @param {Object} e The drag over event
         * @returns {boolean}
         */
        dragOver(e) {
            return this.preventDefault(e)
        },
        /**
         * Handles the drag enter event
         *
         * @param {Object} e The drag over event
         * @param {boolean} disabled Whether dropping is disabled for this breadcrumb
         */
        dragEnter(e, disabled) {
            /**
             * Don't do anything if dropping is disabled.
             */
            if (disabled) {
                return
            }
            // Get the correct element, in case we hover a child.
            if (e.target.closest) {
                const target = e.target.closest(`.${crumbClass}`)
                if (target.classList && target.classList.contains(crumbClass)) {
                    const crumbs = document.querySelectorAll(`.${crumbClass}`)
                    crumbs.forEach((f) => {
                        f.classList.remove(`${crumbClass}--hovered`)
                    })
                    target.classList.add(`${crumbClass}--hovered`)
                }
            }
        },
        /**
         * Handles the drag leave event
         *
         * @param {Object} e The drag leave event
         * @param {boolean} disabled Whether dropping is disabled for this breadcrumb
         */
        dragLeave(e, disabled) {
            /**
             * Don't do anything if dropping is disabled.
             */
            if (disabled) {
                return
            }
            // Don't do anything if we leave towards a child element.
            if (e.target.contains(e.relatedTarget)) {
                return
            }
            // Get the correct element, in case we leave directly from a child.
            if (e.target.closest) {
                const target = e.target.closest(`.${crumbClass}`)
                if (target.contains(e.relatedTarget)) {
                    return
                }
                if (target.classList && target.classList.contains(crumbClass)) {
                    target.classList.remove(`${crumbClass}--hovered`)
                }
            }
        },
        /**
         * Check for each crumb if we have to hide it and
         * add it to the array of all crumbs.
         *
         * @param {Array} crumbs The array of the crumbs to hide
         * @param {Integer} offset The offset of the indices of the provided crumbs array
         */
        hideCrumbs(crumbs, offset = 0) {
            crumbs.forEach((crumb, i) => {
                if (crumb?.elm?.classList) {
                    if (this.hiddenIndices.includes(i + offset)) {
                        crumb.elm.classList.add(`${crumbClass}--hidden`)
                    } else {
                        crumb.elm.classList.remove(`${crumbClass}--hidden`)
                    }
                }
            })
        },
    },
    /**
     * The render function to display the component
     *
     * @param {Function} createElement The function to create VNodes
     * @returns {VNodes} The created VNodes
     */
    render(createElement) {
        // Get the breadcrumbs
        const breadcrumbs = this.$slots.default || []

        // Check that we have at least one breadcrumb
        if (breadcrumbs.length === 0) {
            return
        }

        // Add the root icon to the first breadcrumb
        Vue.set(
            breadcrumbs[0].componentOptions.propsData,
            "icon",
            this.rootIcon
        )

        // The array of all created VNodes
        let crumbs = []
        /**
         * We show the first half of the breadcrumbs before the Actions dropdown menu
         * which shows the hidden breadcrumbs.
         */
        const crumbs1 = this.hiddenCrumbs.length
            ? breadcrumbs.slice(0, Math.round(breadcrumbs.length / 2))
            : breadcrumbs
        // Add the breadcrumbs to the array of the created VNodes, check if hiding them is necessary.
        crumbs = crumbs.concat(crumbs1)
        this.hideCrumbs(crumbs1)

        // The Actions menu
        if (this.hiddenCrumbs.length) {
            // Use a breadcrumb component for the hidden breadcrumbs
            crumbs.push(
                createElement(
                    "Breadcrumb",
                    {
                        class: "dropdown",

                        props: this.menuBreadcrumbProps,

                        // Add a ref to the Actions menu
                        ref: "actionsBreadcrumb",
                        key: "actions-breadcrumb-1",
                        // Add handlers so the Actions menu opens on hover
                        nativeOn: {
                            dragstart: this.dragStart,
                            dragenter: () => {
                                this.menuBreadcrumbProps.open = true
                            },
                            dragleave: this.closeActions,
                        },
                        on: {
                            // Make sure we keep the same open state
                            // as the Actions component
                            "update:open": (open) => {
                                this.menuBreadcrumbProps.open = open
                            },
                        },
                        // Add all hidden breadcrumbs as ActionRouter or ActionLink
                    },
                    this.hiddenCrumbs.map((crumb) => {
                        // Get the parameters from the breadcrumb component props
                        const to = crumb.componentOptions.propsData.to
                        const href = crumb.componentOptions.propsData.href
                        const disabled =
                            crumb.componentOptions.propsData.disableDrop
                        // Decide whether to show the breadcrumbs as ActionRouter or ActionLink
                        let element = "ActionLink"
                        let path = href
                        if (to) {
                            element = "ActionRouter"
                            path = to
                        }
                        const folderIcon = createElement("IconFolder", {
                            props: {
                                size: 20,
                            },
                            slot: "icon",
                        })
                        return createElement(
                            element,
                            {
                                class: crumbClass,
                                props: {
                                    to,
                                    href,
                                },
                                // Prevent the breadcrumbs from being draggable
                                attrs: {
                                    draggable: false,
                                },
                                // Add the drag and drop handlers
                                nativeOn: {
                                    dragstart: this.dragStart,
                                    drop: ($event) =>
                                        this.dropped($event, path, disabled),
                                    dragover: this.dragOver,
                                    dragenter: ($event) =>
                                        this.dragEnter($event, disabled),
                                    dragleave: ($event) =>
                                        this.dragLeave($event, disabled),
                                },
                            },
                            [crumb.componentOptions.propsData.title, folderIcon]
                        )
                    })
                )
            )
        }
        // The second half of the breadcrumbs
        const crumbs2 = this.hiddenCrumbs.length
            ? breadcrumbs.slice(Math.round(breadcrumbs.length / 2))
            : []
        crumbs = crumbs.concat(crumbs2)
        this.hideCrumbs(crumbs2, crumbs1.length)

        const wrapper = []
        wrapper.push(
            createElement("div", { class: "breadcrumb__crumbs" }, crumbs)
        )
        // Append the actions slot if it is populated
        if (this.$slots.actions) {
            wrapper.push(
                createElement(
                    "div",
                    {
                        class: "breadcrumb__actions",
                        ref: "breadcrumb__actions",
                    },
                    this.$slots.actions
                )
            )
        }

        return createElement(
            "div",
            {
                class: [
                    "breadcrumb",
                    {
                        "breadcrumb--collapsed":
                            this.hiddenCrumbs.length === breadcrumbs.length - 2,
                    },
                ],
                ref: "container",
            },
            wrapper
        )
    },
}
</script>

<style lang="scss" scoped>
.breadcrumb {
    display: inline-flex;
    width: 100%;
    flex-grow: 1;

    &--collapsed .vue-crumb:last-child {
        min-width: 100px;
        flex-shrink: 1;
    }

    & #{&}__crumbs {
        /**
         * This value is given by the min-width of the last crumb (100px) plus
         * two times the width of a crumb with an icon (first crumb and hidden crumbs actions).
         */
        min-width: 228px;

        max-width: 100%;
        flex-shrink: 1;
    }

    & #{&}__crumbs,
    & #{&}__actions {
        display: inline-flex;
    }
}
</style>
