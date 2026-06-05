<?php

class ChatbotController extends Controller
{
    //chỉ dùng để tự tạo JSON lỗi hoặc JSON đơn giản từ PHP
    private function jsonResponse(int $httpCode, array $payload): void
    {
        http_response_code($httpCode);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    // gửi yêu cầu tới chatbot service
    // hàm proxyFASTAPi nhận path , phương thức POST hoặc GET , jsonBody dạng string
    private function proxyToFastApi(string $path, string $method, ?string $jsonBody = null): void
    {
        // nối base url của chatbot service và path
        $base = rtrim(CHATBOT_SERVICE_URL, '/');
        // nối thêm url
        $url = $base . $path;

        // tạo headers để xác thực với chatbot service
        $headers = [];
        // nếu là phương thức POST thì thêm header content type 
        if ($method === 'POST') {
            $headers[] = 'Content-Type: application/json; charset=utf-8';
        }
        // nếu có secret thì thêm header secret
        if (CHATBOT_INTERNAL_SECRET !== '') {
            $headers[] = 'X-Internal-Secret: ' . CHATBOT_INTERNAL_SECRET;
        }

        // tạo curl resource
        $ch = curl_init($url);
        // set các tùy chọn curl 
        $opts = [
            CURLOPT_RETURNTRANSFER => true, // trả về dạng chuỗi 
            CURLOPT_TIMEOUT => 90, // timeout 90s
            CURLOPT_HTTPHEADER => $headers, // thêm header
        ];
        // nếu là phương thức POST thì thêm post và jsonbody 
        if ($method === 'POST') {
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = $jsonBody ?? '{}';
        } else {
            // nếu là GET thì thêm GET
            $opts[CURLOPT_HTTPGET] = true;
        }
        // thực thi curl
        curl_setopt_array($ch, $opts);
        // lấy kết quả
        $res = curl_exec($ch);
        // lấy lỗi nếu có 
        $err = curl_error($ch);
        // lấy mã lỗi
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // đóng curl
        curl_close($ch);

        // nếu không lấy được kết quả thì trả về lỗi 503
        if ($res === false) {
            $this->jsonResponse(503, [
                'reply_text' => 'Không kết nối được dịch vụ chatbot. Hãy chạy FastAPI (xem chatbot-service/README.md) và kiểm tra CHATBOT_SERVICE_URL.',
                'intent' => 'unknown',
                'filters' => ['stype' => 'rent'],
                'missing_slots' => [],
                'follow_up_questions' => [],
                'result_count' => 0,
                'fallback_level' => 0,
                'fallback_note' => $err ?: null,
                'properties' => [],
                'session_id' => '',
            ]);
            return;
        }
        // trả về kết quả cho frontend
        http_response_code($code > 0 ? $code : 200);
        // thêm header content type 
        header('Content-Type: application/json; charset=utf-8');
        // thêm header content type options
        header('X-Content-Type-Options: nosniff');
        // in ra kết quả
        echo $res;
    }

    //gửi tin nhắn cho chatbot
    public function message(): void
    {
        // kiểm tra request có phải POST không
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->jsonResponse(405, ['error' => 'Method not allowed']);
            return;
        }

        // php đọc dữ liệu frontend gửi lên và lấy raw json
        $raw = file_get_contents('php://input');
        // chuyển json thành array
        $body = json_decode((string) $raw, true);
        // kiểm tra body có phải là array không 
        if (!is_array($body)) {
            $this->jsonResponse(400, ['error' => 'Invalid JSON body']);
            return;
        }

        // kiểm tra session_id và user_text có tồn tại và không rỗng không 
        $sessionId = isset($body['session_id']) ? trim((string) $body['session_id']) : '';
        $text = isset($body['user_text']) ? trim((string) $body['user_text']) : '';
        if ($sessionId === '' || $text === '') {
            $this->jsonResponse(400, ['error' => 'session_id và user_text là bắt buộc']);
            return;
        }

        // lấy user đang login nếu chưa logn trả về null
        $uid = isset($_SESSION['uid']) ? (int) $_SESSION['uid'] : null;
        // tạo payload gửi sang chatbot service 
        $payload = [
            'session_id' => $sessionId,
            'user_text' => $text,
            'user_id' => $uid > 0 ? $uid : null,
            'locale' => isset($body['locale']) ? (string) $body['locale'] : 'vi-VN',
        ];

        // php gọi FAST API qua proxyToFastApi 
        $this->proxyToFastApi('/v1/chat/message', 'POST', json_encode($payload, JSON_UNESCAPED_UNICODE));
    }

    // reset phiên chat của người dùng 
    public function reset(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->jsonResponse(405, ['error' => 'Method not allowed']);
            return;
        }

        $raw = file_get_contents('php://input');
        $body = json_decode((string) $raw, true);
        if (!is_array($body)) {
            $this->jsonResponse(400, ['error' => 'Invalid JSON body']);
            return;
        }

        $sessionId = isset($body['session_id']) ? trim((string) $body['session_id']) : '';
        if ($sessionId === '') {
            $this->jsonResponse(400, ['error' => 'session_id là bắt buộc']);
            return;
        }

        $uid = isset($_SESSION['uid']) ? (int) $_SESSION['uid'] : null;
        $payload = [
            'session_id' => $sessionId,
            'user_id' => $uid > 0 ? $uid : null,
        ];

        $this->proxyToFastApi('/v1/chat/reset', 'POST', json_encode($payload, JSON_UNESCAPED_UNICODE));
    }

    // lấy các gợi ý các câu hỏi cho chatbot 
    public function suggestions(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
            $this->jsonResponse(405, ['error' => 'Method not allowed']);
            return;
        }

        $sessionId = isset($_GET['session_id']) ? trim((string) $_GET['session_id']) : '';
        if ($sessionId === '') {
            $this->jsonResponse(400, ['error' => 'session_id là bắt buộc']);
            return;
        }

        $uid = isset($_SESSION['uid']) ? (int) $_SESSION['uid'] : null;
        $q = http_build_query([
            'session_id' => $sessionId,
            'user_id' => $uid > 0 ? $uid : null,
        ]);

        $this->proxyToFastApi('/v1/chat/suggestions?' . $q, 'GET', null);
    }

    // lấy đề xuất các tin BĐS cho người dùng 
    public function recommendations(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
            $this->jsonResponse(405, ['error' => 'Method not allowed']);
            return;
        }

        $uid = isset($_SESSION['uid']) ? (int) $_SESSION['uid'] : null;
        $limit = isset($_GET['limit']) ? max(1, min(30, (int) $_GET['limit'])) : 8;
        $q = http_build_query([
            'user_id' => $uid > 0 ? $uid : null,
            'limit' => $limit,
        ]);

        $this->proxyToFastApi('/v1/recommendations?' . $q, 'GET', null);
    }

    // Xử lý sự kiện từ người dùng
    public function event(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->jsonResponse(405, ['error' => 'Method not allowed']);
            return;
        }

        $raw = file_get_contents('php://input');
        $body = json_decode((string) $raw, true);
        if (!is_array($body)) {
            $this->jsonResponse(400, ['error' => 'Invalid JSON body']);
            return;
        }

        $eventType = isset($body['event_type']) ? trim((string) $body['event_type']) : '';
        $propertyId = isset($body['property_id']) ? (int) $body['property_id'] : 0;
        if ($eventType === '' || $propertyId <= 0) {
            $this->jsonResponse(400, ['error' => 'event_type và property_id là bắt buộc']);
            return;
        }

        $uid = isset($_SESSION['uid']) ? (int) $_SESSION['uid'] : null;
        $payload = [
            'event_type' => $eventType,
            'property_id' => $propertyId,
            'session_id' => isset($body['session_id']) ? trim((string) $body['session_id']) : null,
            'user_id' => $uid > 0 ? $uid : null,
            'source' => isset($body['source']) ? trim((string) $body['source']) : 'chatbot',
            'metadata' => isset($body['metadata']) && is_array($body['metadata']) ? $body['metadata'] : [],
        ];

        $this->proxyToFastApi('/v1/events', 'POST', json_encode($payload, JSON_UNESCAPED_UNICODE));
    }
}
