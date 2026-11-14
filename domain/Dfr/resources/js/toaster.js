// Backs the toaster region in the layout. A toast arrives as a `toast` event with { message, description }.
// The design is the same as the toaster in Outnordic.

// Long enough for the exit animation in dfr.css to finish.
const LEAVE_MS = 200;
const DURATION_MS = 3000;

let nextId = 0;

// Alpine has $dispatch only inside an x-data scope, so a plain function lets any element show a toast.
window.toast = (detail) => window.dispatchEvent(new CustomEvent('toast', { detail }));

document.addEventListener('alpine:init', () => {
    window.Alpine.data('toaster', () => ({
        toasts: [],

        // On window, so a caller outside this component reaches it too.
        init() {
            window.addEventListener('toast', (event) => this.add(event.detail));
        },

        add({ message, description }) {
            const id = ++nextId;
            this.toasts.push({ id, message, description, leaving: false });
            setTimeout(() => this.dismiss(id), DURATION_MS);
        },

        // x-for runs no leave transition, so CSS animates the flagged toast before it is dropped.
        dismiss(id) {
            const toast = this.toasts.find((t) => t.id === id);
            if (! toast || toast.leaving) {
                return;
            }
            toast.leaving = true;
            setTimeout(() => {
                this.toasts = this.toasts.filter((t) => t.id !== id);
            }, LEAVE_MS);
        },
    }));
});
