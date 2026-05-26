from __future__ import annotations

import re
import unicodedata
from typing import Any

# Ghi chú: Tất cả danh sách phường/xã được load động từ DB.
# Không hardcode để hỗ trợ mở rộng đa thành phố.

# Từ tương đối/đại từ — không phải tên phường/xã cụ thể
_WARD_RELATIVE_WORDS: frozenset[str] = frozenset({
    "khác", "kia", "nào", "đó", "đấy", "nọ", "đây",
    "khác đi", "khác thôi", "nữa", "luôn", "hẳn",
})

SMALLTALK_PATTERNS = (
    r"^(xin\s+chào|chào|hello|hi|hey|thanks|cảm\s+ơn)\b",
    r"^bạn\s+là\s+ai",
    r"^bot\b",
)


def is_smalltalk(text: str) -> bool:
    t = text.strip().lower()
    if len(t) < 2:
        return True
    for pat in SMALLTALK_PATTERNS:
        if re.search(pat, t, re.IGNORECASE):
            return True
    return False


def wants_cheapest(text: str) -> bool:
    t = strip_accents_lower(text)
    return bool(
        re.search(
            r"(re nhat|rẻ nhất|gia tot nhat|giá tốt nhất|thap nhat|thấp nhất|re nhat trong|rẻ nhất trong)",
            t,
        )
    )


def wants_priciest(text: str) -> bool:
    raw = text.lower()
    if re.search(r"(đắt nhất|cao nhất|mắc nhất|dat nhat|cao nhat|mac nhat)", raw):
        return True
    t = strip_accents_lower(text)
    return bool(re.search(r"(dat nhat|cao nhat|mac nhat)", t))


def wants_budget_friendly(text: str) -> bool:
    """Nhận diện nhu cầu giá rẻ mơ hồ: chỉ nên sort tăng dần, không tự đặt ngân sách."""
    t = strip_accents_lower(text)
    return bool(
        re.search(
            r"\b(gia re|re re|gia mem|mem mem|tiet kiem|vua tui tien|hop tui tien|binh dan)\b",
            t,
        )
    )


def strip_accents_lower(s: str) -> str:
    s = "".join(
        c
        for c in unicodedata.normalize("NFD", s.lower())
        if unicodedata.category(c) != "Mn"
    )
    return s


def extract_rules(normalized_text: str) -> dict[str, Any]:
    """Trích xuất rule-based từ chuỗi đã normalize (chữ thường)."""
    text = normalized_text.lower()
    out: dict[str, Any] = {}

    m = re.search(r"\b(phường|xã)\s*(\d{1,2})\b", text, flags=re.IGNORECASE)
    if m:
        unit = (m.group(1) or "").strip()
        num = int(m.group(2))
        hint = f"{unit.capitalize()} {num}"
        out["ward_name_hint"] = hint
        out["keyword"] = hint

    # Địa danh dạng "thành phố X" / "tp X" → dùng làm keyword (không phụ thuộc id DB)
    m = re.search(r"\b(thành phố|tp\.?)\s+([a-zà-ỹ0-9\s\-]{2,40})", text, flags=re.IGNORECASE)
    if m:
        name = (m.group(2) or "").strip()
        name = re.split(r"[,.;\n]", name)[0].strip()
        # cắt bớt các từ đệm hay gặp ở cuối câu
        name = re.sub(r"\b(không|ko|khong|nha|nhé|nhe|ạ|a)\b.*$", "", name).strip()
        if len(name) >= 2:
            # giữ dạng Title-ish cho keyword để match LIKE tốt hơn
            out["keyword"] = out.get("keyword") or " ".join([w.capitalize() for w in name.split()])

    # Địa danh hành chính: xã / phường
    m = re.search(r"\b(xã|phường)\s+([a-zà-ỹ0-9\s\-]{2,40})", text, flags=re.IGNORECASE)
    if m:
        unit = (m.group(1) or "").strip()
        name = (m.group(2) or "").strip()
        name = re.split(r"[,.;\n]", name)[0].strip()
        # tránh nuốt luôn phần "thành phố/tp ..." phía sau
        name = re.split(r"\b(thành phố|tp\.?)\b", name, maxsplit=1, flags=re.IGNORECASE)[0].strip()
        name = re.sub(r"\b(không|ko|khong|nha|nhé|nhe|ạ|a)\b.*$", "", name).strip()
        name = re.sub(r"\s+\b(có|và|gần|cách|với|để|không|nha|nhé|nhe|ko|ạ|a|càng|thì|là|hoặc|nếu)\b.*$", "", name, flags=re.IGNORECASE).strip()
        if len(name) >= 2 and name.lower().strip() not in _WARD_RELATIVE_WORDS:
            hint = f"{unit.capitalize()} " + " ".join([w.capitalize() for w in name.split()])
            if not out.get("ward_name_hint"):
                out["ward_name_hint"] = hint
                # keyword: ưu tiên địa danh chi tiết hơn để match LIKE
                out["keyword"] = hint

    out["stype"] = "rent"

    if wants_budget_friendly(text) and not wants_cheapest(text):
        out["sort"] = "price_asc"

    m = re.search(r"(\d+)\s*phòng\s*ngủ", text)
    if m:
        out["bedrooms_min"] = int(m.group(1))

    # Pattern: "X-Y triệu" hoặc "X đến Y triệu"
    m = re.search(r"(\d+(?:[.,]\d+)?)\s*(?:-|đến|den)\s*(\d+(?:[.,]\d+)?)\s*(?:triệu|tr)", text)
    if m:
        a = float(m.group(1).replace(",", "."))
        b = float(m.group(2).replace(",", "."))
        out["price_min_million"] = min(a, b)
        out["price_max_million"] = max(a, b)
    else:
        # Pattern: "X triệu" hoặc "Xtr" đơn lẻ
        m = re.search(r"(\d+(?:[.,]\d+)?)\s*(?:triệu|tr)\b", text)
        if m:
            val = float(m.group(1).replace(",", "."))
            if re.search(r"(duoi|dưới|toi da|tối đa|không quá|khong qua)", text):
                out["price_max_million"] = val
            elif re.search(r"(tren|trên|toi thieu|tối thiểu)", text):
                out["price_min_million"] = val
            else:
                out["price_max_million"] = val

    # Hỗ trợ pattern "phường/xã X" (được resolve từ DB sau)
    m = re.search(r"(?:phường|xã)\s*(\d{1,2})\b", text)
    if m:
        num = int(m.group(1))
        # Để DB resolve - không cứng danh sách
        out["ward_name_hint"] = out.get("ward_name_hint") or f"Phường {num}"
        out["keyword"] = out.get("keyword") or f"Phường {num}"

    if re.search(r"(hồ chí minh|ho chi minh|sài gòn|sai gon|tphcm|tp\.?\s*hcm)", text):
        # Alias cho HCM - service sẽ resolve từ DB
        out["keyword"] = out.get("keyword") or "Hồ Chí Minh"

    if re.search(r"hà nội|ha noi|hn\b", text):
        # Alias cho Hà Nội - service sẽ resolve từ DB
        out["keyword"] = out.get("keyword") or "Hà Nội"

    # Diện tích (m2)
    # - "50m2", "50 m²"
    # - "từ 40-60m2", "40 đến 60 m2"
    m = re.search(r"\b(\d{1,4})\s*(?:m2|m²)\b", text)
    if m:
        out["size_min"] = int(m.group(1))
        out["size_max"] = int(m.group(1))
    m = re.search(r"\b(?:tu|từ)\s*(\d{1,4})\s*(?:-|den|đến)\s*(\d{1,4})\s*(?:m2|m²)\b", text)
    if m:
        a, b = int(m.group(1)), int(m.group(2))
        out["size_min"] = min(a, b)
        out["size_max"] = max(a, b)
    m = re.search(r"\b(\d{1,4})\s*-\s*(\d{1,4})\s*(?:m2|m²)\b", text)
    if m:
        a, b = int(m.group(1)), int(m.group(2))
        out["size_min"] = min(a, b)
        out["size_max"] = max(a, b)

    types: list[str] = []
    if re.search(r"(tòa nhà|toa nha)", text):
        types.append("Tòa nhà")
    if re.search(r"\b(studio)\b", text):
        types.append("Studio")
    if re.search(r"(căn hộ|can ho|chung cu|chung cư)", text):
        types.append("Căn hộ")
        types.append("Chung cư")
    if re.search(r"\b(nhà|nha)\b", text) and "căn hộ" not in text and "tòa nhà" not in text and "toa nha" not in text:
        types.append("Nhà")
    if re.search(r"biệt thự|biet thu", text):
        types.append("Biệt thự")
    if re.search(r"văn phòng|van phong", text):
        types.append("Văn phòng")
    if types:
        out["property_types"] = list(dict.fromkeys(types))

    am: dict[str, bool] = {}
    if re.search(r"(hồ bơi|ho boi|pool)", text):
        am["swimming_pool"] = True
    if re.search(r"(gần trường|gan truong|near school)", text):
        am["near_school"] = True
    if re.search(r"(bảo vệ|bao ve|an ninh|security)", text):
        am["security"] = True
    if re.search(r"(gym|phòng gym)", text):
        am["gym"] = True
    if re.search(r"(bãi xe|bai xe|parking|chỗ đậu)", text):
        am["parking"] = True
    if re.search(r"(wifi|wi-fi|internet|mạng|mang|mạng mẽo|mang meo|mạng đầy đủ|mang day du)", text):
        am["wifi"] = True
    if am:
        out["amenities"] = am

    return out


def extract_keyword_guess(normalized_text: str) -> str | None:
    """Một số địa danh / POI — map thành keyword hoặc locality."""
    text = normalized_text.lower()
    if "bach khoa" in text or "bách khoa" in text:
        return "Bách Khoa"
    if "phạm hùng" in text or "pham hung" in text:
        return "Phạm Hùng"
    return None
