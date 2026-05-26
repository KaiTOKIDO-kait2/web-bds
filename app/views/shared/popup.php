<?php
$popupAutoType = '';
$popupAutoMessage = '';

if (isset($_GET['msg']) && is_string($_GET['msg']) && $_GET['msg'] !== '') {
    $rawPopupMsg = (string) $_GET['msg'];
    $decodedPopupMsg = html_entity_decode($rawPopupMsg, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $normalizedPopupMsg = str_ireplace(['<br />', '<br/>', '<br>'], "\n", $decodedPopupMsg);
    $popupAutoMessage = trim(preg_replace('/\s+/', ' ', strip_tags($normalizedPopupMsg)) ?? '');

    if ($popupAutoMessage === '') {
        $popupAutoMessage = trim($normalizedPopupMsg);
    }

    if (stripos($rawPopupMsg, 'alert-success') !== false) {
        $popupAutoType = 'success';
    } elseif (stripos($rawPopupMsg, 'alert-danger') !== false) {
        $popupAutoType = 'error';
    } elseif (stripos($rawPopupMsg, 'alert-warning') !== false) {
        $popupAutoType = 'warning';
    } elseif ($popupAutoMessage !== '') {
        $popupAutoType = 'info';
    }

    if ($popupAutoMessage === '') {
        $popupAutoType = '';
    }
}

if ($popupAutoType === '' && $popupAutoMessage !== '') {
    $popupAutoType = 'info';
}
?>
<style>
    .app-popup-overlay {
        position: fixed; inset: 0; z-index: 99999;
        background: rgba(25, 28, 29, 0.48);
        display: flex; align-items: center; justify-content: center;
        padding: 16px;
        backdrop-filter: blur(3px);
        animation: app-popup-fadein 0.18s ease;
    }
    .app-popup-overlay[hidden] { display: none !important; }

    @keyframes app-popup-fadein {
        from { opacity: 0; }
        to   { opacity: 1; }
    }
    @keyframes app-popup-slidein {
        from { opacity: 0; transform: translateY(18px) scale(0.97); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    .app-popup-dialog {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.18);
        padding: 36px 32px 28px;
        max-width: 420px; width: 100%;
        text-align: center;
        animation: app-popup-slidein 0.22s ease;
    }

    .app-popup-icon-wrap {
        width: 64px; height: 64px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 18px;
        font-size: 28px;
    }
    .app-popup-icon-wrap.success { background: #eaf9ef; }
    .app-popup-icon-wrap.error   { background: #feeced; }
    .app-popup-icon-wrap.warning { background: #fff1e8; }
    .app-popup-icon-wrap.info    { background: #e8f1ff; }

    .app-popup-title {
        margin: 0 0 10px;
        font-size: 18px; font-weight: 700;
        color: #191c1d; line-height: 1.3;
    }
    .app-popup-msg {
        margin: 0 0 24px;
        font-size: 14px; color: #424654; line-height: 1.6;
    }
    .app-popup-actions {
        display: flex; gap: 10px; justify-content: center;
    }
    .app-popup-confirm {
        flex: 1; max-width: 140px;
        padding: 11px 20px; border-radius: 10px;
        font-size: 14px; font-weight: 700; cursor: pointer;
        border: none; color: #fff;
        transition: box-shadow 0.18s ease, transform 0.12s ease;
    }
    .app-popup-confirm:hover { transform: translateY(-1px); }
    .app-popup-confirm.success { background: linear-gradient(135deg,#17c964 0%,#12a053 100%); box-shadow: 0 3px 10px rgba(23,201,100,0.30); }
    .app-popup-confirm.success:hover { box-shadow: 0 5px 16px rgba(23,201,100,0.42); }
    .app-popup-confirm.error   { background: linear-gradient(135deg,#f31260 0%,#c20e4d 100%); box-shadow: 0 3px 10px rgba(243,18,96,0.28); }
    .app-popup-confirm.error:hover { box-shadow: 0 5px 16px rgba(243,18,96,0.40); }
    .app-popup-confirm.warning { background: linear-gradient(135deg,#f5a623 0%,#d4880a 100%); box-shadow: 0 3px 10px rgba(245,166,35,0.30); }
    .app-popup-confirm.info    { background: linear-gradient(135deg,#0056d2 0%,#0040a1 100%); box-shadow: 0 3px 10px rgba(0,86,210,0.28); }

    .app-popup-cancel {
        flex: 1; max-width: 140px;
        padding: 11px 20px; border-radius: 10px;
        font-size: 14px; font-weight: 600; cursor: pointer;
        border: 1.5px solid #c3c6d6; background: #fff; color: #424654;
        transition: background 0.18s ease, border-color 0.18s ease;
    }
    .app-popup-cancel:hover { background: #f3f4f5; border-color: #a0a5b9; }
    .app-popup-cancel[hidden] { display: none !important; }
</style>

<div id="app-popup-overlay" class="app-popup-overlay" hidden role="dialog" aria-modal="true" aria-labelledby="app-popup-title">
    <div class="app-popup-dialog">
        <div class="app-popup-icon-wrap info" id="app-popup-icon-wrap">
            <span id="app-popup-icon">ℹ</span>
        </div>
        <h3 class="app-popup-title" id="app-popup-title"></h3>
        <p  class="app-popup-msg"   id="app-popup-msg"></p>
        <div class="app-popup-actions">
            <button class="app-popup-confirm info" id="app-popup-confirm">Xác nhận</button>
            <button class="app-popup-cancel"       id="app-popup-cancel"  hidden>Hủy</button>
        </div>
    </div>
</div>

<?php if ($popupAutoMessage !== '' && $popupAutoType !== ''): ?>
    <script>
        window.APP_POPUP_AUTO = {
            message: <?= json_encode($popupAutoMessage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
            type: <?= json_encode($popupAutoType, JSON_UNESCAPED_UNICODE) ?>,
            removeParam: 'msg',
            confirmText: 'Đóng'
        };
    </script>
    <script>
        (function () {
            var config = window.APP_POPUP_AUTO;
            if (!config || !config.message) {
                return;
            }

            var handled = false;

            function removeParam(param) {
                if (!param || !history || !location) {
                    return;
                }

                try {
                    if (typeof URL !== 'undefined' && typeof URLSearchParams !== 'undefined') {
                        var url = new URL(window.location.href);
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
                        history.replaceState(null, document.title, next);
                        return;
                    }
                } catch (err) {
                    // ignore and fall back
                }

                var pattern = new RegExp('([?&])' + param + '=[^&#]*');
                var cleaned = window.location.href.replace(pattern, '$1');
                cleaned = cleaned.replace(/[?&]$/, '');
                if (history && typeof history.replaceState === 'function') {
                    history.replaceState(null, document.title, cleaned);
                }
            }

            function attemptShow() {
                if (handled) {
                    return;
                }

                if (!window.AppPopup || typeof window.AppPopup.show !== 'function') {
                    setTimeout(attemptShow, 80);
                    return;
                }

                handled = true;

                window.AppPopup.show({
                    type: config.type || 'info',
                    title: config.title || undefined,
                    message: config.message,
                    confirmText: config.confirmText || 'Đóng'
                });

                if (config.removeParam) {
                    removeParam(config.removeParam);
                }

                window.APP_POPUP_AUTO = null;
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', attemptShow, { once: true });
            } else {
                attemptShow();
            }
        })();
    </script>
<?php endif; ?>
