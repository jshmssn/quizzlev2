// Disable right-click context menu
document.addEventListener('contextmenu', function (e) {
    e.preventDefault();
});

// Disable developer tools shortcuts and F5 refresh
document.addEventListener('keydown', function (e) {
    // Disable Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+U, and F12
    if ((e.ctrlKey && e.shiftKey && e.key === 'I') || // Prevent Ctrl+Shift+I
        (e.ctrlKey && e.shiftKey && e.key === 'J') || // Prevent Ctrl+Shift+J
        (e.ctrlKey && e.key === 'U') ||              // Prevent Ctrl+U
        (e.key === 'F12')) {                         // Prevent F12
        e.preventDefault();
    }
});
