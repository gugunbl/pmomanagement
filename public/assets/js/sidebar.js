(function () {
    'use strict';

    var body = document.body;
    var sidebar = document.getElementById('sidenav-main');

    if (!body || !sidebar) {
        return;
    }

    var topLinks = sidebar.querySelectorAll('.navbar-nav > .nav-item > .nav-link');
    var toggles = document.querySelectorAll('[data-action="sidenav-pin"], [data-action="sidenav-unpin"]');

    /* One-time migration: introduce the redesigned sidebar in its most useful state. */
    if (window.innerWidth >= 1200 && window.Cookies && Cookies.get('sidebar-ui-version') !== '2') {
        body.classList.remove('g-sidenav-hidden', 'g-sidenav-hide');
        body.classList.add('g-sidenav-show', 'g-sidenav-pinned');
        Cookies.set('sidenav-state', 'pinned');
        Cookies.set('sidebar-ui-version', '2');
    }

    Array.prototype.forEach.call(topLinks, function (link) {
        var label = link.querySelector('.nav-link-text');

        if (label) {
            link.setAttribute('data-sidebar-label', label.textContent.trim());
        }
    });

    function sidebarIsExpanded() {
        return body.classList.contains('g-sidenav-pinned') || body.classList.contains('g-sidenav-show');
    }

    function syncAccessibility() {
        var expanded = sidebarIsExpanded();

        sidebar.setAttribute('data-expanded', expanded ? 'true' : 'false');
        Array.prototype.forEach.call(topLinks, function (link) {
            var label = link.getAttribute('data-sidebar-label');

            if (!label) {
                return;
            }

            link.setAttribute('aria-label', label);

            if (expanded) {
                link.removeAttribute('title');
            } else {
                link.setAttribute('title', label);
            }
        });

        Array.prototype.forEach.call(toggles, function (toggle) {
            toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            toggle.setAttribute('aria-label', expanded ? 'Ciutkan sidebar' : 'Buka sidebar');
            toggle.setAttribute('title', expanded ? 'Ciutkan sidebar' : 'Buka menu utama');
        });
    }

    sidebar.addEventListener('focusin', function () {
        if (window.innerWidth >= 1200 && !body.classList.contains('g-sidenav-pinned')) {
            body.classList.remove('g-sidenav-hide', 'g-sidenav-hidden');
            body.classList.add('g-sidenav-show');
            syncAccessibility();
        }
    });

    sidebar.addEventListener('focusout', function (event) {
        if (window.innerWidth >= 1200 && !body.classList.contains('g-sidenav-pinned') && !sidebar.contains(event.relatedTarget)) {
            body.classList.remove('g-sidenav-show');
            body.classList.add('g-sidenav-hidden');
            syncAccessibility();
        }
    });

    if (window.MutationObserver) {
        new MutationObserver(syncAccessibility).observe(body, {
            attributes: true,
            attributeFilter: ['class']
        });
    }

    syncAccessibility();
})();
