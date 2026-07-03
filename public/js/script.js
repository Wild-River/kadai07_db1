document.querySelectorAll('.nav-toggle').forEach(function (toggle) {
    toggle.addEventListener('click', function (e) {
        e.stopPropagation();
        var group = toggle.closest('.nav-group');
        var isOpen = group.classList.contains('open');
        document.querySelectorAll('.nav-group.open').forEach(function (openGroup) {
            openGroup.classList.remove('open');
            openGroup.querySelector('.nav-toggle').setAttribute('aria-expanded', 'false');
        });
        if (!isOpen) {
            group.classList.add('open');
            toggle.setAttribute('aria-expanded', 'true');
        }
    });
});

var menuToggle = document.querySelector('.menu-toggle');
var siteNav = document.querySelector('.site-nav');

if (menuToggle && siteNav) {
    menuToggle.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = siteNav.classList.contains('nav-open');
        siteNav.classList.toggle('nav-open', !isOpen);
        menuToggle.setAttribute('aria-expanded', String(!isOpen));
    });
}

document.addEventListener('click', function (e) {
    document.querySelectorAll('.nav-group.open').forEach(function (openGroup) {
        openGroup.classList.remove('open');
        openGroup.querySelector('.nav-toggle').setAttribute('aria-expanded', 'false');
    });

    if (siteNav && menuToggle && siteNav.classList.contains('nav-open') && !siteNav.contains(e.target) && e.target !== menuToggle) {
        siteNav.classList.remove('nav-open');
        menuToggle.setAttribute('aria-expanded', 'false');
    }
});