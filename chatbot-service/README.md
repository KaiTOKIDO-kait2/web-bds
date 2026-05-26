# Chatbot tìm kiếm BĐS (FastAPI)

Microservice xử lý hội thoại, trích xuất bộ lọc (rule + tùy chọn DeepSeek), truy vấn MySQL và fallback.

## Cài đặt

```bash
cd chatbot-service
python -m venv .venv
.venv\Scripts\activate
pip install -r requirements.txt
copy .env.example .env
```

Chỉnh `.env` (biến `MYSQL_*`, `PUBLIC_BASE_PATH` trùng `BASEURL` trong PHP, ví dụ `/Real-Estate-website-in-PHP-main`). Xem [.env.example](.env.example).

Import bảng chat (một lần):

```sql
SOURCE ../DATABASE FILE/chatbot_schema.sql;
```

Hoặc mở file [../DATABASE FILE/chatbot_schema.sql](../DATABASE%20FILE/chatbot_schema.sql) trong phpMyAdmin.

## Chạy server

```bash
uvicorn app.main:app --reload --host 0.0.0.0 --port 8000
```

## AI search nâng cao

Service hỗ trợ hybrid ranking và recommendation cá nhân hóa ở mức đồ án:

- `property_embedding`: lưu vector nội dung tin đăng để xếp hạng semantic.
- `chatbot_event`: lưu impression/click từ chatbot để làm tín hiệu recommendation.
- `GET /v1/recommendations?user_id=...&limit=8`: trả danh sách gợi ý cho user.
- `POST /v1/events`: ghi hành vi như `chat_result_click`, `property_detail_view`.

Import schema bổ sung một lần:

```sql
SOURCE ai_schema.sql;
```

Sau đó build embedding cho dữ liệu hiện có:

```bash
cd chatbot-service
python -m app.embedding_pipeline
```

Nếu chưa chạy pipeline hoặc máy chưa tải được model embedding, chatbot vẫn chạy bằng filter/rule/DeepSeek như MVP.
Nếu `sentence-transformers` lỗi do dependency HuggingFace, pipeline sẽ dùng hashing embedding cục bộ để vẫn tạo vector cho demo. Muốn dùng model thật, cài lại dependency tương thích rồi chạy lại pipeline.

## Liên kết PHP

Trong `app/init.php` đã có `CHATBOT_SERVICE_URL` / `CHATBOT_INTERNAL_SECRET` (hoặc biến môi trường). Widget gọi `POST /chatbot/message` trên site PHP, PHP proxy sang FastAPI.

## Tài liệu API

Xem [../CHAT-BOT-TIMKIEM/API_CONTRACT.md](../CHAT-BOT-TIMKIEM/API_CONTRACT.md).
