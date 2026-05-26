/**
 * AppPopup — reusable popup/modal utility
 *
 * Usage:
 *   AppPopup.show({ title, message, type, confirmText, cancelText, onConfirm, onCancel })
 *   AppPopup.success(message, onConfirm)
 *   AppPopup.error(message, onConfirm)
 *   AppPopup.warning(message, onConfirm)
 *   AppPopup.info(message, onConfirm)
 *   AppPopup.confirm(message, onConfirm, onCancel)
 *
 * Types: 'success' | 'error' | 'warning' | 'info'
 */
(function (global) {
    'use strict';

    var ICONS = {
        success: '✓',
        error:   '✕',
        warning: '!',
        info:    'ℹ',
    };

    var TITLES = {
        success: 'Thông báo',
        error:   'Cảnh báo',
        warning: 'Cảnh báo',
        info:    'Thông báo',
    };

    var overlay  = null;
    var iconWrap = null;
    var icon     = null;
    var title    = null;
    var msg      = null;
    var btnOk    = null;
    var btnCancel = null;

    function init() {
        overlay   = document.getElementById('app-popup-overlay');
        iconWrap  = document.getElementById('app-popup-icon-wrap');
        icon      = document.getElementById('app-popup-icon');
        title     = document.getElementById('app-popup-title');
        msg       = document.getElementById('app-popup-msg');
        btnOk     = document.getElementById('app-popup-confirm');
        btnCancel = document.getElementById('app-popup-cancel');

        if (!overlay) return;

        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) AppPopup.close();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !overlay.hidden) AppPopup.close();
        });
    }

    var AppPopup = {
        show: function (opts) {
            if (!overlay) init();
            if (!overlay) return;

            var type        = opts.type        || 'info';
            var titleText   = opts.title       || TITLES[type] || 'Thông báo';
            var messageText = opts.message     || '';
            var confirmText = opts.confirmText || 'Xác nhận';
            var cancelText  = opts.cancelText  || 'Hủy';
            var onConfirm   = typeof opts.onConfirm === 'function' ? opts.onConfirm : null;
            var onCancel    = typeof opts.onCancel  === 'function' ? opts.onCancel  : null;

            iconWrap.className = 'app-popup-icon-wrap ' + type;
            icon.textContent   = ICONS[type] || 'ℹ';
            title.textContent  = titleText;
            msg.textContent    = messageText;

            btnOk.textContent = confirmText;
            btnOk.className   = 'app-popup-confirm ' + type;
            btnOk.onclick     = function () {
                AppPopup.close();
                if (onConfirm) onConfirm();
            };

            if (onCancel || opts.cancelText) {
                btnCancel.textContent = cancelText;
                btnCancel.hidden      = false;
                btnCancel.onclick     = function () {
                    AppPopup.close();
                    if (onCancel) onCancel();
                };
            } else {
                btnCancel.hidden  = true;
                btnCancel.onclick = null;
            }

            overlay.hidden = false;
            btnOk.focus();
        },

        close: function () {
            if (overlay) overlay.hidden = true;
        },

        success: function (message, onConfirm) {
            this.show({ type: 'success', message: message, onConfirm: onConfirm });
        },

        error: function (message, onConfirm) {
            this.show({ type: 'error', message: message, onConfirm: onConfirm });
        },

        warning: function (message, onConfirm) {
            this.show({ type: 'warning', message: message, onConfirm: onConfirm });
        },

        info: function (message, onConfirm) {
            this.show({ type: 'info', message: message, onConfirm: onConfirm });
        },

        confirm: function (message, onConfirm, onCancel) {
            this.show({
                type:       'warning',
                message:    message,
                confirmText: 'Xác nhận',
                cancelText:  'Hủy',
                onConfirm:  onConfirm,
                onCancel:   onCancel,
            });
        },
    };

    function removeQueryParam(param) {
        if (!param || !global.history || typeof global.location === 'undefined') {
            return;
        }

        try {
            if (typeof URL !== 'undefined' && typeof URLSearchParams !== 'undefined') {
                var url = new URL(global.location.href);
                if (!url.searchParams.has(param)) {
                    return;
                }
                url.searchParams.delete(param);
                var next = url.pathname;
                var remainder = url.searchParams.toString();
                if (remainder) {
                    next += '?' + remainder;
                }
                if (url.hash) {
                    next += url.hash;
                }
                global.history.replaceState(null, document.title, next);
                return;
            }
        } catch (err) {
            // fall through to string replacement fallback
        }

        var pattern = new RegExp('([?&])' + param + '=[^&#]*');
        var cleaned = global.location.href.replace(pattern, '$1');
        cleaned = cleaned.replace(/[?&]$/, '');
        if (global.history && global.history.replaceState) {
            global.history.replaceState(null, document.title, cleaned);
        }
    }

    function consumeAutoPopup() {
        var config = global.APP_POPUP_AUTO;
        if (!config || !config.message) {
            return;
        }

        delete global.APP_POPUP_AUTO;

        var type = config.type || 'info';
        var confirmLabel = config.confirmText || 'Đóng';
        var onConfirm = typeof config.onConfirm === 'function' ? config.onConfirm : null;

        AppPopup.show({
            type: type,
            title: config.title || TITLES[type] || 'Thông báo',
            message: config.message,
            confirmText: confirmLabel,
            onConfirm: onConfirm
        });

        if (config.removeParam) {
            removeQueryParam(config.removeParam);
        }
    }

    function scheduleAutoPopup() {
        if (!global.APP_POPUP_AUTO || !global.APP_POPUP_AUTO.message) {
            return;
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', consumeAutoPopup, { once: true });
        } else {
            consumeAutoPopup();
        }
    }

    global.AppPopup = AppPopup;

    scheduleAutoPopup();

}(window));
