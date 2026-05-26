(function () {
    'use strict';

    function toArray(list) {
        return Array.prototype.slice.call(list || []);
    }

    function getLabelText(option) {
        var labelSpan = option.querySelector('span:not(.lx-select-radio)');
        if (labelSpan) {
            return labelSpan.textContent.trim();
        }
        return option.textContent.trim();
    }

    var overlay = null;

    function initOverlay() {
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'lx-select-overlay';
            // Styling the overlay
            overlay.style.position = 'fixed';
            overlay.style.top = '0';
            overlay.style.left = '0';
            overlay.style.width = '100vw';
            overlay.style.height = '100vh';
            overlay.style.backgroundColor = 'rgba(0, 15, 36, 0.4)';
            overlay.style.zIndex = '15'; // Below .pp-hero (20) but above standard content
            overlay.style.opacity = '0';
            overlay.style.visibility = 'hidden';
            overlay.style.transition = 'opacity 0.25s ease, visibility 0.25s ease';
            overlay.style.pointerEvents = 'none'; // so it doesn't block clicks when hidden
            document.body.appendChild(overlay);
        }
    }

    function initSelect(select) {
        if (!select || select.__lxInitialized) {
            return;
        }
        select.__lxInitialized = true;

        var toggle = select.querySelector('.lx-select-toggle');
        var menu = select.querySelector('.lx-select-menu');
        var text = select.querySelector('.lx-select-text');
        var options = toArray(select.querySelectorAll('.lx-select-option'));
        var closeBtn = select.querySelector('.lx-select-menu-close');
        var resetBtn = select.querySelector('.lx-select-reset');
        var applyBtn = select.querySelector('.lx-select-apply');
        var targetSelector = select.getAttribute('data-target');
        var hiddenInput = targetSelector ? document.querySelector(targetSelector) : null;
        var form = null;

        if (typeof select.closest === 'function') {
            form = select.closest('form');
        }

        if (!form) {
            var parent = select.parentElement;
            while (parent && parent !== document.body) {
                if (parent.tagName && parent.tagName.toLowerCase() === 'form') {
                    form = parent;
                    break;
                }
                parent = parent.parentElement;
            }
        }

        if (!toggle || !menu || !text || options.length === 0) {
            return;
        }

        var closeOnOutside = function (event) {
            if (!select.contains(event.target)) {
                closeSelect();
            }
        };

        var onKeydown = function (event) {
            if (event.key === 'Escape') {
                closeSelect();
                toggle.focus();
            }
        };

        function closeOthers() {
            toArray(document.querySelectorAll('.lx-select.is-open')).forEach(function (openSelect) {
                if (openSelect !== select && typeof openSelect.__lxClose === 'function') {
                    openSelect.__lxClose();
                }
            });
        }

        function openSelect() {
            if (select.classList.contains('is-open')) {
                return;
            }
            closeOthers();
            select.classList.add('is-open');
            toggle.setAttribute('aria-expanded', 'true');
            menu.setAttribute('aria-hidden', 'false');
            document.addEventListener('click', closeOnOutside);
            document.addEventListener('keydown', onKeydown);
            
            if (overlay) {
                overlay.style.opacity = '1';
                overlay.style.visibility = 'visible';
                overlay.style.pointerEvents = 'auto';
            }
        }

        function closeSelect() {
            if (!select.classList.contains('is-open')) {
                return;
            }
            select.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
            menu.setAttribute('aria-hidden', 'true');
            document.removeEventListener('click', closeOnOutside);
            document.removeEventListener('keydown', onKeydown);
            
            // Check if any other select is open
            if (document.querySelectorAll('.lx-select.is-open').length === 0 && overlay) {
                overlay.style.opacity = '0';
                overlay.style.visibility = 'hidden';
                overlay.style.pointerEvents = 'none';
            }
        }

        function updateValue(option) {
            options.forEach(function (item) {
                item.classList.remove('is-active');
                item.setAttribute('aria-selected', 'false');
            });

            option.classList.add('is-active');
            option.setAttribute('aria-selected', 'true');

            var value = option.getAttribute('data-value') || '';
            if (hiddenInput) {
                hiddenInput.value = value;
            }
            text.textContent = getLabelText(option);
            if (value !== '') {
                select.classList.add('has-value');
            } else {
                select.classList.remove('has-value');
            }
        }

        select.__lxClose = closeSelect;

        toggle.addEventListener('click', function (event) {
            event.preventDefault();
            if (select.classList.contains('is-open')) {
                closeSelect();
            } else {
                openSelect();
            }
        });

        toggle.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                if (select.classList.contains('is-open')) {
                    closeSelect();
                } else {
                    openSelect();
                }
            }
        });

        options.forEach(function (option) {
            option.setAttribute('tabindex', '0');
            option.addEventListener('click', function () {
                updateValue(option);
            });
            option.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    updateValue(option);
                }
            });
        });

        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                closeSelect();
            });
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                var defaultOption = options[0];
                if (defaultOption) {
                    updateValue(defaultOption);
                }
            });
        }

        if (applyBtn) {
            applyBtn.addEventListener('click', function () {
                closeSelect();
                if (form && typeof form.submit === 'function') {
                    form.submit();
                } else {
                    toggle.focus();
                }
            });
        } else {
            options.forEach(function (option) {
                option.addEventListener('click', function () {
                    closeSelect();
                });
            });
        }

        var initialOption = null;
        if (hiddenInput) {
            initialOption = options.find(function (option) {
                return option.getAttribute('data-value') === hiddenInput.value;
            });
        }
        if (!initialOption) {
            initialOption = options.find(function (option) {
                return option.classList.contains('is-active');
            }) || options[0];
        }
        if (initialOption) {
            updateValue(initialOption);
        }

        closeSelect();
    }

    var LXSelect = {
        init: function (root) {
            initOverlay();
            var context = root && root.querySelectorAll ? root : document;
            toArray(context.querySelectorAll('.lx-select[data-target]')).forEach(initSelect);
        }
    };

    if (typeof window !== 'undefined') {
        window.LXSelect = LXSelect;
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                LXSelect.init();
            });
        } else {
            LXSelect.init();
        }
    }
})();
