// Copies a text to the clipboard and shows a toast with the text.
window.copyText = async (text) => {
    // The browser has navigator.clipboard only on localhost and HTTPS.
    try {
        await navigator.clipboard.writeText(text);
        window.toast({ message: 'Copied', description: text });
    } catch {
        window.toast({ message: 'The browser did not allow the copy.' });
    }
};
