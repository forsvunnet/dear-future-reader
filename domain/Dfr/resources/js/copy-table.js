// Copies a table to the clipboard as Markdown, CSV or TSV.
// CopyableTableRenderer puts each text in a data attribute on the element with x-data="copyTable".
document.addEventListener('alpine:init', () => {
    window.Alpine.data('copyTable', () => ({
        message: '',

        async copy(format) {
            // The browser has navigator.clipboard only on localhost and HTTPS.
            try {
                await navigator.clipboard.writeText(this.$root.dataset[format]);
                this.message = `Copied as ${format.toUpperCase()}.`;
            } catch {
                this.message = 'The browser did not allow the copy.';
            }
        },
    }));
});
