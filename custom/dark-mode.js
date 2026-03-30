$(document).ready(function() {
    const toggleBtn = $('#darkModeToggle');
    const icon = toggleBtn.find('i');
    const body = $('body');

    // 1. Check LocalStorage on Load
    // Default is light, so only if 'enabled' do we switch
    if (localStorage.getItem('darkMode') === 'enabled') {
        enableDarkMode();
    }

    // 2. Toggle Click Handler
    toggleBtn.click(function(e) {
        e.preventDefault();
        if (body.hasClass('dark-mode')) {
            disableDarkMode();
        } else {
            enableDarkMode();
        }
    });

    function enableDarkMode() {
        body.addClass('dark-mode');
        icon.removeClass('fa-moon-o').addClass('fa-sun-o');
        toggleBtn.removeClass('btn-outline-light').addClass('btn-outline-warning');
        localStorage.setItem('darkMode', 'enabled');
    }

    function disableDarkMode() {
        body.removeClass('dark-mode');
        icon.removeClass('fa-sun-o').addClass('fa-moon-o');
        toggleBtn.removeClass('btn-outline-warning').addClass('btn-outline-light');
        localStorage.setItem('darkMode', 'disabled');
    }
});
