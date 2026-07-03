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

document.addEventListener('click', function () {
    document.querySelectorAll('.nav-group.open').forEach(function (openGroup) {
        openGroup.classList.remove('open');
        openGroup.querySelector('.nav-toggle').setAttribute('aria-expanded', 'false');
    });
});