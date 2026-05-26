# API contract — Chatbot tìm kiếm BĐS (MVP)

Base path FastAPI: `/v1`

Tất cả response JSON dùng `UTF-8`. Thời gian server: UTC hoặc local theo cấu hình DB (không ảnh hưởng MVP).

## Headers (tùy chọn bảo vệ nội bộ)

| Header | Mô tả |
|--------|--------|
| `X-Internal-Secret` | Phải khớp biến môi trường `INTERNAL_SECRET` nếu biến được đặt (PHP proxy gửi cùng giá trị). |

## `POST /v1/chat/message`

### Request body

```json
{
  "session_id": "550e8400-e29b-41d4-a716-446655440000",
  "user_text": "Căn hộ 2PN phường 1 dưới 20 triệu có hồ bơi",
  "user_id": 36,
  "locale": "vi-VN"
}
```

| Trường | Kiểu | Bắt buộc | Ghi chú |
|--------|------|-----------|---------|
| `session_id` | string (UUID) | Có | Định danh phiên hội thoại ổn định theo client/PHP session. |
| `user_text` | string | Có | Tối đa ~2000 ký tự (server cắt nếu dài hơn). |
| `user_id` | int \| null | Không | `uid` website nếu đã đăng nhập. |
| `locale` | string | Không | Mặc định `vi-VN`. |

### Response body

```json
{
  "reply_text": "Tìm thấy 3 tin phù hợp. Dưới đây là các lựa chọn nổi bật.",
  "intent": "property_search",
  "filters": {
    "stype": "rent",
    "city_id": 2,
    "ward_id": 13,
    "property_types": ["Căn hộ"],
    "bedrooms_min": 2,
    "price_max_million": 20,
    "amenities": { "swimming_pool": true }
  },
  "missing_slots": [],
  "follow_up_questions": [],
  "result_count": 3,
  "fallback_level": 0,
  "fallback_note": null,
  "properties": [
    {
      "pid": 70,
      "title": "Biệt thự cho thuê có hồ bơi riêng",
      "price_raw": "24",
      "stype": "rent",
      "location": "11 Trần Ngọc Diện",
      "city_name": "Thành phố Hồ Chí Minh",
      "ward_name": "Phường 1",
      "type": "Biệt thự",
      "bedroom": 5,
      "pimage": "zillhms5.jpg",
      "image_path": "/Real-Estate-website-in-PHP-main/admin/property/zillhms5.jpg",
      "detail_path": "/Real-Estate-website-in-PHP-main/property/detail/70"
    }
  ],
  "session_id": "550e8400-e29b-41d4-a716-446655440000"
}
```

#### Ý nghĩa trường

| Trường | Mô tả |
|--------|--------|
| `intent` | `property_search` \| `clarify` \| `smalltalk` \| `unknown` |
| `filters` | Bộ lọc đã merge với ngữ cảnh; giá trị `null` = không áp dụng. |
| `missing_slots` | Danh sách slot còn thiếu khi `intent=clarify`, ví dụ `["location"]`. |
| `follow_up_questions` | Câu hỏi gợi ý cho người dùng (tiếng Việt). |
| `result_count` | Số bản ghi sau truy vấn (trước khi cắt top-N hiển thị). |
| `fallback_level` | 0 = khớp chặt; 1+ = đã nới lỏng theo tầng fallback. |
| `fallback_note` | Giải thích ngắn nếu có fallback. |
| `properties` | Tối đa 8 tin; `detail_path` do server tạo từ `PUBLIC_BASE_PATH`. |

### Schema `filters` (chuẩn)

```json
{
  "stype": "rent | null",
  "city_id": "int | null",
  "ward_id": "int | null",
  "ward_name_hint": "string | null",
  "property_types": ["string"],
  "bedrooms_min": "int | null",
  "price_min_million": "float | null",
  "price_max_million": "float | null",
  "size_min": "int | null",
  "size_max": "int | null",
  "keyword": "string | null",
  "amenities": {
    "swimming_pool": "bool",
    "parking": "bool",
    "gym": "bool",
    "near_school": "bool",
    "security": "bool",
    "near_hospital": "bool",
    "near_market": "bool",
    "wifi": "bool",
    "elevator": "bool",
    "cctv": "bool"
  },
  "sort": "relevance | price_asc | price_desc | null",
  "limit_to_pids": [1, 2, 3]
}
```

- Giá trong DB website đang diễn giải là **triệu** (đồng bộ với `formatPropertyPrice` trên PHP).
- `limit_to_pids`: dùng cho ngữ cảnh “rẻ nhất trong các tin vừa tìm”.

## `POST /v1/chat/reset`

### Request

```json
{
  "session_id": "550e8400-e29b-41d4-a716-446655440000",
  "user_id": null
}
```

### Response

```json
{
  "ok": true,
  "session_id": "550e8400-e29b-41d4-a716-446655440000"
}
```

Xóa `chat_context_state` và có thể giữ hoặc không giữ lịch sử `chat_message` (MVP: xóa context, giữ message log tùy cấu hình — implementation hiện tại xóa context + chuẩn bị phiên mới nếu cần).

## `GET /v1/chat/suggestions?session_id=...`

### Response

```json
{
  "suggestions": [
    "Căn hộ 2 phòng ngủ Phường 1 dưới 15 triệu",
    "Nhà nguyên căn Bình Thạnh có bãi xe",
    "Studio gần trung tâm giá dưới 8 triệu"
  ]
}
```

Gợi ý có thể phụ thuộc `filters` trong context (ví dụ đã chọn TP.HCM thì gợi ý phường/xã).

## Lỗi

| HTTP | `detail` |
|------|----------|
| 400 | Thiếu `session_id` / `user_text` |
| 401 | Sai `X-Internal-Secret` khi bật kiểm tra |
| 503 | Không kết nối được MySQL |

