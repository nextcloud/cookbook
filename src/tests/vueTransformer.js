// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

import { compileScript, compileTemplate, parse } from 'vue/compiler-sfc';
import ts from 'typescript';

const compile = (descriptor, id) => {
    if (descriptor.scriptSetup) {
        return compileScript(descriptor, { id, inlineTemplate: true }).content;
    }
    const script = descriptor.script
        ? compileScript(descriptor, { id, genDefaultAs: '__sfc__' }).content
        : 'const __sfc__ = {};';
    if (!descriptor.template) {
        return `${script}\nexport default __sfc__;`;
    }
    const { code: render } = compileTemplate({
        id,
        filename: descriptor.filename,
        source: descriptor.template.content,
    });
    return `${script}\n${render}\n__sfc__.render = render;\nexport default __sfc__;`;
};

export default {
    process(source, filename) {
        const { descriptor } = parse(source, { filename });
        const { outputText } = ts.transpileModule(
            compile(descriptor, filename),
            {
                fileName: filename,
                compilerOptions: {
                    module: ts.ModuleKind.ESNext,
                    target: ts.ScriptTarget.ES2022,
                },
            },
        );
        return { code: outputText };
    },
};
