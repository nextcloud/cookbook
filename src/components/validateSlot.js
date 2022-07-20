/**
 * @copyright Copyright (c) 2018 John Molakvoæ <skjnldsv@protonmail.com>
 *
 * @author John Molakvoæ <skjnldsv@protonmail.com>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */
import Vue from "vue"

/**
 * Validate children of a vue component
 *
 * @param {Object[]} slots the vue component slot
 * @param {String[]} allowed the allowed components name
 * @param {Object} vm the vue component instance
 */
const ValidateSlot = (slots, allowed, vm) => {
    if (slots === undefined) {
        return
    }

    for (let index = slots.length - 1; index >= 0; index--) {
        const node = slots[index]
        // also check against allowed to avoid uninitiated vnodes with no componentOptions
        const isHtmlElement =
            !node.componentOptions &&
            node.tag &&
            allowed.indexOf(node.tag) === -1
        const isVueComponent =
            !!node.componentOptions &&
            typeof node.componentOptions.tag === "string"
        const isForbiddenComponent =
            isVueComponent && allowed.indexOf(node.componentOptions.tag) === -1

        // if html element or not a vue component or vue component not in allowed tags
        if (isHtmlElement || !isVueComponent || isForbiddenComponent) {
            // only warn when html elment or forbidden component
            // sometimes text nodes are present which are hardly removeable by the developer and spam the warnings
            if (isHtmlElement || isForbiddenComponent) {
                Vue.util.warn(
                    `${
                        isHtmlElement ? node.tag : node.componentOptions.tag
                    } is not allowed inside the ${vm.$options.name} component`,
                    vm
                )
            }

            // cleanup
            slots.splice(index, 1)
        }
    }
}

export default ValidateSlot
