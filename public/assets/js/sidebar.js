(function () {
    'use strict';

    var body = document.body;
    var sidebar = document.getElementById('sidenav-main');
    var toggle = document.querySelector('[data-sidebar-toggle]');

    if (!body || !sidebar) {
        return;
    }

    var topLinks = sidebar.querySelectorAll('.navbar-nav > .nav-item > .nav-link');
    var expanded = true;

    if (window.jQuery) {
        window.jQuery(sidebar).off('mouseenter mouseleave');
    }

    Array.prototype.forEach.call(topLinks, function (link) {
        var label = link.querySelector('.nav-link-text');

        if (label) {
            link.setAttribute('data-sidebar-label', label.textContent.trim());
            link.setAttribute('aria-label', label.textContent.trim());
        }
    });

    function readSavedState() {
        if (!window.Cookies) {
            return true;
        }

        var saved = Cookies.get('app-sidebar-expanded');

        if (saved === 'false') {
            return false;
        }

        if (saved === 'true') {
            return true;
        }

        return Cookies.get('sidenav-state') !== 'unpinned';
    }

    function syncAccessibility() {
        sidebar.setAttribute('data-expanded', expanded ? 'true' : 'false');

        Array.prototype.forEach.call(topLinks, function (link) {
            var label = link.getAttribute('data-sidebar-label');

            if (!label) {
                return;
            }

            if (expanded) {
                link.removeAttribute('title');
            } else {
                link.setAttribute('title', label);
            }
        });

        if (toggle) {
            toggle.classList.toggle('active', expanded);
            toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            toggle.setAttribute('aria-label', expanded ? 'Ciutkan sidebar' : 'Buka sidebar');
            toggle.setAttribute('title', expanded ? 'Ciutkan sidebar' : 'Buka menu utama');
        }
    }

    function applyState(nextExpanded, persist) {
        expanded = Boolean(nextExpanded);

        if (window.innerWidth >= 1200) {
            body.classList.toggle('app-sidenav-expanded', expanded);

            if (expanded) {
                body.classList.remove('g-sidenav-hidden', 'g-sidenav-hide');
                body.classList.add('g-sidenav-show', 'g-sidenav-pinned');
            } else {
                body.classList.remove('g-sidenav-show', 'g-sidenav-pinned', 'g-sidenav-hide');
                body.classList.add('g-sidenav-hidden');
                body.querySelectorAll('.backdrop').forEach(function (backdrop) {
                    backdrop.remove();
                });
            }
        }

        if (persist && window.Cookies) {
            Cookies.set('app-sidebar-expanded', expanded ? 'true' : 'false');
            Cookies.set('sidenav-state', expanded ? 'pinned' : 'unpinned');
        }

        syncAccessibility();
    }

    if (toggle) {
        toggle.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            applyState(!expanded, true);
        });
    }

    Array.prototype.forEach.call(topLinks, function (link) {
        link.addEventListener('click', function (event) {
            if (window.innerWidth < 1200 || expanded || link.getAttribute('data-toggle') !== 'collapse') {
                return;
            }

            event.preventDefault();
            applyState(true, true);

            if (window.jQuery && link.getAttribute('href')) {
                window.jQuery(link.getAttribute('href')).collapse('show');
            }
        });
    });

    expanded = readSavedState();
    applyState(expanded, false);
})();
