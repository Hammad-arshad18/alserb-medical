(function () {
    'use strict';

    var toggle = document.querySelector('[data-nav-toggle]');
    var nav = document.getElementById('site-nav');

    if (!toggle || !nav) {
        return;
    }

    function setOpen(open) {
        nav.classList.toggle('is-open', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        document.body.classList.toggle('nav-open', open);
    }

    toggle.addEventListener('click', function () {
        setOpen(!nav.classList.contains('is-open'));
    });

    nav.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
            setOpen(false);
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && nav.classList.contains('is-open')) {
            setOpen(false);
            toggle.focus();
        }
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 960 && nav.classList.contains('is-open')) {
            setOpen(false);
        }
    });
}());

(function () {
    'use strict';

    var snackbar = document.getElementById('snackbar');
    var snackbarTimer = null;
    var snackbarHideTimer = null;

    function showSnackbar(message, isError) {
        if (!snackbar) {
            return;
        }

        snackbar.textContent = message;
        snackbar.hidden = false;
        snackbar.classList.toggle('is-error', Boolean(isError));

        window.requestAnimationFrame(function () {
            snackbar.classList.add('is-visible');
        });

        window.clearTimeout(snackbarTimer);
        window.clearTimeout(snackbarHideTimer);
        snackbarTimer = window.setTimeout(function () {
            snackbar.classList.remove('is-visible');
            snackbarHideTimer = window.setTimeout(function () {
                snackbar.hidden = true;
            }, 220);
        }, 3200);
    }

    function updateCartCount(count) {
        count = Number(count) || 0;

        document.querySelectorAll('.cart-link, .nav__cart').forEach(function (container) {
            var badge = container.querySelector('.cart-count');

            if (count > 0) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'cart-count';
                    container.appendChild(badge);
                }
                badge.textContent = String(count);
            } else if (badge) {
                badge.remove();
            }
        });
    }

    document.querySelectorAll('form[data-cart-add]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.fetch) {
                return;
            }

            event.preventDefault();

            var button = form.querySelector('button[type="submit"]');
            if (button) {
                button.disabled = true;
                button.setAttribute('aria-busy', 'true');
            }

            window.fetch(form.action, {
                method: 'POST',
                body: new window.FormData(form),
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            }).then(function (response) {
                return response.json().catch(function () {
                    return null;
                }).then(function (payload) {
                    if (!response.ok || !payload || payload.ok !== true) {
                        throw new Error(payload && payload.message ? payload.message : 'Could not add this item. Please try again.');
                    }
                    return payload;
                });
            }).then(function (payload) {
                updateCartCount(payload.cart_count);
                showSnackbar(payload.message || 'Added to your order list.', false);
            }).catch(function (error) {
                showSnackbar(error.message || 'Could not add this item. Please try again.', true);
            }).finally(function () {
                if (button) {
                    button.disabled = false;
                    button.removeAttribute('aria-busy');
                }
            });
        });
    });
}());
