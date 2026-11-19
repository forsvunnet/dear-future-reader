// The server writes the tooltip of a time element in UTC, because it does not know the time zone of the browser.
// When the mouse goes over a time, the tooltip changes to the local time, like 2026-11-15 15:30:00.
// It changes on each hover, because the poll of the page puts the UTC tooltip back.
document.addEventListener('mouseover', (event) => {
    const time = event.target.closest?.('time[datetime]');

    if (!time) {
        return;
    }

    // The sv-SE format is the ISO form of a date and time.
    time.title = new Date(time.dateTime).toLocaleString('sv-SE');
});
