// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

import { compileScript, parse } from 'vue/compiler-sfc';
import ts from 'typescript';

export default {
    process(source, filename) {
        const { descriptor } = parse(source, { filename });
        const { content } = compileScript(descriptor, {
            id: filename,
            inlineTemplate: true,
        });
        const { outputText } = ts.transpileModule(content, {
            fileName: filename,
            compilerOptions: {
                module: ts.ModuleKind.ESNext,
                target: ts.ScriptTarget.ES2022,
            },
        });
        return { code: outputText };
    },
};
