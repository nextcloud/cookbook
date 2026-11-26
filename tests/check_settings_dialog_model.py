# SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
#
# SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

"""Check that SettingsDialog binds NcAppSettingsDialog's open state with Vue 3 syntax."""

import sys
from pathlib import Path

path = Path(__file__).resolve().parent.parent / "src" / "components" / "Modals" / "SettingsDialog.vue"
text = path.read_text(encoding="utf-8")

if ":open.sync" in text:
    print("FAIL: SettingsDialog.vue uses ':open.sync', which Vue 3 ignores; expected 'v-model:open'")
    sys.exit(1)

if 'v-model:open="isOpen"' not in text:
    print("FAIL: SettingsDialog.vue does not bind 'v-model:open=\"isOpen\"'")
    sys.exit(1)

dialog = Path(__file__).resolve().parent.parent / "node_modules" / "@nextcloud" / "vue" / "dist" / "chunks" / "NcAppSettingsDialog.mjs"
if 'emits: ["update:open"]' not in dialog.read_text(encoding="utf-8"):
    print("FAIL: NcAppSettingsDialog does not declare the 'update:open' event that v-model:open needs")
    sys.exit(1)

print("OK: SettingsDialog.vue binds the open state with v-model:open")
