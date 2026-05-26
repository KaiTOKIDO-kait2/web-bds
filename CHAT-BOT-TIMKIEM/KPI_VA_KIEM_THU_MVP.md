# KPI và kiểm thử MVP — Chatbot tìm kiếm BĐS

## KPI đề xuất (nghiệm thu)

| KPI | Mục tiêu MVP | Cách đo |
|-----|----------------|---------|
| Parse đúng slot chính | ≥ 80% trên bộ mẫu dưới đây | Chạy test thủ công / ghi nhận `intent_json` trong `chat_message` |
| Thời gian phản hồi (P90) | < 2.5s (máy dev local, có DB) | Log server hoặc DevTools Network |
| Luôn có phản hồi hữu ích | 100% | Không trả lỗi trần; lỗi kỹ thuật → thông báo hướng dẫn (DB / service) |
| Fallback có giải thích | 100% khi `fallback_level` > 0 | Kiểm tra `fallback_note` trong JSON |

## Bộ câu kiểm thử tiếng Việt (tối thiểu)

1. **Đủ slot — TP.HCM Quận 7**  
   `Căn hộ 2 phòng ngủ Quận 7 dưới 20 triệu có hồ bơi`  
   Kỳ vọng: `locality_id=13`, `bedrooms_min=2`, `price_max_million=20`, `amenities.swimming_pool=true`, có kết quả hoặc fallback có tin gần đúng.

2. **Viết tắt**  
   `2pn Q1 dưới 15tr`  
   Kỳ vọng: map Quận 1 → `locality_id=14`, giá max 15.

3. **Thiếu địa điểm — clarify**  
   `Tìm nhà giá rẻ`  
   Kỳ vọng: `intent=clarify`, `missing_slots` chứa `location`, có `follow_up_questions`.

4. **Ngữ cảnh “rẻ nhất”**  
   - Bước A: `Căn hộ Quận 7 dưới 30 triệu`  
   - Bước B: `Căn rẻ nhất`  
   Kỳ vọng bước B: lọc trong tập kết quả trước (`limit_to_pids`), `sort=price_asc`.

5. **Smalltalk**  
   `Xin chào`  
   Kỳ vọng: `intent=smalltalk`, không lỗi 500.

6. **Hà Nội / Hoàn Kiếm**  
   `Căn hộ Hoàn Kiếm dưới 50 triệu`  
   Kỳ vọng: `city_id=16`, `locality_id=16` (theo seed `realestatephp_new.sql`).

## Kiểm thử tự động (Python)

Trong thư mục `chatbot-service`:

```bash
pip install -r requirements.txt
set PYTHONPATH=.
python -m unittest discover -s tests -p "test_*.py"
```

## Kiểm thử tích hợp nhanh (API)

1. Chạy FastAPI: `uvicorn app.main:app --reload --port 8000`  
2. `curl -s http://127.0.0.1:8000/health`  
3. `curl -s -X POST http://127.0.0.1:8000/v1/chat/message -H "Content-Type: application/json" -d "{\"session_id\":\"00000000-0000-4000-8000-000000000001\",\"user_text\":\"Căn hộ Quận 7 dưới 30 triệu\"}"`

## Ghi chú

- Cần import [../DATABASE FILE/chatbot_schema.sql](../DATABASE%20FILE/chatbot_schema.sql) trước khi test lưu phiên / lịch sử.
- DeepSeek là tùy chọn: không có `DEEPSEEK_API_KEY` vẫn chạy rule-based.
