(function () {
    'use strict';

    var modelContext = (document.modelContext && typeof document.modelContext.registerTool === 'function')
        ? document.modelContext
        : (navigator.modelContext && typeof navigator.modelContext.registerTool === 'function'
            ? navigator.modelContext
            : null);

    if (modelContext) {
        var body = document.body;
        var siteName = body.getAttribute('data-site-name') || 'Alserb Medical';

        function registerTool(definition) {
            try {
                var result = modelContext.registerTool(definition);
                if (result && typeof result.then === 'function') {
                    result.catch(function () {});
                }
            } catch (error) {
                // WebMCP is progressive; ignore unsupported environments.
            }
        }

        registerTool({
            name: 'get_contact_details',
            description: 'Return Alserb Medical phone, email, WhatsApp, and address from the current page.',
            inputSchema: {
                type: 'object',
                properties: {},
                additionalProperties: false
            },
            annotations: { readOnlyHint: true },
            execute: function () {
                return {
                    name: siteName,
                    phone: body.getAttribute('data-phone') || '',
                    email: body.getAttribute('data-email') || '',
                    whatsapp: body.getAttribute('data-whatsapp') || '',
                    address: body.getAttribute('data-address') || '',
                    contactUrl: body.getAttribute('data-contact-url') || '/contact'
                };
            }
        });

        registerTool({
            name: 'search_equipment',
            description: 'Open the Alserb Medical equipment catalog filtered by an optional search query.',
            inputSchema: {
                type: 'object',
                properties: {
                    query: {
                        type: 'string',
                        description: 'Optional search terms such as ultrasound or patient monitor.'
                    }
                },
                additionalProperties: false
            },
            annotations: { readOnlyHint: true },
            execute: function (input) {
                var base = body.getAttribute('data-equipment-url') || '/equipment';
                var query = input && input.query ? String(input.query).trim() : '';
                var url = query ? (base + (base.indexOf('?') >= 0 ? '&' : '?') + 'q=' + encodeURIComponent(query)) : base;
                window.location.href = url;
                return { ok: true, navigatedTo: url };
            }
        });

        registerTool({
            name: 'go_to_page',
            description: 'Navigate to a key Alserb Medical page: home, about, equipment, supply, contact, or terms.',
            inputSchema: {
                type: 'object',
                properties: {
                    page: {
                        type: 'string',
                        enum: ['home', 'about', 'equipment', 'supply', 'contact', 'terms'],
                        description: 'Which site section to open.'
                    }
                },
                required: ['page'],
                additionalProperties: false
            },
            annotations: { readOnlyHint: true },
            execute: function (input) {
                var page = input && input.page ? String(input.page) : 'home';
                var map = {
                    home: body.getAttribute('data-home-url'),
                    about: body.getAttribute('data-about-url'),
                    equipment: body.getAttribute('data-equipment-url'),
                    supply: body.getAttribute('data-supply-url'),
                    contact: body.getAttribute('data-contact-url'),
                    terms: body.getAttribute('data-terms-url')
                };
                var url = map[page] || map.home || '/';
                window.location.href = url;
                return { ok: true, page: page, navigatedTo: url };
            }
        });
    }

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
