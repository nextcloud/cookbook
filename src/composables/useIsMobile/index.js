// source: https://github.com/nextcloud-libraries/nextcloud-vue/blob/d8e63c7db317db5024d5d4d3b05105d28bb21ed5/src/composables/useIsMobile/index.js
// part of @nextcloud/vue > v8.x package
/**
 * @copyright Copyright (c) 2023 Grigorii K. Shartsev <me@shgk.me>
 *
 * @author Grigorii K. Shartsev <me@shgk.me>
 *
 * @license AGPL-3.0-or-later
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

import { readonly, ref } from 'vue'

/**
 * The minimal width of the viewport to be considered a desktop device
 */
export const MOBILE_BREAKPOINT = 1024

const checkIfIsMobile = () => document.documentElement.clientWidth < MOBILE_BREAKPOINT

const isMobile = ref(checkIfIsMobile())

window.addEventListener('resize', () => {
    isMobile.value = checkIfIsMobile()
})

/**
 * Use global isMobile state, based on the viewport width
 *
 * @return {import('vue').DeepReadonly<import('vue').Ref<boolean>>}
 */
export function useIsMobile() {
    return readonly(isMobile)
}

/**
 * @deprecated Is to be removed in v9.0.0 with Vue 3 migration.
 *             Use `composables/useIsMobile` instead.
 *             Defined and exported only for isMobile mixin.
 */
export const isMobileState = readonly(isMobile)
