# Chatbot-service

## Deploy

1. Trên VPS, cài Docker và Docker Compose.
2. Tại thư mục gốc project, tạo `.env` cho toàn bộ stack.
3. Khởi động dịch vụ:

```bash
docker compose up -d --build
```

4. Kiểm tra trạng thái bằng log và danh sách container.

## Env vars

Thiết lập đúng các biến sau cho `chatbot-service`:

```env
MYSQL_HOST=db
MYSQL_PORT=3306
MYSQL_USER=realestate
MYSQL_PASSWORD=realestate123
MYSQL_DATABASE=realestatephp_new
PUBLIC_BASE_PATH=/Real-Estate-website-in-PHP-main
INTERNAL_SECRET=change-me
DEEPSEEK_API_KEY=
CORS_ORIGINS=*
EMBEDDING_ENABLED=true
```

Ghi chú:
- `PUBLIC_BASE_PATH` phải khớp với đường dẫn PHP public.
- `INTERNAL_SECRET` phải khớp giá trị proxy nội bộ từ PHP.
- `DEEPSEEK_API_KEY` có thể để trống nếu chỉ dùng rule/filter.
- `EMBEDDING_ENABLED=true` để bật hybrid search.

## Database/schema import

MySQL trong `docker-compose.yml` tự nạp dump ban đầu khi volume dữ liệu chưa tồn tại:

```bash
DATABASE FILE/realestatephp_new.sql
```

Nếu cần nạp lại từ đầu:
1. Xóa volume MySQL.
2. Chạy lại:

```bash
docker compose up -d --build
```

Schema AI/chat đã nằm trong dump này, gồm các bảng như `chatbot_event` và `property_embedding`.

## Embedding pipeline

Sau khi DB đã có dữ liệu tin đăng, chạy pipeline để tạo vector embedding:

```bash
docker compose exec chatbot python -m app.embedding_pipeline
```

Có thể giới hạn số bản ghi:

```bash
docker compose exec chatbot python -m app.embedding_pipeline --limit 500
```

## Useful commands

```bash
docker compose ps
docker compose logs -f chatbot
docker compose logs -f web
docker compose restart chatbot
docker compose down
docker compose up -d --build
```

## Notes

- `chatbot-service` chạy sau proxy PHP, không cần cài Python local trên VPS nếu dùng Docker.
- Khi đổi `.env`, cần restart stack để áp dụng.
- Nếu dùng CORS khác `*`, hãy khai báo chuỗi origin hợp lệ trong `CORS_ORIGINS`.
- Nếu chưa có embedding, chatbot vẫn hoạt động bằng filter/rule và tùy chọn DeepSeek.
