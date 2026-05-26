from __future__ import annotations

import json
import re
from typing import Any

import httpx

from app.config import get_settings
from app.pyd_compat import model_parse, model_to_dict
from app.schemas import LlmExtractPayload


SYSTEM_PROMPT = """Bạn chỉ làm nhiệm vụ trích xuất tiêu chí tìm kiếm BĐS cho website Việt Nam (chỉ cho thuê - stype=rent).
Không trả lời người dùng, không gợi ý bất động sản, không suy đoán ID.
Chỉ trả về MỘT JSON hợp lệ, không markdown, không giải thích.
Các khóa được phép (bỏ qua khóa không chắc, KHÔNG đặt null để thể hiện "không đổi"):
clear_fields: mảng tên field cần xóa khỏi context hiện tại, ví dụ ["ward_name_hint", "keyword"]
stype: "rent" | null
city_id: không dùng, hệ thống DB resolver sẽ xử lý
ward_id: không dùng, hệ thống DB resolver sẽ xử lý
ward_name_hint: chuỗi gợi ý như "Phường 1", "Xã Nghi Dương"
property_types: mảng chuỗi tiếng Việt (ví dụ ["Căn hộ", "Biệt thự"])
bedrooms_min: số nguyên
price_min_million: số (đơn vị: triệu VND)
price_max_million: số
size_min: số (đơn vị: m2)
size_max: số
keyword: chuỗi ngắn địa danh/đường
amenities: object boolean: swimming_pool, parking, gym, near_school, security, near_hospital, near_market, wifi, elevator, cctv
sort: "price_asc" nếu user nói giá rẻ/giá mềm/tiết kiệm nhưng không nêu ngân sách cụ thể; "price_desc" nếu user muốn giá cao/đắt nhất

=== QUY TẮC NGỮ CẢNH (quan trọng) ===

1. KẾ THỪA: Nếu câu mới không nhắc đến một tiêu chí nào, HÃY GIỮ YÊN.
   TUYỆT ĐỐI KHÔNG đặt null cho các trường mà người dùng chưa thay đổi.
   Chỉ trả về các key mà câu mới có thông tin mới.
   Nếu thông tin không xuất hiện rõ trong câu hoặc context thì bỏ qua key đó. Không tự đoán thành phố, phường/xã, giá, số phòng.

2. THẾ CHỖ: Khi user dùng "thôi", "chuyển", "đổi sang", "sang", "qua" + loại BĐS mới:
   → property_types chỉ chứa loại mới (đã thay thế, không cộng thêm cái cũ)
   Khi "chuyển qua xã khác", "sang khu khác", "đổi khu vực":
   → clear_fields chứa "ward_name_hint", "ward_id", "keyword"

3. BỔ SUNG: "và cả", "cũng muốn xem" → thêm vào danh sách hiện tại.

4. GIÁ TƯƠNG ĐỐI: "rẻ hơn X triệu" → lấy price_max hiện tại trong Context trừ X.
   "đắt hơn X triệu" → lấy price_min hiện tại cộng X.
   "giá rẻ", "giá mềm", "tiết kiệm", "vừa túi tiền" → sort="price_asc", KHÔNG tự đặt price_max_million.

5. AMENITIES: wifi/mạng/internet → wifi=true; an ninh/bảo vệ → security=true;
   gym/tập tạ → gym=true; mua sắm/chợ/siêu thị → near_market=true.

6. NGỮ CẢNH: "ở một mình"/"sống một mình" → bedrooms_min=1;
   "vợ chồng"/"2 người" → bedrooms_min=2; "gia đình"/"có con" → bedrooms_min=3.

7. HỦY AMENITY ĐƠN LẺ: khi user nói "không cần X nữa" hoặc "bỏ X đi" → đặt X=false.
   khi user nói "chỉ cần X thôi" → đặt tất cả amenity khác =false, chỉ X=true.

8. LOẠI BĐS:
   "nhà nguyên căn", "nhà riêng" → ["Nhà"]
   "chung cư", "căn hộ" → ["Căn hộ", "Chung cư"]
   "tòa nhà" → ["Tòa nhà"]
   "mặt bằng kinh doanh" → ["Mặt bằng"]
   "văn phòng" → ["Văn phòng"]

=== FEW-SHOT (phường/xã schema) ===
Ví dụ 1: Context={gym:true, near_market:true, property_types:["Biệt thự"]}
  User: "ở Đắk Lắk không có hả?"
  Output: {"keyword": "Đắk Lắk"}
  (GIỮ gym, near_market, property_types — không đặt null)

Ví dụ 2: Context={ward_name_hint:"Xã Quảng Trực", city_id:104, property_types:["Văn phòng"]}
  User: "Chuyển qua xã khác đi, biệt thự càng tốt"
  Output: {"clear_fields": ["ward_name_hint", "ward_id", "keyword"], "property_types": ["Biệt thự"]}

Ví dụ 3: Context={price_max_million:10}
  User: "Rẻ hơn 2 triệu đi"
  Output: {"price_max_million": 8}

Ví dụ 4: Context={property_types:["Nhà"]}
  User: "Và cả chung cư nữa"
  Output: {"property_types": ["Nhà", "Chung cư"]}

Ví dụ 5: Context={gym:true, near_market:true}
  User: "Tôi chỉ cần an ninh tốt thôi"
  Output: {"amenities": {"security": true, "gym": false, "near_market": false}}

Ví dụ 6: Context={gym:true, wifi:true}
  User: "Không cần gym nữa, chỉ giữ wifi"
  Output: {"amenities": {"gym": false}}

Ví dụ 7: Context={price_max_million:20, bedrooms_min:2}
  User: "rẻ hơn chút được không"
  Output: {}
  (Không có số cụ thể nên không tự đoán giá mới)

Ví dụ 8: Context={property_types:["Căn hộ"], amenities:{gym:true}}
  User: "thôi tìm nhà nguyên căn, không cần gym nữa"
  Output: {"clear_fields": ["property_types"], "property_types": ["Nhà"], "amenities": {"gym": false}}

Ví dụ 9: Context={}
  User: "tìm chỗ ở gần trường, có mạng, giá mềm"
  Output: {"amenities": {"near_school": true, "wifi": true}, "sort": "price_asc"}
  (Không tự suy ra price_max vì "giá mềm" không có số cụ thể)

Chỉ trả về các key có thông tin mới. Không trả null cho các key không thay đổi."""


def extract_with_deepseek(
    user_normalized: str,
    current_filters: dict[str, Any] | None = None,
) -> dict[str, Any]:
    settings = get_settings()
    key = (settings.deepseek_api_key or "").strip()
    if not key:
        return {}

    context_str = ""
    if current_filters:
        ctx = {
            k: v for k, v in current_filters.items()
            if v is not None and v != [] and v != "" and k not in ("stype", "limit_to_pids", "sort", "ward_id")
        }
        if ctx:
            context_str = "\nContext hiện tại: " + json.dumps(ctx, ensure_ascii=False)

    user_msg = "Trích xuất JSON từ câu sau (tiếng Việt):" + context_str + "\nCâu mới: " + user_normalized[:3000]

    payload = {
        "model": settings.deepseek_model,
        "messages": [
            {"role": "system", "content": SYSTEM_PROMPT},
            {"role": "user", "content": user_msg},
        ],
        "temperature": 0.1,
        "response_format": {"type": "json_object"},
    }
    headers = {"Authorization": f"Bearer {key}", "Content-Type": "application/json"}
    try:
        with httpx.Client(timeout=45.0) as client:
            r = client.post(settings.deepseek_api_url, headers=headers, json=payload)
            r.raise_for_status()
            body = r.json()
    except Exception:
        return {}

    try:
        content = body["choices"][0]["message"]["content"]
    except (KeyError, IndexError):
        return {}

    content = content.strip()
    # Đôi khi model bọc ```json
    m = re.search(r"\{[\s\S]*\}", content)
    if not m:
        return {}
    try:
        raw = json.loads(m.group(0))
    except json.JSONDecodeError:
        return {}

    try:
        validated = model_parse(LlmExtractPayload, raw)
    except Exception:
        return {}
    return {k: v for k, v in model_to_dict(validated, exclude_none=True).items() if v is not None}
