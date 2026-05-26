# Local Embedding cho Chatbot BĐS

Plan này triển khai lớp semantic extraction chạy offline trong FastAPI để nhận diện tiện ích/ý định tìm kiếm linh hoạt hơn, giảm phụ thuộc hard-code và không gửi dữ liệu người dùng ra API ngoài.

## Mục tiêu

- **Nhận diện ngữ nghĩa tốt hơn**: Ví dụ `mạng mẽo đầy đủ`, `có internet`, `kết nối mạng ổn` đều map về `amenities.wifi=true`.
- **Không dùng cloud API**: Chạy embedding model local trong `chatbot-service`.
- **Giữ kiến trúc an toàn**: Rule hiện có vẫn là lớp chắc chắn; embedding là lớp bổ sung trước khi merge/search.
- **Không thay DB chính**: Giai đoạn đầu dùng catalog tiện ích/từ khóa trong code/config; chưa cần thêm bảng MySQL.

## Kiến trúc đề xuất

Luồng mới:

1. `normalize_user_text(raw)` chuẩn hóa câu hỏi.
2. `extract_rules(normalized)` lấy filter chắc chắn bằng regex/rule.
3. `extract_semantic_filters(normalized)` dùng local embedding để suy ra tiện ích/loại nhu cầu.
4. `extract_with_deepseek(normalized)` vẫn có thể giữ như lớp LLM tùy chọn.
5. `merge_search_filters(prev_filters, rules, semantic_payload, llm_payload)` gộp filter.
6. Resolve `city_id`, `ward_id` từ DB như hiện tại.
7. `search_with_fallback()` query MySQL.

## Thành phần cần thêm

- **Dependency local embedding**:
  - Ưu tiên `sentence-transformers`.
  - Model đề xuất: `paraphrase-multilingual-MiniLM-L12-v2` vì hỗ trợ tiếng Việt tương đối tốt và nhẹ hơn các model lớn.
  - Nếu máy yếu, cân nhắc model nhỏ hơn hoặc cache vector để tránh chậm.

- **File semantic extractor mới**:
  - `chatbot-service/app/semantic_extract.py`
  - Chứa:
    - load model lazy/singleton
    - danh sách concept chuẩn: `wifi`, `parking`, `security`, `gym`, `swimming_pool`, `near_school`, `near_hospital`, `near_market`, `elevator`, `cctv`
    - cụm mô tả mẫu cho từng concept
    - hàm cosine similarity
    - threshold cấu hình
    - output dạng dict tương thích `merge_search_filters`, ví dụ:
      ```json
      {"amenities": {"wifi": true}}
      ```

- **Config mới trong `config.py`**:
  - `EMBEDDING_ENABLED=true/false`
  - `EMBEDDING_MODEL=sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2`
  - `EMBEDDING_THRESHOLD=0.55` hoặc tinh chỉnh sau test

- **Tích hợp vào `chat_service.py`**:
  - Import `extract_semantic_filters`.
  - Gọi sau `rules` và trước `llm_payload`.
  - Merge theo thứ tự đề xuất:
    - `prev_filters`
    - `rules`
    - `semantic_payload`
    - `llm_payload`
  - Lý do: rule/semantic bắt chắc intent từ câu; LLM vẫn có thể bổ sung field khác. Nếu muốn semantic ưu tiên hơn LLM cho amenities thì đổi thứ tự hoặc chặn LLM ghi đè `True` thành `False/null`.

## Catalog semantic ban đầu

Ví dụ concept:

- **wifi**:
  - `wifi`
  - `internet`
  - `mạng đầy đủ`
  - `mạng mẽo đầy đủ`
  - `kết nối mạng ổn định`
  - `có mạng sẵn`

- **parking**:
  - `bãi xe`
  - `chỗ đậu xe`
  - `gửi xe`
  - `có nơi để xe`

- **security**:
  - `an ninh tốt`
  - `bảo vệ`
  - `khu an toàn`

- **swimming_pool**:
  - `hồ bơi`
  - `bể bơi`
  - `pool`

- **gym**:
  - `phòng gym`
  - `tập gym`
  - `fitness`

Catalog này nên đặt tập trung trong `semantic_extract.py` hoặc file JSON riêng để dễ mở rộng, thay vì rải `if/else` trong code.

## Chiến lược hiệu năng

- **Lazy load model**: Chỉ load model khi có request đầu tiên và `EMBEDDING_ENABLED=true`.
- **Cache concept embeddings**: Vector của catalog được tính một lần khi service chạy.
- **Không embed toàn DB ở giai đoạn đầu**: Chỉ embed câu user và catalog filter, vì mục tiêu hiện tại là extract filter chứ chưa search semantic toàn bài đăng.
- **Fallback an toàn**: Nếu model lỗi hoặc chưa cài dependency, trả `{}` để chatbot vẫn chạy bằng rule + DeepSeek hiện tại.

## Test cần thêm

- **Unit test semantic extractor**:
  - `mạng mẽo đầy đủ` => `amenities.wifi=true`
  - `có nơi để xe` => `amenities.parking=true`
  - `an ninh tốt` => `amenities.security=true`
  - câu không liên quan không được set tiện ích sai.

- **Test tích hợp merge**:
  - Câu: `có căn nào có ít phòng ngủ gần phường nam triệu, mạng mẽo đầy đủ không`
  - Kỳ vọng filter cuối có:
    - `ward_name_hint="Phường Nam Triệu"`
    - `amenities.wifi=true`

## Rủi ro và cách xử lý

- **Cài `sentence-transformers` nặng**:
  - Có thể cần PyTorch, tải model lần đầu lâu.
  - Nên ghi rõ trong README và `.env` có thể tắt embedding.

- **Sai threshold gây nhận nhầm tiện ích**:
  - Bắt đầu threshold cao vừa phải.
  - Log score khi debug để tinh chỉnh.

- **Tiếng Việt đời thường khó**:
  - Dùng nhiều phrase mẫu cho mỗi concept.
  - Có thể kết hợp rule đơn giản cho những phrase cực phổ biến.

- **Thời gian response tăng**:
  - Cache model và catalog vector.
  - Chỉ embed câu user một lần/request.

## Thứ tự triển khai

1. Thêm dependency và config embedding local.
2. Tạo `semantic_extract.py` với catalog, model loader, cosine similarity, threshold và fallback lỗi.
3. Tích hợp semantic payload vào `chat_service.py` trước bước merge/search.
4. Thêm unit test cho semantic extractor và test câu `mạng mẽo đầy đủ`.
5. Chạy test, sau đó thử thủ công trong chatbot với câu thực tế của bạn.
6. Nếu ổn, cập nhật tài liệu cấu hình service/README/API contract nếu cần.

## Kết quả mong đợi

Sau khi triển khai, câu như `có căn nào có ít phòng ngủ gần phường nam triệu, mạng mẽo đầy đủ không` sẽ không cần hard-code riêng từng biến thể trong rule, mà lớp embedding có thể suy ra `wifi=true` nhờ độ gần nghĩa với concept `wifi/internet/kết nối mạng`.
