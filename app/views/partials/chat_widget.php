<?php
/**
 * Widget chatbot — file này phải được include SAU khi load jquery.min.js (xem layouts/footer.php).
 * Session id lưu trong PHP session để ổn định giữa các trang.
 */
if (empty($_SESSION['chatbot_session_id'])) {
    try {
        $_SESSION['chatbot_session_id'] = sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff) | 0x4000,
            random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff)
        );
    } catch (Throwable $e) {
        $_SESSION['chatbot_session_id'] = uniqid('chat_', true);
    }
}
?>
<div id="re-chatbot-root" class="re-chatbot-root" aria-live="polite">
    <button type="button" class="re-chatbot-fab" id="re-chatbot-toggle" aria-expanded="false" aria-controls="re-chatbot-panel" title="Tìm BĐS bằng chat">
        <span class="re-chatbot-fab-icon">💬</span>
    </button>
    <div class="re-chatbot-panel" id="re-chatbot-panel" hidden>
        <div class="re-chatbot-header">
            <span>Tìm kiếm thông minh</span>
            <div class="re-chatbot-header-actions">
                <button type="button" class="re-chatbot-linkbtn" id="re-chatbot-reset">Xóa hội thoại</button>
                <button type="button" class="re-chatbot-close" id="re-chatbot-close" aria-label="Đóng">×</button>
            </div>
        </div>
        <div class="re-chatbot-messages" id="re-chatbot-messages"></div>
        <form class="re-chatbot-form" id="re-chatbot-form" autocomplete="off">
            <input type="text" id="re-chatbot-input" maxlength="2000" placeholder="Ví dụ: Căn hộ chung cư 2 phòng ngủ Quận 7 dưới 20 triệu…" aria-label="Nhập yêu cầu" />
            <button type="submit" class="re-chatbot-send">Gửi</button>
        </form>
    </div>
</div>
<style>
.re-chatbot-root { position: fixed; right: 18px; bottom: 18px; z-index: 9998; font-family: Roboto, system-ui, sans-serif; }
.re-chatbot-fab {
    width: 56px; height: 56px; border-radius: 50%; border: none; cursor: pointer;
    background: #1a56a8; color: #fff; box-shadow: 0 6px 20px rgba(0,0,0,.18);
    display: flex; align-items: center; justify-content: center; font-size: 22px;
}
.re-chatbot-fab:hover { filter: brightness(1.05); }
.re-chatbot-panel {
    position: absolute; right: 0; bottom: 64px; width: min(100vw - 32px, 380px); height: 480px;
    background: #fff; border-radius: 12px; box-shadow: 0 12px 40px rgba(0,0,0,.2);
    display: flex; flex-direction: column; overflow: hidden; border: 1px solid #e5e7eb;
}
.re-chatbot-header {
    display: flex; align-items: center; justify-content: space-between; padding: 10px 12px;
    background: #f8fafc; font-weight: 600; font-size: 14px; border-bottom: 1px solid #e5e7eb;
}
.re-chatbot-header-actions { display: flex; gap: 8px; align-items: center; }
.re-chatbot-linkbtn {
    background: none; border: none; color: #1a56a8; font-size: 12px; cursor: pointer; padding: 0;
}
.re-chatbot-close { background: none; border: none; font-size: 22px; line-height: 1; cursor: pointer; color: #64748b; }
.re-chatbot-messages { flex: 1; overflow-y: auto; padding: 10px; background: #f1f5f9; }
.re-chatbot-bubble { max-width: 92%; padding: 8px 10px; border-radius: 10px; margin-bottom: 8px; font-size: 13px; line-height: 1.45; white-space: pre-wrap; }
.re-chatbot-bubble.user { margin-left: auto; background: #1a56a8; color: #fff; border-bottom-right-radius: 2px; }
.re-chatbot-bubble.bot { margin-right: auto; background: #fff; border: 1px solid #e2e8f0; border-bottom-left-radius: 2px; }
.re-chatbot-card {
    margin-top: 6px; padding: 8px; border-radius: 8px; background: #fff; border: 1px solid #e2e8f0; font-size: 12px;
}
.re-chatbot-card a { color: #1a56a8; font-weight: 600; }
.re-chatbot-form { display: flex; gap: 6px; padding: 8px; border-top: 1px solid #e5e7eb; background: #fff; }
.re-chatbot-form input { flex: 1; border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px 10px; font-size: 13px; }
.re-chatbot-send { border: none; border-radius: 8px; padding: 0 14px; background: #1a56a8; color: #fff; font-weight: 600; cursor: pointer; }
.re-chatbot-send:disabled { opacity: .6; cursor: wait; }
</style>
<script>
(function () {
    var sessionId = <?php echo json_encode((string) $_SESSION['chatbot_session_id'], JSON_UNESCAPED_UNICODE); ?>;
    var endpoint = <?php echo json_encode(rtrim(BASEURL, '/') . '/chatbot/message', JSON_UNESCAPED_UNICODE); ?>;
    var resetEndpoint = <?php echo json_encode(rtrim(BASEURL, '/') . '/chatbot/reset', JSON_UNESCAPED_UNICODE); ?>;
    var eventEndpoint = <?php echo json_encode(rtrim(BASEURL, '/') . '/chatbot/event', JSON_UNESCAPED_UNICODE); ?>;

    function el(id) { return document.getElementById(id); }
    var root = el('re-chatbot-root');
    if (!root || !window.jQuery) { return; }
    var $ = window.jQuery;
    var panel = el('re-chatbot-panel');
    var msgs = el('re-chatbot-messages');
    var input = el('re-chatbot-input');
    var form = el('re-chatbot-form');
    var toggle = el('re-chatbot-toggle');
    var closeBtn = el('re-chatbot-close');
    var resetBtn = el('re-chatbot-reset');

    function appendBubble(text, who) {
        var d = document.createElement('div');
        d.className = 're-chatbot-bubble ' + (who === 'user' ? 'user' : 'bot');
        d.textContent = text;
        msgs.appendChild(d);
        msgs.scrollTop = msgs.scrollHeight;
    }

    function appendCards(props) {
        if (!props || !props.length) { return; }
        var wrap = document.createElement('div');
        wrap.className = 're-chatbot-bubble bot';
        props.forEach(function (p) {
            var c = document.createElement('div');
            c.className = 're-chatbot-card';
            var title = p.title || ('Tin #' + p.pid);
            var price = p.price_raw || '';
            var loc = (p.ward_name || '') + (p.city_name ? (' · ' + p.city_name) : '');
            var img = p.image_path || '';
            var reasons = p.matched_reasons || [];
            var score = p.ranking_score || null;
            var html = '';
            if (img) { html += '<div style="margin-bottom:6px"><img src="' + img.replace(/"/g, '') + '" alt="" style="max-width:100%;max-height:120px;border-radius:6px;object-fit:cover"/></div>'; }
            html += '<div><strong>' + $('<div/>').text(title).html() + '</strong></div>';
            html += '<div>Giá: ' + $('<div/>').text(price).html() + (p.stype === 'rent' ? ' triệu/tháng' : ' triệu') + '</div>';
            if (loc) { html += '<div style="color:#64748b">' + $('<div/>').text(loc).html() + '</div>'; }
            if (reasons.length) {
                html += '<div style="color:#475569;margin-top:4px">Phù hợp: ' + $('<div/>').text(reasons.slice(0, 2).join(', ')).html() + '</div>';
            }
            if (score) {
                html += '<div style="color:#64748b;font-size:11px">AI score: ' + $('<div/>').text(String(score)).html() + '</div>';
            }
            var link = p.detail_path || '#';
            html += '<div style="margin-top:6px"><a class="re-chatbot-card-link" data-pid="' + String(p.pid || '').replace(/"/g, '') + '" href="' + link.replace(/"/g, '&quot;') + '">Xem chi tiết</a></div>';
            c.innerHTML = html;
            wrap.appendChild(c);
        });
        msgs.appendChild(wrap);
        msgs.scrollTop = msgs.scrollHeight;
    }

    msgs.addEventListener('click', function (e) {
        var target = e.target;
        if (!target || !target.classList || !target.classList.contains('re-chatbot-card-link')) { return; }
        var pid = parseInt(target.getAttribute('data-pid') || '0', 10);
        if (!pid) { return; }
        $.ajax({
            url: eventEndpoint,
            method: 'POST',
            dataType: 'json',
            contentType: 'application/json; charset=UTF-8',
            data: JSON.stringify({
                event_type: 'chat_result_click',
                property_id: pid,
                session_id: sessionId,
                source: 'chatbot',
                metadata: { href: target.getAttribute('href') || '' }
            })
        });
    });

    function setOpen(open) {
        panel.hidden = !open;
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) { setTimeout(function () { input.focus(); }, 50); }
    }

    toggle.addEventListener('click', function () { setOpen(panel.hidden); });
    closeBtn.addEventListener('click', function () { setOpen(false); });

    resetBtn.addEventListener('click', function () {
        $.ajax({
            url: resetEndpoint,
            method: 'POST',
            dataType: 'json',
            contentType: 'application/json; charset=UTF-8',
            data: JSON.stringify({ session_id: sessionId }),
            success: function () {
                msgs.innerHTML = '';
                appendBubble('Đã xóa ngữ cảnh hội thoại. Bạn cần tìm BĐS thế nào?', 'bot');
            }
        });
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var text = (input.value || '').trim();
        if (!text) { return; }
        appendBubble(text, 'user');
        input.value = '';
        var btn = form.querySelector('.re-chatbot-send');
        btn.disabled = true;
        $.ajax({
            url: endpoint,
            method: 'POST',
            dataType: 'json',
            contentType: 'application/json; charset=UTF-8',
            data: JSON.stringify({ session_id: sessionId, user_text: text, locale: 'vi-VN' }),
            success: function (data) {
                if (data && data.reply_text) { appendBubble(data.reply_text, 'bot'); }
                if (data && data.properties) { appendCards(data.properties); }
            },
            error: function (xhr) {
                var msg = 'Lỗi kết nối chatbot.';
                try {
                    var j = xhr.responseJSON || JSON.parse(xhr.responseText);
                    if (j && j.reply_text) { msg = j.reply_text; }
                } catch (ignore) {}
                appendBubble(msg, 'bot');
            },
            complete: function () { btn.disabled = false; }
        });
    });
})();
</script>
