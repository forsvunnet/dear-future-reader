// Backs the commit panel. On a wide screen the panel is a column next to the files,
// and the handle on its left edge sets its width, as a percent of the page.
// The width follows the window splitter pattern of WAI-ARIA: drag the handle, or focus it and press the arrow keys.

const WIDE = '(min-width: 48rem)';
const MIN = 25;
const MAX = 75;
const STEP = 5;
const KEY = 'dfr-panel-width';

const clamp = (width) => Math.round(Math.min(MAX, Math.max(MIN, width)));

// The storage can be blocked, for example in a private window.
const load = () => {
    try {
        const width = Number(localStorage.getItem(KEY));
        return width > 0 ? clamp(width) : 50;
    } catch {
        return 50;
    }
};

document.addEventListener('alpine:init', () => {
    window.Alpine.data('splitter', () => ({
        width: load(),
        wide: window.matchMedia(WIDE).matches,
        min: MIN,
        max: MAX,

        init() {
            window.matchMedia(WIDE).addEventListener('change', (event) => this.wide = event.matches);
        },

        set(width) {
            this.width = clamp(width);
            try {
                localStorage.setItem(KEY, String(this.width));
            } catch {
                // The width still holds until the page reloads.
            }
        },

        start(event) {
            event.target.setPointerCapture(event.pointerId);
        },

        // The panel is the last column of its row, so its width is the distance from the pointer to the right edge.
        drag(event) {
            if (! event.target.hasPointerCapture(event.pointerId)) {
                return;
            }
            const row = this.$root.parentElement.getBoundingClientRect();
            this.set((row.right - event.clientX) / row.width * 100);
        },

        key(event) {
            const width = { ArrowLeft: this.width + STEP, ArrowRight: this.width - STEP, Home: MAX, End: MIN }[event.key];
            if (width === undefined) {
                return;
            }
            event.preventDefault();
            this.set(width);
        },
    }));
});
