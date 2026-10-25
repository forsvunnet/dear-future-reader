/*
  Colors code with Shiki in the browser.

  An element with the x-highlight directive holds plain, escaped code from the server:
  - data-lang: the language of a code block in a context file.
  - data-diff: the element holds a unified diff of the file in data-file.

  The server marks each element with wire:ignore and a wire:key from its content.
  Livewire then keeps the colored HTML, and swaps the element when the code changes.
*/
import { bundledLanguages, createHighlighter } from 'shiki';
import { createJavaScriptRegexEngine } from 'shiki/engine/javascript';

const themes = { light: 'github-light', dark: 'github-dark' };

// One highlighter for the page. It loads each language the first time a block needs it.
let highlighter;

async function codeToHtml(code, lang, transformers = []) {
    highlighter ??= createHighlighter({ themes: Object.values(themes), langs: [], engine: createJavaScriptRegexEngine() });
    const shiki = await highlighter;

    if (!(lang in bundledLanguages)) {
        lang = 'text';
    }

    if (lang !== 'text') {
        await shiki.loadLanguage(lang);
    }

    // defaultColor false gives each token only the --shiki-light and --shiki-dark vars. dfr.css picks one.
    return shiki.codeToHtml(code, { lang, themes, defaultColor: false, transformers });
}

function languageOf(path) {
    if (path.endsWith('.blade.php')) {
        return 'blade';
    }

    // A file without an extension, like Dockerfile, uses its name.
    const name = path.split('/').pop().toLowerCase();

    return name.includes('.') ? name.split('.').pop() : name;
}

// Colors a diff in the language of the file. Each hunk is colored on its own, so a comment that a hunk cuts off does not color the next hunk.
async function diffToHtml(diff, path) {
    const hunks = diff.split(/\n(?=@@ )/);

    return (await Promise.all(hunks.map((hunk) => hunkToHtml(hunk, languageOf(path))))).join('');
}

// Each line gets a class for its kind.
// ponytail: added and removed lines are colored as one text, so a token that spans both can get the wrong color. GitHub has the same limit.
function hunkToHtml(hunk, lang) {
    const lines = hunk.split('\n');
    const kinds = lines.map((line) => ({ '+': 'add', '-': 'del', ' ': 'context' })[line[0]] ?? 'meta');
    const code = lines.map((line, index) => (kinds[index] === 'meta' ? '' : line.slice(1)));

    // The PHP grammar colors only the code after "<?php". The hunk header shows its own text, so it can hold the tag.
    if (lang === 'php' && kinds[0] === 'meta') {
        code[0] = '<?php';
    }

    return codeToHtml(code.join('\n'), lang, [{
        line(node, number) {
            const index = number - 1;
            this.addClassToHast(node, `dfr-diff-${kinds[index]}`);

            // A hunk header or "\ No newline at end of file" shows as it is.
            if (kinds[index] === 'meta') {
                node.children = [{ type: 'text', value: lines[index] }];
            }
        },
    }]);
}

async function highlight(el) {
    const code = el.textContent.replace(/\n$/, '');
    el.innerHTML = 'diff' in el.dataset ? await diffToHtml(code, el.dataset.file) : await codeToHtml(code, el.dataset.lang);
}

document.addEventListener('alpine:init', () => {
    window.Alpine.directive('highlight', (el) => {
        // A large diff is in a closed <details>. Color it only when it opens.
        const details = el.closest('details');

        if (details && !details.open) {
            details.addEventListener('toggle', () => highlight(el), { once: true });

            return;
        }

        highlight(el);
    });
});
